<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="margin:0; padding:0; background:#F4F7F4; font-family:'Segoe UI', Arial, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="padding:32px 16px;">
        <tr>
            <td align="center">
                <table width="480" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.08);">
                    <tr>
                        <td style="background:#3AA346; padding:24px 32px; text-align:center;">
                            <div style="color:#fff; font-size:16px; font-weight:700;">PUBLIC EMPLOYMENT SERVICE OFFICE</div>
                            <div style="color:rgba(255,255,255,0.9); font-size:12px; margin-top:2px;">A Web-based Job Management System</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <div style="font-size:15px; font-weight:700; color:#1D6F27; margin-bottom:12px;">
                                Hi {{ $contactName }},
                            </div>
            @if($isFinal)
                            <div style="background:#FDECEC; border:1px solid #D9534F; border-radius:12px; padding:12px 16px; margin-bottom:16px;">
                                <div style="font-size:12.5px; color:#8A2A27; line-height:1.6;">
                                    <strong>Second notice.</strong> We wrote to you about this a month ago
                                    and have not heard back.
                                </div>
                            </div>
            @endif
                            <div style="font-size:13.5px; color:#363D37; line-height:1.7; margin-bottom:16px;">
                                We have not received a new job vacancy from
                                <strong>{{ $companyName }}</strong>
                                @if($lastPostedOn)
                                    since <strong>{{ $lastPostedOn }}</strong>
                                @else
                                    since your account was registered
                                @endif
                                — {{ $monthsQuiet }} month{{ $monthsQuiet === 1 ? '' : 's' }} now,
                                so we are writing to ask how your company is doing.
                            </div>
                            <div style="font-size:13.5px; color:#363D37; line-height:1.7; margin-bottom:24px;">
                                Please sign in and tell us your status — still hiring, paused for now,
                                or closed down. It takes a minute, and it keeps your account and your
                                postings active.
                            </div>

                            <div style="text-align:center; margin-bottom:24px;">
                                <a href="{{ route('login') }}"
                                   style="display:inline-block; background:#3AA346; color:#ffffff; text-decoration:none;
                                          font-size:14px; font-weight:700; padding:12px 28px; border-radius:10px;">
                                    Sign in and update your status
                                </a>
                            </div>

                            <div style="background:#FFF7E6; border:1px solid #E0B64D; border-radius:12px; padding:14px 16px; margin-bottom:20px;">
                                <div style="font-size:12.5px; color:#6B4500; line-height:1.6;">
                                @if($isFinal)
                                    If we do not hear from you within <strong>{{ $graceDays }} days</strong>
                                    (by <strong>{{ $disableOn }}</strong>), PESO staff will review your
                                    account and may set it to inactive, which hides your job postings from
                                    jobseekers. Nothing is deleted — you can sign in, tell us what happened,
                                    and PESO staff will switch your account back on.
                                @else
                                    Answering keeps your account and your postings active. If we still hear
                                    nothing, we will write once more next month before anything changes.
                                @endif
                                </div>
                            </div>

                            <div style="font-size:12px; color:#5B655C; line-height:1.6;">
                                Posting a new vacancy also counts as an answer. If you have already posted
                                one since this email was prepared, you can ignore this message.
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 32px; border-top:1px solid #EAEFEA; text-align:center;">
                            <div style="font-size:11px; color:#909B91;">PESO Cagayan de Oro — Job Smart System</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
