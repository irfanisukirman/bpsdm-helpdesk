@extends('emails.layout')
@section('subject', 'Tiket Anda Telah Selesai')
@section('content')
    <p>Yth. {{ $ticket->reporter_name }},</p>

    <p>Kami informasikan bahwa tiket Anda dengan nomor
        <strong style="color:#0b5a34;">{{ $ticket->ticket_number }}</strong>
        telah <strong>selesai</strong> ditangani.</p>

    <table role="presentation" cellpadding="6" cellspacing="0" style="width:100%;border-collapse:collapse;margin:16px 0;">
        <tr>
            <td style="color:#6b7280;width:150px;">Judul Kendala</td>
            <td>{{ $ticket->title }}</td>
        </tr>
        <tr>
            <td style="color:#6b7280;">Diselesaikan pada</td>
            <td>{{ optional($ticket->resolved_at)->translatedFormat('d F Y, H:i') }} WIB</td>
        </tr>
    </table>

    <p>Rincian penyelesaian, tindak lanjut, dan analisis dapat Anda lihat pada halaman pelacakan tiket.</p>

    <p style="margin:24px 0;">
        <a href="{{ route('track.form') }}" style="background:#0b5a34;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:6px;display:inline-block;">Lihat Detail Penyelesaian</a>
    </p>

    <p style="color:#6b7280;">Apabila Anda merasa penyelesaian belum memadai, Anda dapat mengajukan buka kembali tiket dari halaman pelacakan dalam batas {{ config('helpdesk.reopen_window_working_days') }} hari kerja.</p>

    <p style="color:#6b7280;">Salam,<br>Tim Helpdesk BPSDM Provinsi Jawa Barat</p>
@endsection
