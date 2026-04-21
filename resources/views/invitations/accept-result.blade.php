<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SplitMate Invitation</title>
</head>
<body style="margin:0;padding:0;background:#f2f5fb;font-family:Arial,sans-serif;color:#122238;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="min-height:100vh;padding:20px;">
        <tr>
            <td align="center" valign="middle">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;background:#ffffff;border:1px solid #e3eaf4;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td style="padding:24px;background:{{ $status === 'success' ? 'linear-gradient(135deg,#00a86b,#2ec4b6)' : 'linear-gradient(135deg,#ff6b6b,#ff9f43)' }};color:#ffffff;">
                            <div style="font-size:12px;letter-spacing:1px;text-transform:uppercase;font-weight:700;opacity:0.9;">SplitMate</div>
                            <h1 style="margin:8px 0 0;font-size:28px;line-height:1.2;">{{ $title }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 14px;font-size:16px;line-height:1.6;">{{ $message }}</p>

                            @if(!empty($groupName))
                                <div style="margin:0 0 18px;padding:14px;border-radius:12px;background:#f5f9ff;border:1px solid #d8e7ff;">
                                    <div style="font-size:12px;text-transform:uppercase;letter-spacing:0.6px;color:#5a6f90;font-weight:700;">Group</div>
                                    <div style="margin-top:4px;font-size:18px;font-weight:700;color:#12345f;">{{ $groupName }}</div>
                                </div>
                            @endif

                            <p style="margin:0;font-size:14px;color:#51627f;line-height:1.6;">
                                Open the SplitMate app and sign in with this email to continue.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
