<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 10px; color: #1f2937; }
        h1 { font-size: 15px; margin: 0 0 2px; color: #0b5a34; }
        .sub { color: #6b7280; font-size: 9px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 4px 6px; text-align: left; }
        th { background: #e7f2ec; color: #094a2b; }
        tr:nth-child(even) td { background: #f9fafb; }
    </style>
</head>
<body>
    <h1>Daftar Tiket Helpdesk BPSDM</h1>
    <div class="sub">Dicetak: {{ $generatedAt->translatedFormat('d F Y, H:i') }} WIB — Total {{ $tickets->count() }} tiket</div>
    <table>
        <thead>
            <tr>
                <th>Nomor</th><th>Tanggal</th><th>Pelapor</th><th>Kategori</th><th>Judul</th><th>Prioritas</th><th>Status</th><th>Selesai</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tickets as $t)
                <tr>
                    <td>{{ $t->ticket_number }}</td>
                    <td>{{ $t->created_at->format('d/m/Y') }}</td>
                    <td>{{ $t->reporter_name }}</td>
                    <td>{{ $t->category->name ?? '-' }}</td>
                    <td>{{ $t->title }}</td>
                    <td>{{ $t->priority->label() }}</td>
                    <td>{{ $t->status->label() }}</td>
                    <td>{{ optional($t->resolved_at)->format('d/m/Y') ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
