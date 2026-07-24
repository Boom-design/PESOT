<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="margin:0; padding:0; background:#f0f9f6; font-family:'Segoe UI', sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="padding:32px 16px;">
        <tr>
            <td align="center">
                <table width="480" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.08);">
                    <tr>
                        <td style="background:linear-gradient(90deg,#90d870,#4dd9c0); padding:24px 32px; text-align:center;">
                            <div style="color:#fff; font-size:16px; font-weight:700;">PUBLIC EMPLOYMENT SERVICE OFFICE</div>
                            <div style="color:rgba(255,255,255,0.9); font-size:12px; margin-top:2px;">A Web-based Job Management System</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <div style="font-size:15px; font-weight:700; color:#2d7a5f; margin-bottom:12px;">
                                Hi {{ $userName }},
                            </div>
                            <div style="font-size:13.5px; color:#555; line-height:1.7; margin-bottom:24px;">
                                We received a request to reset your PESO Job Smart account password. Use the verification code below to proceed. This code will expire in 15 minutes.
                            </div>
                            <div style="text-align:center; margin-bottom:24px;">
                                <div style="display:inline-block; background:#f0f9f6; border:1.5px dashed #a8e6cf; border-radius:12px; padding:16px 32px;">
                                    <span style="font-size:32px; font-weight:800; letter-spacing:8px; color:#2d7a5f;">{{ $code }}</span>
                                </div>
                            </div>
                            <div style="font-size:12px; color:#888; line-height:1.6;">
                                If you did not request a password reset, you can safely ignore this email — your password will remain unchanged.
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 32px; border-top:1px solid #f0f9f6; text-align:center;">
                            <div style="font-size:11px; color:#aaa;">PESO Cagayan de Oro — Job Smart System</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>