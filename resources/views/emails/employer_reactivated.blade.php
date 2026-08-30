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
                            <div style="font-size:13.5px; color:#363D37; line-height:1.7; margin-bottom:16px;">
                                PESO staff have switched the account of
                                <strong>{{ $companyName }}</strong> back on. You can sign in and use
                                Job Smart as before.
                            </div>

                            <div style="background:#EDF7EE; border:1px solid #A8D6AE; border-radius:12px; padding:14px 16px; margin-bottom:24px;">
                                <div style="font-size:12.5px; color:#1D6F27; line-height:1.6;">
                                    @if($reopenedPostings > 0)
                                        <strong>{{ $reopenedPostings }}</strong>
                                        job posting{{ $reopenedPostings === 1 ? ' is' : 's are' }}
                                        visible to jobseekers again.
                                    @else
                                        Your account is active, and any new vacancy you post is visible
                                        to jobseekers right away.
                                    @endif
                                </div>
                            </div>

                            <div style="text-align:center; margin-bottom:24px;">
                                <a href="{{ route('login') }}"
                                   style="display:inline-block; background:#3AA346; color:#ffffff; text-decoration:none;
                                          font-size:14px; font-weight:700; padding:12px 28px; border-radius:10px;">
                                    Sign in to your account
                                </a>
                            </div>

                            <div style="font-size:12px; color:#5B655C; line-height:1.6;">
                                Reopened by {{ $staffName }} of PESO. Posting a vacancy at least once a
                                month keeps the account active — if a month passes with no new vacancy,
                                we will write again to ask about your status.
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
