@extends('emails.layout')
@section('subject', 'Tiket Dibuka Kembali')
@section('content')
    <p>Pelapor mengajukan <strong>buka kembali</strong> atas tiket berikut karena penyelesaian dinilai belum memadai:</p>

    <table role="presentation" cellpadding="6" cellspacing="0" style="width:100%;border-collapse:collapse;margin:16px 0;">
        <tr>
            <td style="color:#6b7280;width:150px;">Nomor Tiket</td>
            <td style="font-weight:bold;color:#0b5a34;">{{ $ticket->ticket_number }}</td>
        </tr>
        <tr>
            <td style="color:#6b7280;">Kategori</td>
            <td>{{ $ticket->category->name }}</td>
        </tr>
        <tr>
            <td style="color:#6b7280;">Judul Kendala</td>
            <td>{{ $ticket->title }}</td>
        </tr>
        <tr>
            <td style="color:#6b7280;">Jumlah Dibuka Kembali</td>
            <td>{{ $ticket->reopened_count }}×</td>
        </tr>
    </table>

    <p>Status tiket kembali menjadi <strong>Diproses</strong>. Mohon ditindaklanjuti kembali.</p>

    <p style="margin:24px 0;">
        <a href="{{ route('admin.tickets.show', $ticket->ticket_number) }}" style="background:#0b5a34;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:6px;display:inline-block;">Buka di Dashboard</a>
    </p>
@endsection
