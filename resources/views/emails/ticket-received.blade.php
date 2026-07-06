@extends('emails.layout')
@section('subject', 'Tiket Anda Telah Diterima')
@section('content')
    <p>Yth. {{ $ticket->reporter_name }},</p>

    <p>Terima kasih telah menghubungi Helpdesk BPSDM Provinsi Jawa Barat. Laporan Anda telah kami terima dengan rincian berikut:</p>

    <table role="presentation" cellpadding="6" cellspacing="0" style="width:100%;border-collapse:collapse;margin:16px 0;">
        <tr>
            <td style="color:#6b7280;width:150px;">Nomor Tiket</td>
            <td style="font-weight:bold;font-size:16px;color:#0b5a34;">{{ $ticket->ticket_number }}</td>
        </tr>
        <tr>
            <td style="color:#6b7280;">Judul Kendala</td>
            <td>{{ $ticket->title }}</td>
        </tr>
        <tr>
            <td style="color:#6b7280;">Kategori</td>
            <td>{{ $ticket->category->name }}</td>
        </tr>
        <tr>
            <td style="color:#6b7280;">Status</td>
            <td>Diterima</td>
        </tr>
        <tr>
            <td style="color:#6b7280;">Tanggal</td>
            <td>{{ $ticket->created_at->translatedFormat('d F Y, H:i') }} WIB</td>
        </tr>
    </table>

    <p><strong>Mohon simpan nomor tiket di atas.</strong> Anda dapat memantau status penanganan kapan saja melalui halaman pelacakan dengan memasukkan nomor tiket dan alamat surel Anda.</p>

    <p style="margin:24px 0;">
        <a href="{{ route('track.form') }}" style="background:#0b5a34;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:6px;display:inline-block;">Lacak Tiket Saya</a>
    </p>

    <p style="color:#6b7280;">Salam,<br>Tim Helpdesk BPSDM Provinsi Jawa Barat</p>
@endsection
