@extends('emails.layout')
@section('subject', 'Tiket Baru Masuk')
@section('content')
    <p>Terdapat tiket baru yang memerlukan penanganan:</p>

    <table role="presentation" cellpadding="6" cellspacing="0" style="width:100%;border-collapse:collapse;margin:16px 0;">
        <tr>
            <td style="color:#6b7280;width:150px;">Nomor Tiket</td>
            <td style="font-weight:bold;color:#008a41;">{{ $ticket->ticket_number }}</td>
        </tr>
        <tr>
            <td style="color:#6b7280;">Kategori</td>
            <td>{{ $ticket->category->name }}</td>
        </tr>
        <tr>
            <td style="color:#6b7280;">Nama Pelapor</td>
            <td>{{ $ticket->reporter_name }}</td>
        </tr>
        <tr>
            <td style="color:#6b7280;">Judul Kendala</td>
            <td>{{ $ticket->title }}</td>
        </tr>
        <tr>
            <td style="color:#6b7280;">Prioritas</td>
            <td>{{ ucfirst($ticket->priority->value) }}</td>
        </tr>
        <tr>
            <td style="color:#6b7280;">Tanggal Pengajuan</td>
            <td>{{ $ticket->created_at->translatedFormat('d F Y, H:i') }} WIB</td>
        </tr>
    </table>

    <p style="margin:24px 0;">
        <a href="{{ route('admin.tickets.show', $ticket->ticket_number) }}" style="background:#008a41;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:6px;display:inline-block;">Buka di Dashboard</a>
    </p>

    <p style="color:#6b7280;">Mohon segera ditindaklanjuti sesuai batas waktu penanganan.</p>
@endsection
