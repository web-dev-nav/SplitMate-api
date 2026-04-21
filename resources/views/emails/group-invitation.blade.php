<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SplitMate Invitation</title>
</head>
<body style="margin:0;padding:0;background:#f3f6fb;font-family:Arial,sans-serif;color:#132238;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e6ebf4;">
                    <tr>
                        <td style="padding:28px;background:linear-gradient(135deg,#1b6fff,#2ec4b6);color:#ffffff;">
                            <div style="font-size:12px;letter-spacing:1px;text-transform:uppercase;font-weight:700;opacity:0.9;">SplitMate</div>
                            <h1 style="margin:10px 0 6px;font-size:26px;line-height:1.2;">You are invited to a group</h1>
                            <p style="margin:0;font-size:14px;opacity:0.95;">Join and start splitting expenses seamlessly.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 12px;font-size:16px;line-height:1.5;">Hi there,</p>
                            <p style="margin:0 0 14px;font-size:15px;line-height:1.6;">
                                <strong>{{ $inviterName ?? 'A group member' }}</strong> invited you to join
                                <strong>{{ $groupName }}</strong> on SplitMate.
                            </p>
                            <p style="margin:0 0 22px;font-size:14px;color:#4b5b74;line-height:1.6;">
                                Click the button below to accept the invitation. This link expires on
                                <strong>{{ optional($expiresAt)->format('M d, Y h:i A') }}</strong>.
                            </p>

                            <table role="presentation" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center" style="border-radius:10px;background:#1b6fff;">
                                        <a href="{{ $acceptUrl }}" style="display:inline-block;padding:12px 22px;font-size:15px;font-weight:700;color:#ffffff;text-decoration:none;">
                                            Accept Invitation
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:22px 0 0;font-size:12px;color:#687991;line-height:1.5;">
                                If the button does not work, copy and paste this link into your browser:<br>
                                <a href="{{ $acceptUrl }}" style="color:#1b6fff;text-decoration:none;">{{ $acceptUrl }}</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
