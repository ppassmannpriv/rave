<?php

namespace Tests\Feature\Payment;

use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\PaymentMethod\PayPalFriendsFamily;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * End to end for the live F&F path: admin uploads a PayPal CSV export, the scheduled scan reads it
 * and reconciles transactions. Same hand-built SQLite schema caveat as PayTransactionTest (no MySQL
 * in this sandbox, migrations are not SQLite compatible, KI-18).
 *
 * The upload controller writes to the "s3" disk while the scan command reads the default disk, so
 * the two only meet when FILESYSTEM_DISK=s3; these tests set that explicitly.
 */
class PayPalFriendsFamilyReportPipelineTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'filesystems.default' => 's3',
        ]);
        DB::purge('sqlite');
        Storage::fake('s3');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
        foreach (['roles', 'permissions'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->timestamps();
                $table->softDeletes();
            });
        }
        Schema::create('permission_role', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('permission_id');
        });
        Schema::create('role_user', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');
        });
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('alias');
            $table->string('name');
            $table->string('FQN');
            $table->text('description')->nullable();
            $table->boolean('active');
            $table->boolean('admin_only')->default(false);
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status');
            $table->decimal('price', 15, 2)->nullable();
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('payment_method_id');
            $table->integer('order_id');
            $table->string('reference');
            $table->string('external_id')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('state');
            $table->string('comment')->nullable();
            $table->timestamps();
        });
        Schema::create('transaction_partials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->string('external_id')->nullable();
            $table->decimal('amount', 15, 2);
            $table->json('raw')->nullable();
            $table->timestamps();
            $table->unique(['transaction_id', 'external_id']);
        });
    }

    /** @return array{0: Order, 1: Transaction} */
    private function openTransaction(string $reference, string $amount, ?PaymentMethod $method = null): array
    {
        $method ??= PaymentMethod::firstOrCreate(['alias' => 'paypal_ff'], ['name' => 'F&F', 'FQN' => 'PayPalFriendsFamily', 'active' => true]);
        $order = Order::create(['status' => Order::STATUS_INITIALIZED]);
        $transaction = Transaction::create([
            'payment_method_id' => $method->id,
            'order_id' => $order->id,
            'reference' => $reference,
            'amount' => $amount,
            'state' => Transaction::STATE_INIT,
        ]);
        $order->transaction()->associate($transaction);
        $order->save();

        return [$order, $transaction];
    }

    private function admin(bool $mayManagePayments): User
    {
        $user = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'x']);
        $role = Role::create(['title' => 'Staff']);
        if ($mayManagePayments) {
            $role->permissions()->attach(Permission::create(['title' => 'payment_access'])->id);
        }
        $user->roles()->attach($role->id);

        return $user;
    }

    private function csv(array $rows): string
    {
        $lines = ['"Datum","Transaktionscode","Brutto","Hinweis","Absender E-Mail-Adresse"'];
        foreach ($rows as $i => [$amount, $note]) {
            $lines[] = sprintf('"01.01.2026","TX%d","%s","%s","buyer@example.com"', $i, $amount, $note);
        }

        return implode("\n", $lines) . "\n";
    }

    private function upload(User $admin, string $csvContents)
    {
        return $this->actingAs($admin)->post('/admin/paymentMethods/upload', [
            'csv' => UploadedFile::fake()->createWithContent('export.csv', $csvContents),
        ]);
    }

    private function storedReports(): array
    {
        return Storage::disk('s3')->files(PayPalFriendsFamily::REPORT_DIR);
    }

    public function test_an_admin_with_payment_access_can_upload_a_report(): void
    {
        $this->upload($this->admin(true), $this->csv([['25,00', 'AAAAAAAAAA']]))->assertRedirect('/admin/paymentMethods/index');

        $this->assertCount(1, $this->storedReports());
    }

    public function test_an_admin_without_payment_access_cannot_upload(): void
    {
        $this->upload($this->admin(false), $this->csv([['25,00', 'AAAAAAAAAA']]))->assertForbidden();

        $this->assertSame([], $this->storedReports());
    }

    public function test_a_non_csv_upload_is_rejected(): void
    {
        $this->actingAs($this->admin(true))->post('/admin/paymentMethods/upload', [
            'csv' => UploadedFile::fake()->create('export.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('csv');

        $this->assertSame([], $this->storedReports());
    }

    public function test_uploaded_report_is_reconciled_by_the_scan_and_moved_to_processed(): void
    {
        [$exactOrder, $exact] = $this->openTransaction('AAAAAAAAAA', '25.00');
        [, $underpaid] = $this->openTransaction('BBBBBBBBBB', '20.00');
        [, $untouched] = $this->openTransaction('DDDDDDDDDD', '30.00');
        $this->upload($this->admin(true), $this->csv([
            ['25,00', 'AAAAAAAAAA'],
            ['10,00', 'BBBBBBBBBB'],
            ['5,00', 'ZZZZZZZZZZ'],
            ['not-money', 'CCCCCCCCCC'],
        ]));

        $this->artisan('payment_methods:paypal_ff:scan-report')->assertExitCode(0);

        $this->assertSame(Transaction::STATE_PAID, $exact->refresh()->state);
        $this->assertSame(Order::STATUS_PAID, $exactOrder->refresh()->status);
        $this->assertSame(Transaction::STATE_INIT, $underpaid->refresh()->state);
        $this->assertSame(1, $underpaid->partialTransactions()->count());
        $this->assertSame(Transaction::STATE_INIT, $untouched->refresh()->state);
        $this->assertSame(0, $untouched->partialTransactions()->count());
        $this->assertSame([], $this->storedReports());
        $this->assertCount(1, Storage::disk('s3')->files(PayPalFriendsFamily::REPORT_DIR . '/processed'));
    }

    public function test_an_invalid_row_does_not_stop_the_rows_after_it(): void
    {
        [, $transaction] = $this->openTransaction('AAAAAAAAAA', '25.00');
        $this->upload($this->admin(true), $this->csv([
            ['not-money', 'CCCCCCCCCC'],
            ['25,00', 'AAAAAAAAAA'],
        ]));

        $this->artisan('payment_methods:paypal_ff:scan-report')->assertExitCode(0);

        $this->assertSame(Transaction::STATE_PAID, $transaction->refresh()->state);
    }

    public function test_overlapping_exports_do_not_count_the_same_payment_twice(): void
    {
        [, $transaction] = $this->openTransaction('BBBBBBBBBB', '20.00');
        $admin = $this->admin(true);
        $export = $this->csv([['10,00', 'BBBBBBBBBB']]);

        $this->upload($admin, $export);
        $this->artisan('payment_methods:paypal_ff:scan-report')->assertExitCode(0);
        $this->upload($admin, $export);
        $this->artisan('payment_methods:paypal_ff:scan-report')->assertExitCode(0);

        $this->assertSame(Transaction::STATE_INIT, $transaction->refresh()->state, 'a replayed 10.00 row must not complete a 20.00 transaction');
        $this->assertSame(1, $transaction->partialTransactions()->count());
    }

    public function test_a_scan_with_no_reports_succeeds_and_changes_nothing(): void
    {
        [, $transaction] = $this->openTransaction('AAAAAAAAAA', '25.00');

        $this->artisan('payment_methods:paypal_ff:scan-report')->assertExitCode(0);

        $this->assertSame(Transaction::STATE_INIT, $transaction->refresh()->state);
    }
}
