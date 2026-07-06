<?php

namespace Tests\Feature;

use App\Enums\TicketStatus;
use App\Mail\NewTicketAdminMail;
use App\Mail\TicketReceivedMail;
use App\Mail\TicketResolvedMail;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TicketFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([CategorySeeder::class, UserSeeder::class]);
    }

    public function test_public_can_submit_ticket_and_it_is_distributed(): void
    {
        Mail::fake();
        Storage::fake('local');

        $category = Category::where('routing_bidang', 'sertifikasi')->first();

        $response = $this->post('/tiket', [
            'reporter_name' => 'Budi Santoso',
            'reporter_email' => 'budi@example.com',
            'category_id' => $category->id,
            'title' => 'Sertifikat belum terbit',
            'description' => 'Sudah lulus uji tetapi sertifikat belum tersedia.',
            'consent' => '1',
            'attachments' => [UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf')],
        ]);

        $response->assertRedirect(route('tickets.success'));

        $ticket = Ticket::first();
        $this->assertNotNull($ticket);
        $this->assertMatchesRegularExpression('/^HD-\d{8}-\d{4}$/', $ticket->ticket_number);
        // Distribusi otomatis mengubah status & mengisi bidang.
        $this->assertSame(TicketStatus::Didistribusikan, $ticket->status);
        $this->assertSame('sertifikasi', $ticket->assigned_bidang);
        $this->assertSame(1, $ticket->attachments()->count());

        // Aktivitas tercatat.
        $this->assertDatabaseHas('ticket_activities', ['ticket_id' => $ticket->id, 'action' => 'dibuat']);
        $this->assertDatabaseHas('ticket_activities', ['ticket_id' => $ticket->id, 'action' => 'didistribusikan']);

        // Notifikasi terkirim.
        Mail::assertQueued(TicketReceivedMail::class);
        Mail::assertQueued(NewTicketAdminMail::class);
    }

    public function test_honeypot_blocks_spam_submission(): void
    {
        $category = Category::first();

        $response = $this->post('/tiket', [
            'reporter_name' => 'Spam Bot',
            'reporter_email' => 'spam@example.com',
            'category_id' => $category->id,
            'title' => 'x',
            'description' => 'x',
            'consent' => '1',
            'website' => 'http://spam.example', // honeypot terisi
        ]);

        $response->assertSessionHasErrors('website');
        $this->assertSame(0, Ticket::count());
    }

    public function test_consent_is_required(): void
    {
        $category = Category::first();

        $response = $this->post('/tiket', [
            'reporter_name' => 'Tanpa Consent',
            'reporter_email' => 'noconsent@example.com',
            'category_id' => $category->id,
            'title' => 'Judul',
            'description' => 'Uraian',
        ]);

        $response->assertSessionHasErrors('consent');
        $this->assertSame(0, Ticket::count());
    }

    public function test_tracking_requires_matching_email(): void
    {
        $ticket = $this->makeDistributedTicket();

        // Surel salah → tidak tampil.
        $this->post('/lacak', [
            'ticket_number' => $ticket->ticket_number,
            'reporter_email' => 'salah@example.com',
        ])->assertSessionHasErrors('ticket_number');

        // Surel benar → tampil.
        $this->post('/lacak', [
            'ticket_number' => $ticket->ticket_number,
            'reporter_email' => $ticket->reporter_email,
        ])->assertOk()->assertSee($ticket->ticket_number);
    }

    public function test_admin_bidang_can_process_and_resolve_only_own_bidang(): void
    {
        Mail::fake();
        $ticket = $this->makeDistributedTicket(); // bidang sertifikasi

        $otherAdmin = User::where('bidang', 'lms')->first();
        // Admin bidang lain tidak boleh melihat.
        $this->actingAs($otherAdmin)->get(route('admin.tickets.show', $ticket))->assertForbidden();

        $admin = User::where('bidang', 'sertifikasi')->first();
        $this->actingAs($admin)->get(route('admin.tickets.show', $ticket))->assertOk();

        // Proses.
        $this->actingAs($admin)->post(route('admin.tickets.process', $ticket))->assertRedirect();
        $ticket->refresh();
        $this->assertSame(TicketStatus::Diproses, $ticket->status);
        $this->assertNotNull($ticket->first_processed_at);

        // Selesaikan tanpa mengisi kolom wajib → gagal.
        $this->actingAs($admin)->post(route('admin.tickets.resolve', $ticket), [])
            ->assertSessionHasErrors(['analysis', 'follow_up', 'resolution']);

        // Selesaikan lengkap.
        $this->actingAs($admin)->post(route('admin.tickets.resolve', $ticket), [
            'analysis' => 'Analisis',
            'follow_up' => 'Tindak lanjut',
            'resolution' => 'Selesai diperbaiki',
        ])->assertRedirect();

        $ticket->refresh();
        $this->assertSame(TicketStatus::Selesai, $ticket->status);
        $this->assertNotNull($ticket->resolved_at);
        Mail::assertQueued(TicketResolvedMail::class);
    }

    public function test_pimpinan_cannot_handle_tickets(): void
    {
        $ticket = $this->makeDistributedTicket();
        $pimpinan = User::where('role', 'pimpinan')->first();

        $this->actingAs($pimpinan)->get(route('admin.tickets.show', $ticket))->assertOk(); // baca boleh
        $this->actingAs($pimpinan)->post(route('admin.tickets.process', $ticket))->assertForbidden();
    }

    public function test_ticket_numbers_are_unique_across_many_submissions(): void
    {
        Mail::fake();
        $category = Category::first();
        $numbers = [];
        for ($i = 0; $i < 5; $i++) {
            $res = $this->post('/tiket', [
                'reporter_name' => "User {$i}",
                'reporter_email' => "user{$i}@example.com",
                'category_id' => $category->id,
                'title' => "Judul {$i}",
                'description' => 'Uraian kendala',
                'consent' => '1',
            ]);
            $res->assertRedirect();
        }
        $numbers = Ticket::pluck('ticket_number')->all();
        $this->assertCount(5, $numbers);
        $this->assertCount(5, array_unique($numbers));
    }

    protected function makeDistributedTicket(): Ticket
    {
        Mail::fake();
        $category = Category::where('routing_bidang', 'sertifikasi')->first();

        $this->post('/tiket', [
            'reporter_name' => 'Budi Santoso',
            'reporter_email' => 'budi@example.com',
            'category_id' => $category->id,
            'title' => 'Sertifikat belum terbit',
            'description' => 'Uraian kendala sertifikat.',
            'consent' => '1',
        ]);

        return Ticket::latest('id')->first();
    }
}
