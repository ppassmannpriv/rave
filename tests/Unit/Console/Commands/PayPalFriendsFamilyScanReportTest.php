<?php

namespace Tests\Unit\Console\Commands;

use App\Models\PaymentMethod\PayPalFriendsFamily;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PayPalFriendsFamilyScanReportTest extends TestCase
{
    public function test_report_without_required_columns_fails_loudly_and_is_left_in_place(): void
    {
        config(['filesystems.default' => 'local']);
        Storage::fake('local');
        $file = PayPalFriendsFamily::REPORT_DIR.'/report.csv';
        Storage::put($file, "Datum,Netto,Beschreibung\n01.01.2026,25.00,something\n");

        $this->artisan('payment_methods:paypal_ff:scan-report')->assertExitCode(1);

        Storage::assertExists($file);
        Storage::assertMissing(PayPalFriendsFamily::REPORT_DIR.'/processed/report.csv');
    }

    public function test_report_missing_only_the_configured_column_name_fails(): void
    {
        config(['filesystems.default' => 'local', 'paypal_ff.columns.amount' => 'Gross']);
        Storage::fake('local');
        $file = PayPalFriendsFamily::REPORT_DIR.'/report.csv';
        Storage::put($file, "Brutto,Hinweis\n25.00,ABCDEFGH12\n");

        $this->artisan('payment_methods:paypal_ff:scan-report')->assertExitCode(1);

        Storage::assertExists($file);
    }
}
