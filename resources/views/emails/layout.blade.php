<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('subject', 'Helpdesk BPSDM')</title>
</head>
<body style="margin:0;padding:0;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e5e7eb;">
                    <tr>
                        <td style="background:#0b5a34;padding:20px 28px;">
                            <span style="color:#ffffff;font-size:18px;font-weight:bold;">Helpdesk BPSDM Jawa Barat</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;font-size:14px;line-height:1.6;">
                            @yield('content')
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 28px;background:#f9fafb;border-top:1px solid #e5e7eb;font-size:12px;color:#6b7280;">
                            {{ config('helpdesk.instansi.nama_panjang') }}<br>
                            Surel ini dikirim otomatis oleh Sistem Helpdesk. Mohon tidak membalas surel ini.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
