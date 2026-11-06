<?php

namespace Tests\Feature\Admin;

use App\Models\ContentCategory;
use App\Models\ContentPage;
use App\Models\ContentTag;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

/**
 * Admin CRUD for content pages, categories and tags (roadmap 3.6). Same hand-built SQLite schema
 * caveat as PayPalFriendsFamilyReportPipelineTest (no MySQL in this sandbox, KI-18). Gates come from
 * AuthGates, which reads roles/permissions per request, so every actor is a real user with a role.
 * Admin Blade views are never rendered: GET routes are only checked for authorization.
 */
class ContentCrudTest extends TestCase
{
    private const RESOURCES = [
        'content-pages' => [ContentPage::class, 'content_page'],
        'content-categories' => [ContentCategory::class, 'content_category'],
        'content-tags' => [ContentTag::class, 'content_tag'],
    ];

    private const ABILITIES = ['access', 'create', 'edit', 'show', 'delete'];

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'scout.driver' => 'null',
        ]);
        DB::purge('sqlite');
        Storage::fake('public');
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
        foreach (['content_categories', 'content_tags'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name')->nullable();
                $table->string('slug')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
        Schema::create('content_pages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->nullable();
            $table->longText('page_text')->nullable();
            $table->longText('excerpt')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->string('path')->unique();
            $table->boolean('enabled')->default(0);
            $table->boolean('index')->default(0);
        });
        Schema::create('content_category_content_page', function (Blueprint $table) {
            $table->unsignedBigInteger('content_page_id');
            $table->unsignedBigInteger('content_category_id');
        });
        Schema::create('content_page_content_tag', function (Blueprint $table) {
            $table->unsignedBigInteger('content_page_id');
            $table->unsignedBigInteger('content_tag_id');
        });
        Schema::create('media', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->morphs('model');
            $table->uuid('uuid')->nullable()->unique();
            $table->string('collection_name');
            $table->string('name');
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->string('disk');
            $table->string('conversions_disk')->nullable();
            $table->unsignedBigInteger('size');
            $table->json('manipulations');
            $table->json('custom_properties');
            $table->json('generated_conversions');
            $table->json('responsive_images');
            $table->unsignedInteger('order_column')->nullable()->index();
            $table->nullableTimestamps();
        });
    }


    private function userWith(array $permissions, string $email = 'admin@example.com'): User
    {
        $user = User::create(['name' => 'Admin', 'email' => $email, 'password' => 'x']);
        $role = Role::create(['title' => 'Role for ' . $email]);
        foreach ($permissions as $title) {
            $permission = Permission::firstOrCreate(['title' => $title]);
            $role->permissions()->attach($permission->id);
        }
        $user->roles()->attach($role->id);

        return $user;
    }

    private function allContentPermissionsExcept(string ...$excluded): array
    {
        $all = [];
        foreach (self::RESOURCES as [, $prefix]) {
            foreach (self::ABILITIES as $ability) {
                $all[] = $prefix . '_' . $ability;
            }
        }

        return array_values(array_diff($all, $excluded));
    }

    private function contentAdmin(): User
    {
        return $this->userWith($this->allContentPermissionsExcept(), 'content-admin@example.com');
    }

    private function page(string $path, array $attributes = []): ContentPage
    {
        return ContentPage::create($attributes + ['title' => 'Page ' . $path, 'path' => $path, 'enabled' => true]);
    }

    private function makeRecord(string $resource): Model
    {
        if ($resource === 'content-pages') {
            return $this->page('existing', ['title' => 'Existing']);
        }
        [$class] = self::RESOURCES[$resource];

        return $class::create(['name' => 'Existing', 'slug' => 'existing']);
    }

    private function validPayload(string $resource): array
    {
        return $resource === 'content-pages'
            ? ['title' => 'Changed', 'path' => 'changed', 'enabled' => '1']
            : ['name' => 'Changed', 'slug' => 'changed'];
    }

    private function sendWrite(string $resource, string $action, Model $record)
    {
        return match ($action) {
            'store' => $this->post("/admin/{$resource}", $this->validPayload($resource)),
            'update' => $this->put("/admin/{$resource}/{$record->id}", $this->validPayload($resource)),
            'destroy' => $this->delete("/admin/{$resource}/{$record->id}"),
            'massDestroy' => $this->delete("/admin/{$resource}/destroy", ['ids' => [$record->id]]),
        };
    }

    private function assertRecordUntouched(string $resource, Model $record): void
    {
        [$class] = self::RESOURCES[$resource];
        $this->assertSame(1, $class::withTrashed()->count(), 'no row may be created');
        $fresh = $class::withTrashed()->findOrFail($record->id);
        $this->assertNull($fresh->deleted_at, 'the row may not be deleted');
        $this->assertSame('Existing', $resource === 'content-pages' ? $fresh->title : $fresh->name);
    }


    public function writeRoutes(): array
    {
        $cases = [];
        $permissionFor = ['store' => 'create', 'update' => 'edit', 'destroy' => 'delete', 'massDestroy' => 'delete'];
        foreach (self::RESOURCES as $resource => [, $prefix]) {
            foreach ($permissionFor as $action => $ability) {
                $cases["{$resource} {$action}"] = [$resource, $action, $prefix . '_' . $ability];
            }
        }

        return $cases;
    }

    public function readRoutes(): array
    {
        $cases = [];
        foreach (array_keys(self::RESOURCES) as $resource) {
            [, $prefix] = self::RESOURCES[$resource];
            $cases["{$resource} index"] = [$resource, '', $prefix . '_access'];
            $cases["{$resource} create"] = [$resource, '/create', $prefix . '_create'];
            $cases["{$resource} show"] = [$resource, '/{id}', $prefix . '_show'];
            $cases["{$resource} edit"] = [$resource, '/{id}/edit', $prefix . '_edit'];
        }

        return $cases;
    }

    /** @dataProvider writeRoutes */
    public function test_write_route_is_forbidden_for_a_user_lacking_only_that_permission(string $resource, string $action, string $permission): void
    {
        $record = $this->makeRecord($resource);
        $this->actingAs($this->userWith($this->allContentPermissionsExcept($permission)));

        $this->sendWrite($resource, $action, $record)->assertForbidden();

        $this->assertRecordUntouched($resource, $record);
    }

    /** @dataProvider writeRoutes */
    public function test_write_route_redirects_a_guest_to_login(string $resource, string $action): void
    {
        $record = $this->makeRecord($resource);

        $this->sendWrite($resource, $action, $record)->assertRedirect(route('login'));

        $this->assertRecordUntouched($resource, $record);
    }

    /** @dataProvider readRoutes */
    public function test_read_route_is_forbidden_for_a_user_lacking_only_that_permission(string $resource, string $suffix, string $permission): void
    {
        $record = $this->makeRecord($resource);
        $this->actingAs($this->userWith($this->allContentPermissionsExcept($permission)));

        $this->get('/admin/' . $resource . str_replace('{id}', (string) $record->id, $suffix))->assertForbidden();
    }

    /** @dataProvider readRoutes */
    public function test_read_route_redirects_a_guest_to_login(string $resource, string $suffix): void
    {
        $record = $this->makeRecord($resource);

        $this->get('/admin/' . $resource . str_replace('{id}', (string) $record->id, $suffix))->assertRedirect(route('login'));
    }


    public function taxonomies(): array
    {
        return [
            'categories' => ['content-categories', ContentCategory::class],
            'tags' => ['content-tags', ContentTag::class],
        ];
    }

    /** @dataProvider taxonomies */
    public function test_taxonomy_store_creates_the_row_and_redirects_to_index(string $resource, string $class): void
    {
        $this->actingAs($this->contentAdmin())
            ->post("/admin/{$resource}", ['name' => 'Techno', 'slug' => 'techno'])
            ->assertRedirect(route("admin.{$resource}.index"));

        $this->assertSame(1, $class::count());
        $this->assertDatabaseHas((new $class())->getTable(), ['name' => 'Techno', 'slug' => 'techno', 'deleted_at' => null]);
    }

    /** @dataProvider taxonomies */
    public function test_taxonomy_store_accepts_a_missing_slug(string $resource, string $class): void
    {
        $this->actingAs($this->contentAdmin())
            ->post("/admin/{$resource}", ['name' => 'House'])
            ->assertSessionHasNoErrors();

        $this->assertNull($class::sole()->slug);
    }

    /** @dataProvider taxonomies */
    public function test_taxonomy_store_without_name_is_rejected_and_creates_nothing(string $resource, string $class): void
    {
        $this->actingAs($this->contentAdmin())
            ->post("/admin/{$resource}", ['slug' => 'techno'])
            ->assertSessionHasErrors('name');

        $this->assertSame(0, $class::withTrashed()->count());
    }

    /** @dataProvider taxonomies */
    public function test_taxonomy_store_with_non_string_slug_is_rejected(string $resource, string $class): void
    {
        $this->actingAs($this->contentAdmin())
            ->post("/admin/{$resource}", ['name' => 'Techno', 'slug' => ['a']])
            ->assertSessionHasErrors('slug');

        $this->assertSame(0, $class::withTrashed()->count());
    }

    /** @dataProvider taxonomies */
    public function test_taxonomy_update_changes_the_row(string $resource, string $class): void
    {
        $record = $class::create(['name' => 'Old', 'slug' => 'old']);

        $this->actingAs($this->contentAdmin())
            ->put("/admin/{$resource}/{$record->id}", ['name' => 'New', 'slug' => 'new'])
            ->assertRedirect(route("admin.{$resource}.index"));

        $record->refresh();
        $this->assertSame('New', $record->name);
        $this->assertSame('new', $record->slug);
    }

    /** @dataProvider taxonomies */
    public function test_taxonomy_update_without_name_is_rejected_and_keeps_the_row(string $resource, string $class): void
    {
        $record = $class::create(['name' => 'Old', 'slug' => 'old']);

        $this->actingAs($this->contentAdmin())
            ->put("/admin/{$resource}/{$record->id}", ['name' => '', 'slug' => 'new'])
            ->assertSessionHasErrors('name');

        $record->refresh();
        $this->assertSame('Old', $record->name);
        $this->assertSame('old', $record->slug);
    }

    /** @dataProvider taxonomies */
    public function test_taxonomy_destroy_soft_deletes_the_row(string $resource, string $class): void
    {
        $record = $class::create(['name' => 'Old', 'slug' => 'old']);
        $other = $class::create(['name' => 'Other', 'slug' => 'other']);

        $this->actingAs($this->contentAdmin())->delete("/admin/{$resource}/{$record->id}")->assertRedirect();

        $this->assertSoftDeleted($record);
        $this->assertNotSoftDeleted($other);
    }

    /** @dataProvider taxonomies */
    public function test_taxonomy_mass_destroy_soft_deletes_only_the_selected_rows(string $resource, string $class): void
    {
        $a = $class::create(['name' => 'A']);
        $b = $class::create(['name' => 'B']);
        $keep = $class::create(['name' => 'Keep']);

        $this->actingAs($this->contentAdmin())
            ->delete("/admin/{$resource}/destroy", ['ids' => [$a->id, $b->id]])
            ->assertNoContent();

        $this->assertSoftDeleted($a);
        $this->assertSoftDeleted($b);
        $this->assertNotSoftDeleted($keep);
    }

    /** @dataProvider taxonomies */
    public function test_taxonomy_mass_destroy_with_an_unknown_id_is_rejected_and_deletes_nothing(string $resource, string $class): void
    {
        $record = $class::create(['name' => 'A']);

        $this->actingAs($this->contentAdmin())
            ->delete("/admin/{$resource}/destroy", ['ids' => [$record->id, 999]])
            ->assertSessionHasErrors('ids.1');

        $this->assertNotSoftDeleted($record);
    }

    /** @dataProvider taxonomies */
    public function test_taxonomy_mass_destroy_without_ids_is_rejected(string $resource, string $class): void
    {
        $record = $class::create(['name' => 'A']);

        $this->actingAs($this->contentAdmin())
            ->delete("/admin/{$resource}/destroy", [])
            ->assertSessionHasErrors('ids');

        $this->assertNotSoftDeleted($record);
    }


    public function test_page_store_creates_the_row_with_posted_fields_and_attaches_categories_and_tags(): void
    {
        $news = ContentCategory::create(['name' => 'News']);
        $events = ContentCategory::create(['name' => 'Events']);
        ContentCategory::create(['name' => 'Unused']);
        $techno = ContentTag::create(['name' => 'Techno']);

        $this->actingAs($this->contentAdmin())->post('/admin/content-pages', [
            'title' => 'About us',
            'path' => 'about/us',
            'page_text' => '<p>Body</p>',
            'excerpt' => 'Short',
            'enabled' => '1',
            'index' => '0',
            'categories' => [$news->id, $events->id],
            'tags' => [$techno->id],
        ])->assertRedirect(route('admin.content-pages.index'));

        $page = ContentPage::sole();
        $this->assertSame('About us', $page->title);
        $this->assertSame('about/us', $page->path);
        $this->assertSame('<p>Body</p>', $page->page_text);
        $this->assertSame('Short', $page->excerpt);
        $this->assertTrue($page->enabled);
        $this->assertFalse($page->index);
        $this->assertEqualsCanonicalizing([$news->id, $events->id], $page->categories()->pluck('content_categories.id')->all());
        $this->assertSame([$techno->id], $page->tags()->pluck('content_tags.id')->all());
    }

    public function test_page_store_without_categories_or_tags_attaches_none(): void
    {
        $this->actingAs($this->contentAdmin())
            ->post('/admin/content-pages', ['title' => 'Plain', 'path' => 'plain', 'enabled' => '0'])
            ->assertSessionHasNoErrors();

        $page = ContentPage::sole();
        $this->assertFalse($page->enabled);
        $this->assertSame(0, $page->categories()->count());
        $this->assertSame(0, $page->tags()->count());
    }

    public function invalidPagePayloads(): array
    {
        $valid = ['title' => 'Title', 'path' => 'path', 'enabled' => '1'];

        return [
            'missing title' => [array_diff_key($valid, ['title' => 1]), 'title'],
            'missing path' => [array_diff_key($valid, ['path' => 1]), 'path'],
            'missing enabled' => [array_diff_key($valid, ['enabled' => 1]), 'enabled'],
            'non boolean enabled' => [['enabled' => 'maybe'] + $valid, 'enabled'],
            'non boolean index' => [['index' => 'maybe'] + $valid, 'index'],
            'categories not an array' => [['categories' => 'x'] + $valid, 'categories'],
            'non integer category id' => [['categories' => ['abc']] + $valid, 'categories.0'],
            'non integer tag id' => [['tags' => ['abc']] + $valid, 'tags.0'],
        ];
    }

    /** @dataProvider invalidPagePayloads */
    public function test_page_store_with_invalid_input_is_rejected_and_creates_nothing(array $payload, string $errorKey): void
    {
        $this->actingAs($this->contentAdmin())->post('/admin/content-pages', $payload)->assertSessionHasErrors($errorKey);

        $this->assertSame(0, ContentPage::withTrashed()->count());
        $this->assertSame(0, DB::table('content_category_content_page')->count());
    }

    public function test_page_store_with_a_path_already_in_use_is_rejected(): void
    {
        $this->page('taken');

        $this->actingAs($this->contentAdmin())
            ->post('/admin/content-pages', ['title' => 'Dup', 'path' => 'taken', 'enabled' => '1'])
            ->assertSessionHasErrors('path');

        $this->assertSame(1, ContentPage::count());
    }

    public function test_page_update_changes_fields_and_replaces_categories_and_tags(): void
    {
        $old = ContentCategory::create(['name' => 'Old']);
        $new = ContentCategory::create(['name' => 'New']);
        $oldTag = ContentTag::create(['name' => 'OldTag']);
        $newTag = ContentTag::create(['name' => 'NewTag']);
        $page = $this->page('before', ['title' => 'Before', 'enabled' => false]);
        $page->categories()->sync([$old->id]);
        $page->tags()->sync([$oldTag->id]);

        $this->actingAs($this->contentAdmin())->put("/admin/content-pages/{$page->id}", [
            'title' => 'After',
            'path' => 'after',
            'excerpt' => 'New excerpt',
            'enabled' => '1',
            'categories' => [$new->id],
            'tags' => [$newTag->id],
        ])->assertRedirect(route('admin.content-pages.index'));

        $page->refresh();
        $this->assertSame('After', $page->title);
        $this->assertSame('after', $page->path);
        $this->assertSame('New excerpt', $page->excerpt);
        $this->assertTrue($page->enabled);
        $this->assertSame([$new->id], $page->categories()->pluck('content_categories.id')->all());
        $this->assertSame([$newTag->id], $page->tags()->pluck('content_tags.id')->all());
    }

    public function test_page_update_without_categories_or_tags_detaches_all(): void
    {
        $page = $this->page('page');
        $page->categories()->sync([ContentCategory::create(['name' => 'C'])->id]);
        $page->tags()->sync([ContentTag::create(['name' => 'T'])->id]);

        $this->actingAs($this->contentAdmin())
            ->put("/admin/content-pages/{$page->id}", ['title' => 'Page', 'path' => 'page', 'enabled' => '1'])
            ->assertSessionHasNoErrors();

        $this->assertSame(0, $page->categories()->count());
        $this->assertSame(0, $page->tags()->count());
    }

    public function test_page_update_may_keep_its_own_path(): void
    {
        $page = $this->page('same', ['title' => 'Old']);

        $this->actingAs($this->contentAdmin())
            ->put("/admin/content-pages/{$page->id}", ['title' => 'New', 'path' => 'same', 'enabled' => '1'])
            ->assertSessionHasNoErrors();

        $this->assertSame('New', $page->refresh()->title);
    }

    public function test_page_update_with_invalid_input_is_rejected_and_keeps_the_row(): void
    {
        $page = $this->page('page', ['title' => 'Old']);

        $this->actingAs($this->contentAdmin())
            ->put("/admin/content-pages/{$page->id}", ['title' => '', 'path' => 'page'])
            ->assertSessionHasErrors(['title', 'enabled']);

        $this->assertSame('Old', $page->refresh()->title);
    }

    /**
     * UpdateContentPageRequest has its unique rule commented out, so taking another page's path is
     * not a validation error: it reaches the database unique index and fails as a server error.
     */
    public function test_page_update_to_another_pages_path_fails_with_server_error_current_behaviour(): void
    {
        $this->page('taken');
        $page = $this->page('mine', ['title' => 'Mine']);

        $this->actingAs($this->contentAdmin())
            ->put("/admin/content-pages/{$page->id}", ['title' => 'Mine', 'path' => 'taken', 'enabled' => '1'])
            ->assertStatus(500);

        $this->assertSame('mine', $page->refresh()->path);
    }

    public function test_page_destroy_soft_deletes_the_row(): void
    {
        $page = $this->page('gone');
        $other = $this->page('stays');

        $this->actingAs($this->contentAdmin())->delete("/admin/content-pages/{$page->id}")->assertRedirect();

        $this->assertSoftDeleted($page);
        $this->assertNotSoftDeleted($other);
    }

    public function test_page_mass_destroy_soft_deletes_only_the_selected_rows(): void
    {
        $a = $this->page('a');
        $b = $this->page('b');
        $keep = $this->page('keep');

        $this->actingAs($this->contentAdmin())
            ->delete('/admin/content-pages/destroy', ['ids' => [$a->id, $b->id]])
            ->assertNoContent();

        $this->assertSoftDeleted($a);
        $this->assertSoftDeleted($b);
        $this->assertNotSoftDeleted($keep);
    }

    public function test_page_mass_destroy_with_an_unknown_id_is_rejected_and_deletes_nothing(): void
    {
        $page = $this->page('a');

        $this->actingAs($this->contentAdmin())
            ->delete('/admin/content-pages/destroy', ['ids' => [999]])
            ->assertSessionHasErrors('ids.0');

        $this->assertNotSoftDeleted($page);
    }


    /**
     * The real site.index view pulls in layouts and more tables, so a plain-PHP stub is put in front
     * of it on the view finder (a temp dir outside the repo) that only echoes what the controller passed.
     */
    private function stubSiteIndexView(): void
    {
        $dir = sys_get_temp_dir() . '/rave-content-crud-views';
        if (!is_dir($dir . '/site')) {
            mkdir($dir . '/site', 0777, true);
        }
        file_put_contents($dir . '/site/index.php', '<?php echo "STUB|" . $siteType . "|" . $contentPage->id;');
        View::getFinder()->prependLocation($dir);
    }

    public function test_catch_all_route_serves_an_enabled_content_page_by_its_path(): void
    {
        $this->stubSiteIndexView();
        $this->page('other');
        $page = $this->page('info/about');

        $response = $this->get('/info/about');

        $response->assertOk();
        $this->assertSame('STUB|cms|' . $page->id, $response->getContent());
    }

    public function test_catch_all_route_redirects_a_disabled_page_to_the_error_page(): void
    {
        $this->page('hidden', ['enabled' => false]);

        $this->get('/hidden')->assertRedirect('/site-error');
    }

    public function test_catch_all_route_redirects_the_index_page_path_to_the_error_page(): void
    {
        $this->page('home', ['index' => true]);

        $this->get('/home')->assertRedirect('/site-error');
    }

    public function test_catch_all_route_redirects_a_soft_deleted_page_to_the_error_page(): void
    {
        $this->page('removed')->delete();

        $this->get('/removed')->assertRedirect('/site-error');
    }

    public function test_catch_all_route_redirects_an_unknown_path_to_the_error_page(): void
    {
        $this->page('known');

        $this->get('/unknown')->assertRedirect('/site-error');
    }
}
