<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use App\Services\ReminderService;
use Database\Seeders\CategorySeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PagesRenderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([CategorySeeder::class, UserSeeder::class]);
    }

    public function test_public_pages_render(): void
    {
        $this->get('/')->assertOk()->assertSee('Helpdesk BPSDM');
        $this->get(route('tickets.create'))->assertOk()->assertSee('Formulir Pengajuan');
        $this->get(route('track.form'))->assertOk()->assertSee('Lacak Status Tiket');
        $this->get(route('login'))->assertOk()->assertSee('Masuk Area Internal');
    }

    public function test_admin_bidang_dashboard_and_ticket_list_render(): void
    {
        $admin = User::where('bidang', 'sertifikasi')->first();
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertSee('Dashboard');
        $this->actingAs($admin)->get(route('admin.tickets.index'))->assertOk()->assertSee('Daftar Tiket');
    }

    public function test_superadmin_management_pages_render(): void
    {
        $super = User::where('role', 'super_admin')->first();
        $this->actingAs($super)->get(route('admin.categories.index'))->assertOk()->assertSee('Kategori Layanan');
        $this->actingAs($super)->get(route('admin.categories.create'))->assertOk();
        $this->actingAs($super)->get(route('admin.subcategories.index'))->assertOk()->assertSee('Subkategori');
        $this->actingAs($super)->get(route('admin.users.index'))->assertOk()->assertSee('Akun Admin');
        $this->actingAs($super)->get(route('admin.users.create'))->assertOk();
    }

    public function test_pimpinan_dashboard_renders_with_charts(): void
    {
        $pimpinan = User::where('role', 'pimpinan')->first();
        $this->actingAs($pimpinan)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Pimpinan')
            ->assertSee('Rata-rata Waktu Penyelesaian');
    }

    public function test_admin_bidang_cannot_access_superadmin_pages(): void
    {
        $admin = User::where('bidang', 'lms')->first();
        $this->actingAs($admin)->get(route('admin.categories.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_reminder_service_escalates_unprocessed_tickets(): void
    {
        Mail::fake();

        // Tiket lama (10 hari lalu) yang belum diproses.
        $category = Category::first();
        $ticket = Ticket::create([
            'ticket_number' => 'HD-20260101-0001',
            'reporter_name' => 'Lama',
            'reporter_email' => 'lama@example.com',
            'category_id' => $category->id,
            'title' => 'Kendala lama',
            'description' => 'Belum ditangani',
            'status' => 'didistribusikan',
            'assigned_bidang' => $category->routing_bidang,
        ]);
        $ticket->created_at = now()->subDays(10);
        $ticket->save();

        $summary = app(ReminderService::class)->run();

        // Semua tahap (1,3,5) terpicu untuk tiket berusia 10 hari kerja lebih.
        $this->assertGreaterThanOrEqual(1, $summary[1]);
        $this->assertDatabaseHas('ticket_reminders', ['ticket_id' => $ticket->id, 'stage' => 5]);

        // Menjalankan lagi tidak mengirim ganda.
        $summary2 = app(ReminderService::class)->run();
        $this->assertSame(0, $summary2[1]);
    }
}
