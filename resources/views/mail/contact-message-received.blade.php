<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Contact message - {{ $companyName }}</title></head>
<body style="margin:0;padding:0;background:#f2f6f3;color:#183d2c;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td align="center" style="padding:24px 12px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;background:#fff;border:1px solid #dce8df;border-radius:12px;overflow:hidden;">
            <tr><td style="padding:28px;background:#062b22;border-top:5px solid #30aa47;">
                <p style="margin:0 0 12px;color:#98d6a3;font-size:11px;letter-spacing:2px;">CONTACT US</p>
                <h1 style="margin:0;color:#fff;font-size:25px;line-height:1.4;">New message for {{ $companyName }}</h1>
            </td></tr>
            <tr><td style="padding:28px;">
                <h2 style="margin:0 0 20px;font-size:20px;line-height:1.5;word-break:break-word;">{{ $contactMessage->subject }}</h2>
                <p style="margin:0 0 8px;font-size:14px;line-height:1.6;"><strong>From:</strong> {{ $contactMessage->name }}</p>
                <p style="margin:0 0 8px;font-size:14px;line-height:1.6;word-break:break-all;"><strong>Email:</strong> <a href="mailto:{{ $contactMessage->email }}" style="color:#268839;">{{ $contactMessage->email }}</a></p>
                <p style="margin:0 0 24px;font-size:13px;line-height:1.6;color:#72877a;"><strong>Received:</strong> {{ \App\Support\LocalTime::format($contactMessage->created_at) }}</p>
                <div style="padding:20px;background:#eff8f1;border:1px solid #c3dbc9;border-radius:8px;font-size:15px;line-height:1.8;word-break:break-word;">{!! nl2br(e($contactMessage->message)) !!}</div>
                <p style="margin:24px 0 0;font-size:13px;line-height:1.7;color:#72877a;">You can reply directly to this email to contact the sender. This message is also saved in the Contact Messages admin module.</p>
            </td></tr>
            <tr><td align="center" style="padding:20px;border-top:1px solid #dce8df;background:#f7fbf8;font-size:12px;line-height:1.7;color:#1d662b;">{{ $companyName }}<br>Contact message #{{ $contactMessage->id }}</td></tr>
        </table>
    </td></tr></table>
</body>
</html>
