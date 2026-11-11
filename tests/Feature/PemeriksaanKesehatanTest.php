<?php

use App\Models\User;
use Database\Seeders\DataPortofolioSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

it('mengembalikan pemeriksaan kesehatan aplikasi yang berhasil', function (): void {
    $this->get('/up')->assertOk();
});

it('memuat homepage portofolio publik', function (): void {
    $this->get('/')->assertOk();
});

it('menampilkan halaman login admin', function (): void {
    $this->get('/admin/login')->assertOk();
});

it('mengalihkan guest ke halaman login admin', function (): void {
    $this->get('/admin/dashboard')
        ->assertRedirect(route('admin.login'));
});

it('menyimpan konten contoh pada skema berbahasa Indonesia', function (): void {
    $this->seed(DataPortofolioSeeder::class);

    $this->assertDatabaseHas('profil', ['nama_lengkap' => 'Fadil']);
    $this->assertDatabaseHas('proyek', ['slug' => 'integrated-management-information-system']);
    $this->assertDatabaseHas('artikel', ['slug' => 'turning-business-needs-into-system-design']);

    $this->get('/')
        ->assertOk()
        ->assertSee('Fadil')
        ->assertSee('Integrated Management Information System');
});

it('mengautentikasi administrator dan menolak akun biasa', function (): void {
    $admin = User::factory()->create([
        'email' => 'admin@example.test',
        'password' => 'password-sangat-rahasia',
        'adalah_admin' => true,
    ]);

    $this->post('/admin/login', [
        'email' => $admin->email,
        'password' => 'password-sangat-rahasia',
    ])->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($admin);

    auth()->logout();

    $pengguna = User::factory()->create([
        'email' => 'pengguna@example.test',
        'password' => 'password-sangat-rahasia',
    ]);

    $this->actingAs($pengguna)
        ->get('/admin/dashboard')
        ->assertForbidden();
});

it('mengelola project melalui dashboard CMS', function (): void {
    $admin = User::factory()->create(['adalah_admin' => true]);

    $this->actingAs($admin)
        ->get('/admin/dashboard')
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertSee('Projects');

    $this->actingAs($admin)
        ->get('/admin/cms/projects')
        ->assertOk()
        ->assertSee('Projects')
        ->assertSee('Add Project');

    $this->post('/admin/cms/projects', [
        'judul' => 'Portfolio CMS',
        'slug' => 'portfolio-cms',
        'kategori' => 'Web application',
        'ringkasan' => 'CMS for a technical portfolio.',
        'tumpukan_teknologi' => "Laravel\nBlade",
        'unggulan' => '1',
        'status' => 'draft',
    ])->assertRedirect(route('admin.cms.index', 'projects'));

    $this->assertDatabaseHas('proyek', [
        'slug' => 'portfolio-cms',
        'judul' => 'Portfolio CMS',
        'status' => 'draft',
        'unggulan' => true,
    ]);

    expect(\App\Models\Proyek::query()->where('slug', 'portfolio-cms')->first()?->tumpukan_teknologi)
        ->toBe(['Laravel', 'Blade']);
});

it('membatasi seluruh halaman CMS untuk admin', function (): void {
    $pengguna = User::factory()->create();

    $this->actingAs($pengguna)
        ->get('/admin/cms/projects')
        ->assertForbidden();
});

it('membuka halaman listing dan form untuk semua modul CMS', function (): void {
    $admin = User::factory()->create(['adalah_admin' => true]);
    $this->actingAs($admin);

    foreach (['profile', 'projects', 'skill-categories', 'skills', 'experience', 'articles', 'messages', 'settings'] as $resource) {
        $this->get("/admin/cms/{$resource}")->assertOk();
    }

    foreach (['projects', 'skill-categories', 'skills', 'experience', 'articles'] as $resource) {
        $this->get("/admin/cms/{$resource}/create")->assertOk();
    }

    $this->get('/admin/cms/profile/edit')->assertOk();
    $this->get('/admin/cms/settings/edit')->assertOk();
});

it('menyimpan profile dan site settings dari halaman singleton CMS', function (): void {
    $admin = User::factory()->create(['adalah_admin' => true]);
    $this->actingAs($admin);

    $this->put('/admin/cms/profile', [
        'nama_lengkap' => 'Fadil',
        'jabatan' => 'Software Engineer',
        'ringkasan' => 'Building useful software.',
        'email' => 'fadil@example.test',
    ])->assertRedirect(route('admin.cms.index', 'profile'));

    $this->assertDatabaseHas('profil', [
        'nama_lengkap' => 'Fadil',
        'email' => 'fadil@example.test',
    ]);

    $this->put('/admin/cms/settings', [
        'nama_situs' => 'OBSIDIAN SIGNAL',
        'slogan' => 'Portfolio and CMS',
        'mode_pemeliharaan' => '1',
    ])->assertRedirect(route('admin.cms.index', 'settings'));

    $this->assertDatabaseHas('pengaturan_situs', [
        'nama_situs' => 'OBSIDIAN SIGNAL',
        'mode_pemeliharaan' => true,
    ]);
});

it('membuat akun admin dari konfigurasi env dengan password yang di-hash', function (): void {
    config([
        'admin.bootstrap.name' => 'Admin Portfolio',
        'admin.bootstrap.email' => 'admin-env@example.test',
        'admin.bootstrap.password' => 'password-env-yang-aman',
    ]);

    expect(Artisan::call('admin:create', ['--from-env' => true]))->toBe(0)
        ->and(Artisan::output())->not->toContain('password-env-yang-aman');

    $user = User::query()->where('email', 'admin-env@example.test')->firstOrFail();

    expect($user->name)->toBe('Admin Portfolio')
        ->and($user->adalah_admin)->toBeTrue()
        ->and(Hash::check('password-env-yang-aman', $user->password))->toBeTrue()
        ->and($user->password)->not->toBe('password-env-yang-aman');

    $this->post('/admin/login', [
        'email' => 'admin-env@example.test',
        'password' => 'password-env-yang-aman',
    ])->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('menolak membuat akun admin env jika kredensial tidak lengkap atau password lemah', function (): void {
    config([
        'admin.bootstrap.name' => 'Admin Portfolio',
        'admin.bootstrap.email' => 'admin-env@example.test',
        'admin.bootstrap.password' => '',
    ]);

    expect(Artisan::call('admin:create', ['--from-env' => true]))->toBe(1)
        ->and(User::query()->where('email', 'admin-env@example.test')->exists())->toBeFalse();

    config('admin.bootstrap.password', 'short');

    expect(Artisan::call('admin:create', ['--from-env' => true]))->toBe(1)
        ->and(User::query()->where('email', 'admin-env@example.test')->exists())->toBeFalse();
});
