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
                                <strong>{{ $companyName }}</strong>. Some of them need to be sent again
                                before the office can approve your folder.
                            </div>

                            {{-- The papers themselves, each with whatever the desk wrote about it.
                                 A list that only says "something is wrong" sends the employer back
                                 to guess, which is the whole reason this email exists. --}}
                            <div style="background:#FDECEA; border:1px solid #F0B4AC; border-radius:12px; padding:14px 16px; margin-bottom:16px;">
                                <div style="font-size:12px; font-weight:700; color:#8A1C13; margin-bottom:8px;">
                                    To resubmit
                                </div>
                                @foreach($documents as $document)
                                    <div style="font-size:12.5px; color:#363D37; line-height:1.6; margin-bottom:{{ $loop->last ? '0' : '8px' }};">
                                        <strong>{{ $document['label'] }}</strong>
                                        @if(!empty($document['note']))
                                            <br>
                                            <span style="color:#5B655C;">{{ $document['note'] }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <div style="background:#F4F7F4; border:1px solid #EAEFEA; border-radius:12px; padding:14px 16px; margin-bottom:24px;">
                                <div style="font-size:12px; font-weight:700; color:#1D6F27; margin-bottom:6px;">
                                    Note from PESO
                                </div>
                                <div style="font-size:12.5px; color:#363D37; line-height:1.6;">
                                    {{ $remarks }}
                                </div>
                            </div>

                            <div style="text-align:center; margin-bottom:24px;">
                                <a href="{{ route('login') }}"
                                   style="display:inline-block; background:#3AA346; color:#ffffff; text-decoration:none;
                                          font-size:14px; font-weight:700; padding:12px 28px; border-radius:10px;">
                                    Sign in and resubmit
                                </a>
                            </div>

                            <div style="font-size:12px; color:#5B655C; line-height:1.6;">
                                Reviewed by {{ $staffName }} of PESO. Only the documents listed above
                                need to be sent again — the rest stay as they are. Your account keeps
                                working, but vacancies you post stay hidden from jobseekers until the
                                folder is approved.
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
