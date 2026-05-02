<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Settlement in {{ $group->name }}</title>
</head>
<body style="margin:0;padding:0;background:#f3f6fb;font-family:Arial,sans-serif;color:#132238;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e6ebf4;">
                    <tr>
                        <td style="padding:28px;background:linear-gradient(135deg,#1b6fff,#2ec4b6);color:#ffffff;">
                            <div style="font-size:12px;letter-spacing:1px;text-transform:uppercase;font-weight:700;opacity:0.9;">SplitMate</div>
                            <h1 style="margin:10px 0 6px;font-size:26px;line-height:1.2;">New Settlement Recorded</h1>
                            <p style="margin:0;font-size:14px;opacity:0.95;">{{ $group->name }}</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 12px;font-size:16px;line-height:1.5;">
                                Hi {{ $recipient->name }},
                            </p>
                            <p style="margin:0 0 20px;font-size:15px;line-height:1.6;">
                                <strong>{{ $settlement->fromUser?->name ?? 'Someone' }}</strong> recorded a settlement payment to
                                <strong>{{ $settlement->toUser?->name ?? 'someone' }}</strong>.
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
                                   style="background:#f3f6fb;border-radius:12px;margin-bottom:24px;">
                                <tr>
                                    <td style="padding:20px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td>
                                                    <div style="font-size:13px;color:#687991;text-transform:uppercase;letter-spacing:0.6px;font-weight:700;margin-bottom:4px;">
                                                        Payment
                                                    </div>
                                                    <div style="font-size:20px;font-weight:700;color:#132238;">
                                                        {{ $settlement->fromUser?->name ?? 'Someone' }} paid {{ $settlement->toUser?->name ?? 'someone' }}
                                                    </div>
                                                </td>
                                                <td align="right">
                                                    <div style="font-size:13px;color:#687991;text-transform:uppercase;letter-spacing:0.6px;font-weight:700;margin-bottom:4px;">
                                                        Total
                                                    </div>
                                                    <div style="font-size:24px;font-weight:800;color:#1b6fff;">
                                                        {{ $group->currency_code }} {{ $amountFormatted }}
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>

                                        <hr style="border:none;border-top:1px solid #dde4ef;margin:16px 0;">

                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td style="font-size:13px;color:#687991;">
                                                    Date
                                                </td>
                                                <td align="right" style="font-size:13px;font-weight:600;color:#132238;">
                                                    {{ optional($settlement->settlement_date)->format('M d, Y') }}
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
                                   style="background:#132238;border-radius:12px;margin-bottom:20px;">
                                <tr>
                                    <td style="padding:20px;">
                                        <div style="font-size:13px;color:#93c5fd;text-transform:uppercase;letter-spacing:0.6px;font-weight:700;margin-bottom:8px;">
                                            Your Current Balance
                                        </div>
                                        <div style="font-size:24px;font-weight:800;color:#ffffff;margin-bottom:12px;">
                                            {{ $group->currency_code }} {{ number_format(abs(($recipientSnapshot['net_balance_cents'] ?? 0) / 100), 2) }}
                                        </div>
                                        <div style="font-size:14px;color:#dbeafe;line-height:1.5;">
                                            @if (($recipientSnapshot['net_balance_cents'] ?? 0) > 0)
                                                You should receive money from the group.
                                            @elseif (($recipientSnapshot['net_balance_cents'] ?? 0) < 0)
                                                You currently owe money in the group.
                                            @else
                                                You are settled up right now.
                                            @endif
                                        </div>

                                        @if (!empty($recipientSnapshot['owes_lines']) || !empty($recipientSnapshot['owed_by_lines']))
                                            <hr style="border:none;border-top:1px solid rgba(255,255,255,0.12);margin:16px 0;">
                                        @endif

                                        @foreach (($recipientSnapshot['owes_lines'] ?? []) as $line)
                                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:8px;">
                                                <tr>
                                                    <td style="font-size:13px;color:#cbd5e1;">You owe {{ $line['name'] }}</td>
                                                    <td align="right" style="font-size:13px;font-weight:700;color:#fca5a5;">
                                                        {{ $group->currency_code }} {{ number_format($line['amount_cents'] / 100, 2) }}
                                                    </td>
                                                </tr>
                                            </table>
                                        @endforeach

                                        @foreach (($recipientSnapshot['owed_by_lines'] ?? []) as $line)
                                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:8px;">
                                                <tr>
                                                    <td style="font-size:13px;color:#cbd5e1;">{{ $line['name'] }} owes you</td>
                                                    <td align="right" style="font-size:13px;font-weight:700;color:#86efac;">
                                                        {{ $group->currency_code }} {{ number_format($line['amount_cents'] / 100, 2) }}
                                                    </td>
                                                </tr>
                                            </table>
                                        @endforeach
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 8px;font-size:13px;color:#687991;line-height:1.5;">
                                Open the SplitMate app to review the record and updated balances.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 28px;background:#f3f6fb;border-top:1px solid #e6ebf4;">
                            <p style="margin:0;font-size:11px;color:#aab4c4;line-height:1.5;">
                                You are receiving this because email notifications are enabled for <strong>{{ $group->name }}</strong>.
                                To turn off notifications, open Group Settings in the SplitMate app.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
