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
                                PESO staff have gone through the documents submitted by
                                <strong>{{ $companyName }}</strong> and approved them. Nothing further is
                                needed from you.
                            </div>

                            <div style="background:#EDF7EE; border:1px solid #A8D6AE; border-radius:12px; padding:14px 16px; margin-bottom:24px;">
                                <div style="font-size:12.5px; color:#1D6F27; line-height:1.7;">
                                    You can now post job vacancies and request an in-house interview at
                                    the PESO Office.
                                    @if($releasedPostings > 0)
                                        <br><br>
                                        {{-- The employer wrote these and watched them stay hidden, so
                                             the news that they are out is worth its own line. --}}
                                        <strong>{{ $releasedPostings }}</strong>
                                        vacanc{{ $releasedPostings === 1 ? 'y you posted while waiting is' : 'ies you posted while waiting are' }}
                                        now visible to jobseekers. You do not need to post
                                        {{ $releasedPostings === 1 ? 'it' : 'them' }} again.
                                    @endif
                                    @if($jobFairInvites > 0)
                                        <br><br>
                                        You were also invited to <strong>{{ $jobFairInvites }}</strong>
                                        upcoming job fair event{{ $jobFairInvites === 1 ? '' : 's' }}.
                                        Sign in to accept or decline.
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
                                Approved by {{ $staffName }} of PESO. Keep an eye on the expiry dates of
                                your documents — we will write again before any of them run out.
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
