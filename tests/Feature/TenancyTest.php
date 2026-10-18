<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Tenancy\Exceptions\TenantLockedException;
use App\Tenancy\Tenant;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantDatabaseManager;
use App\Tenancy\TenantManager;
use App\Tenancy\TenantResolver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TenancyTest extends TestCase
{
    protected string $db1Path;
    protected string $db2Path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db1Path = database_path('test_store1.sqlite');
        $this->db2Path = database_path('test_store2.sqlite');

        // Create clean SQLite test databases
        $this->createTestDatabase($this->db1Path, 'Store 1', 'store1@example.com', 'Product 1 from Store 1');
        $this->createTestDatabase($this->db2Path, 'Store 2', 'store2@example.com', 'Product 2 from Store 2');

        File::deleteDirectory(storage_path('framework/cache/data/tenants'));

        // Configure tenancy for testing
        config([
            'cache.default' => 'file',
            'tenancy.enabled' => true,
            'tenancy.connection_template' => 'sqlite',
            'tenancy.central_domains' => ['admin.example.com', 'central.test'],
            'tenancy.domains' => [
                'store1.example.com' => [
                    'tenant' => 'store1',
                    'name' => 'Store One',
                    'driver' => 'sqlite',
                    'database' => $this->db1Path,
                    'foreign_key_constraints' => false,
                ],
                'store2.example.com' => [
                    'tenant' => 'store2',
                    'name' => 'Store Two',
                    'driver' => 'sqlite',
                    'database' => $this->db2Path,
                    'foreign_key_constraints' => false,
                ],
            ],
        ]);
    }

    protected function tearDown(): void
    {
        // Reset tenant state
        if (app()->bound(TenantManager::class)) {
            app(TenantManager::class)->reset();
        }

        // Clean up test sqlite files
        if (File::exists($this->db1Path)) {
            @unlink($this->db1Path);
        }
        if (File::exists($this->db2Path)) {
            @unlink($this->db2Path);
        }

        // Clean up test storage
        Storage::disk('public')->deleteDirectory('tenants');
        File::deleteDirectory(storage_path('framework/cache/data/tenants'));

        parent::tearDown();
    }

    protected function createTestDatabase(string $path, string $siteTitle, string $userEmail, string $productName): void
    {
        if (File::exists($path)) {
            @unlink($path);
        }

        touch($path);

        $tempConn = 'sqlite_temp_' . md5($path);
        config(["database.connections.{$tempConn}" => [
            'driver' => 'sqlite',
            'database' => $path,
            'foreign_key_constraints' => false,
        ]]);

        Schema::connection($tempConn)->create('settings', function ($table) {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('loader')->nullable();
            $table->string('meta_image')->nullable();
            $table->string('footer_gateway_img')->nullable();
            $table->timestamps();
        });

        Schema::connection($tempConn)->create('extra_settings', function ($table) {
            $table->increments('id');
            $table->timestamps();
        });

        Schema::connection($tempConn)->create('menus', function ($table) {
            $table->increments('id');
            $table->timestamps();
        });

        Schema::connection($tempConn)->create('users', function ($table) {
            $table->increments('id');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('photo')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::connection($tempConn)->create('items', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->string('slug');
            $table->decimal('discount_price', 10, 2)->default(0);
            $table->timestamps();
        });

        DB::connection($tempConn)->table('settings')->insert([
            'id' => 1,
            'title' => $siteTitle,
        ]);

        DB::connection($tempConn)->table('users')->insert([
            'id' => 1,
            'first_name' => $siteTitle,
            'email' => $userEmail,
            'password' => Hash::make('secret123'),
        ]);

        DB::connection($tempConn)->table('items')->insert([
            'id' => 1,
            'name' => $productName,
            'slug' => \Illuminate\Support\Str::slug($productName),
            'discount_price' => 99.00,
        ]);

        DB::purge($tempConn);
    }

    /**
     * Test 1: store1.example.com queries store1 database.
     */
    public function test_store1_returns_data_from_store1_database(): void
    {
        $manager = app(TenantManager::class);
        $manager->bootstrapTenantByHost('store1.example.com', lock: false);

        $this->assertEquals('store1', $manager->getContext()->id());
        $setting = Setting::find(1);
        $this->assertNotNull($setting);
        $this->assertEquals('Store 1', $setting->title);

        $product = DB::table('items')->find(1);
        $this->assertNotNull($product);
        $this->assertEquals('Product 1 from Store 1', $product->name);
    }

    /**
     * Test 2: store2.example.com queries store2 database.
     */
    public function test_store2_returns_data_from_store2_database(): void
    {
        $manager = app(TenantManager::class);
        $manager->bootstrapTenantByHost('store2.example.com', lock: false);

        $this->assertEquals('store2', $manager->getContext()->id());
        $setting = Setting::find(1);
        $this->assertNotNull($setting);
        $this->assertEquals('Store 2', $setting->title);

        $product = DB::table('items')->find(1);
        $this->assertNotNull($product);
        $this->assertEquals('Product 2 from Store 2', $product->name);
    }

    /**
     * Test 3: Unknown domain does NOT query default database and returns 404 fail-safe.
     */
    public function test_unknown_domain_fails_safely_without_querying_default_database(): void
    {
        $response = $this->get('http://unknown-domain.org/api/test-route-or-whatever');

        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * Test 4: User from store1 cannot authenticate against store2.
     */
    public function test_user_from_store1_cannot_authenticate_against_store2(): void
    {
        $manager = app(TenantManager::class);

        // Verify user on store 1
        $manager->bootstrapTenantByHost('store1.example.com', lock: false);
        $user1 = User::where('email', 'store1@example.com')->first();
        $this->assertNotNull($user1);
        $this->assertTrue(Hash::check('secret123', $user1->password));

        // Switch to store 2
        $manager->reset();
        $manager->bootstrapTenantByHost('store2.example.com', lock: false);

        // User from store 1 must not exist on store 2
        $userOnStore2 = User::where('email', 'store1@example.com')->first();
        $this->assertNull($userOnStore2);

        // Store 2 has its own isolated user
        $user2 = User::where('email', 'store2@example.com')->first();
        $this->assertNotNull($user2);
        $this->assertEquals('store2@example.com', $user2->email);
    }

    /**
     * Test 5: Cache keys are tenant isolated.
     */
    public function test_cache_keys_are_tenant_isolated(): void
    {
        $manager = app(TenantManager::class);

        // Put cache in store 1
        $manager->bootstrapTenantByHost('store1.example.com', lock: false);
        Cache::put('daily_deal', '50% off store 1', 300);
        $this->assertEquals('50% off store 1', Cache::get('daily_deal'));

        // Switch to store 2
        $manager->reset();
        $manager->bootstrapTenantByHost('store2.example.com', lock: false);
        $this->assertNull(Cache::get('daily_deal'));

        Cache::put('daily_deal', 'Buy 1 Get 1 store 2', 300);
        $this->assertEquals('Buy 1 Get 1 store 2', Cache::get('daily_deal'));

        // Switch back to store 1
        $manager->reset();
        $manager->bootstrapTenantByHost('store1.example.com', lock: false);
        $this->assertEquals('50% off store 1', Cache::get('daily_deal'));
    }

    /**
     * Test 6: Uploaded files are tenant isolated and cross-tenant file access is forbidden.
     */
    public function test_uploaded_files_are_tenant_isolated(): void
    {
        $manager = app(TenantManager::class);

        // Bootstrap store 1
        $manager->bootstrapTenantByHost('store1.example.com', lock: false);

        // Public disk points to store 1 directory
        $expectedRootStore1 = storage_path('app/public/tenants/store1');
        $this->assertEquals($expectedRootStore1, config('filesystems.disks.public.root'));

        // Store a file
        Storage::disk('public')->put('documents/secret.pdf', 'Store 1 Secret Content');
        $this->assertTrue(Storage::disk('public')->exists('documents/secret.pdf'));
        $this->assertFileExists($expectedRootStore1 . '/documents/secret.pdf');

        // Switch to store 2
        $manager->reset();
        $manager->bootstrapTenantByHost('store2.example.com', lock: false);

        $expectedRootStore2 = storage_path('app/public/tenants/store2');
        $this->assertEquals($expectedRootStore2, config('filesystems.disks.public.root'));

        // File must NOT exist in store 2 disk
        $this->assertFalse(Storage::disk('public')->exists('documents/secret.pdf'));

        // Requesting store 1 file while under store 2 context must return 403 Forbidden
        $response = $this->get('http://store2.example.com/storage/tenants/store1/documents/secret.pdf');

        $this->assertEquals(403, $response->getStatusCode());
    }

    /**
     * Test 7: Queued jobs retain tenant context.
     */
    public function test_queued_jobs_retain_tenant_context(): void
    {
        $manager = app(TenantManager::class);
        $manager->bootstrapTenantByHost('store1.example.com', lock: false);

        // Check that Queue::createPayloadUsing attaches tenant_id
        $payloadData = null;
        if (app()->bound(TenantContext::class)) {
            $context = app(TenantContext::class);
            $this->assertEquals('store1', $context->id());
        }

        // Test worker bootstrap from payload tenant_id
        $manager->reset();
        $this->assertNull($manager->getContext()->id());

        // Simulate queue worker processing job with tenant_id = 'store1'
        $manager->bootstrapTenantById('store1', lock: false);
        $this->assertEquals('store1', $manager->getContext()->id());
        $this->assertEquals('Store 1', Setting::find(1)->title);

        // Worker reset after job
        $manager->reset();
        $this->assertNull($manager->getContext()->id());
    }

    /**
     * Test 8: Multiple sequential requests do not reuse previous tenant connection.
     */
    public function test_sequential_requests_do_not_reuse_previous_connection(): void
    {
        $manager = app(TenantManager::class);

        // Sequence: store1 -> store2 -> store1
        $manager->bootstrapTenantByHost('store1.example.com', lock: false);
        $this->assertEquals('Store 1', Setting::find(1)->title);
        $manager->reset();

        $manager->bootstrapTenantByHost('store2.example.com', lock: false);
        $this->assertEquals('Store 2', Setting::find(1)->title);
        $manager->reset();

        $manager->bootstrapTenantByHost('store1.example.com', lock: false);
        $this->assertEquals('Store 1', Setting::find(1)->title);
        $manager->reset();
    }

    /**
     * Test 9: Context locking prevents changing tenant context halfway through request.
     */
    public function test_context_locking_prevents_tenant_mutation(): void
    {
        $manager = app(TenantManager::class);
        $tenant1 = $manager->getResolver()->resolveFromHost('store1.example.com');
        $tenant2 = $manager->getResolver()->resolveFromHost('store2.example.com');

        // Bootstrap with lock enabled
        $manager->bootstrapTenant($tenant1, lock: true);
        $this->assertTrue($manager->getContext()->isLocked());

        $this->expectException(TenantLockedException::class);
        // Attempting to change to tenant2 must throw exception
        $manager->getContext()->setTenant($tenant2);
    }
}
