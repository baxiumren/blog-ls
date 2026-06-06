@php
    $site = \App\Models\Setting::get('site_name') ?: 'LiveScore';
@endphp
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f4f4f5;font-family:-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f5;padding:24px 12px;">
        <tr><td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e4e4e7;">
                <tr><td style="background:#2563eb;padding:20px 28px;">
                    <span style="color:#ffffff;font-size:20px;font-weight:700;letter-spacing:-0.5px;">{{ $site }}</span>
                </td></tr>
                <tr><td style="padding:28px;color:#18181b;font-size:15px;line-height:1.7;">
                    {!! $bodyHtml !!}
                </td></tr>
                <tr><td style="padding:20px 28px;border-top:1px solid #e4e4e7;background:#fafafa;">
                    <p style="margin:0;color:#a1a1aa;font-size:12px;line-height:1.6;">
                        You're receiving this because you subscribed to {{ $site }}.<br>
                        <a href="{{ $unsubscribeUrl }}" style="color:#71717a;text-decoration:underline;">Unsubscribe</a>
                    </p>
                </td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>