<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Lamaran {{ $companyName }}</title>
    <style>
        @media only screen and (max-width: 600px) {
            .email-outer { padding: 16px 10px !important; }
            .email-section { padding-left: 22px !important; padding-right: 22px !important; }
            .email-title { font-size: 25px !important; }
            .email-label { width: 110px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#f2f6f3;color:#183d2c;font-family:Arial,Helvetica,sans-serif;-webkit-text-size-adjust:100%;">
    <div style="display:none;font-size:1px;line-height:1px;color:#f2f6f3;max-height:0;max-width:0;opacity:0;overflow:hidden;mso-hide:all;">{{ $application->name }} melamar sebagai {{ $position }}. CV terlampir untuk ditinjau.</div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;background-color:#f2f6f3;">
        <tr>
            <td align="center" class="email-outer" style="padding:36px 16px;">
                <!--[if mso]><table role="presentation" width="640" cellpadding="0" cellspacing="0" border="0"><tr><td><![endif]-->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:640px;background-color:#ffffff;border:1px solid #dce8df;border-radius:14px;overflow:hidden;">
                    <tr><td height="5" style="height:5px;background-color:#30aa47;font-size:0;line-height:0;">&nbsp;</td></tr>
                    <tr>
                        <td class="email-section" style="padding:34px 38px 32px;background-color:#062b22;">
                            <p style="margin:0 0 18px;color:#98d6a3;font-size:10px;line-height:16px;font-weight:bold;letter-spacing:2.5px;text-transform:uppercase;">KARIER &amp; REKRUTMEN</p>
                            <h1 class="email-title" style="margin:0;color:#ffffff;font-size:29px;line-height:1.35;font-weight:bold;letter-spacing:-0.5px;">Lamaran {{ $companyName }}</h1>
                            <p style="margin:14px 0 0;color:#c6dfcf;font-size:13px;line-height:21px;">Kandidat baru untuk bergabung bersama tim Anda.</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="email-section" style="padding:28px 38px 0;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;background-color:#eff8f1;border:1px solid #c3dbc9;border-radius:8px;">
                                <tr><td style="padding:17px 20px;border-left:3px solid #268839;">
                                    <p style="margin:0 0 6px;color:#268839;font-size:10px;line-height:15px;font-weight:bold;letter-spacing:1.4px;">POSISI YANG DILAMAR</p>
                                    <p style="margin:0;color:#164e39;font-size:18px;line-height:26px;font-weight:bold;">{{ $position }}</p>
                                </td></tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="email-section" style="padding:28px 38px 0;">
                            <h2 style="margin:0 0 16px;font-size:16px;line-height:24px;font-weight:bold;color:#183d2c;">Informasi pelamar</h2>
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;border-collapse:collapse;text-align:left;">
                                <tr>
                                    <th scope="row" class="email-label" width="155" valign="top" style="width:155px;padding:13px 14px 13px 0;border-bottom:1px solid #e4ece6;color:#72877a;font-size:12px;line-height:20px;font-weight:normal;">Nama lengkap</th>
                                    <td style="padding:13px 0;border-bottom:1px solid #e4ece6;color:#183d2c;font-size:14px;line-height:20px;font-weight:bold;word-break:break-word;">{{ $application->name }}</td>
                                </tr>
                                <tr>
                                    <th scope="row" class="email-label" width="155" valign="top" style="width:155px;padding:13px 14px 13px 0;border-bottom:1px solid #e4ece6;color:#72877a;font-size:12px;line-height:20px;font-weight:normal;">Alamat email</th>
                                    <td style="padding:13px 0;border-bottom:1px solid #e4ece6;font-size:13px;line-height:20px;word-break:break-all;"><a href="mailto:{{ $application->email }}" style="color:#268839;text-decoration:none;">{{ $application->email }}</a></td>
                                </tr>
                                <tr>
                                    <th scope="row" class="email-label" width="155" valign="top" style="width:155px;padding:13px 14px 13px 0;border-bottom:1px solid #e4ece6;color:#72877a;font-size:12px;line-height:20px;font-weight:normal;">Nomor telepon</th>
                                    <td style="padding:13px 0;border-bottom:1px solid #e4ece6;color:#3d5244;font-size:13px;line-height:20px;word-break:break-word;">{{ $application->phone }}</td>
                                </tr>
                                <tr>
                                    <th scope="row" class="email-label" width="155" valign="top" style="width:155px;padding:13px 14px 13px 0;border-bottom:1px solid #e4ece6;color:#72877a;font-size:12px;line-height:20px;font-weight:normal;">Tanggal melamar</th>
                                    <td style="padding:13px 0;border-bottom:1px solid #e4ece6;color:#3d5244;font-size:13px;line-height:20px;">{{ \App\Support\LocalTime::format($application->created_at, 'd M Y, H:i T') }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="email-section" style="padding:24px 38px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;background-color:#f8f9f6;border:1px solid #e5e9df;border-radius:8px;">
                                <tr><td style="padding:17px 20px;">
                                    <p style="margin:0 0 7px;color:#4d6244;font-size:12px;line-height:18px;font-weight:bold;">DOKUMEN CV TERLAMPIR</p>
                                    <p style="margin:0 0 6px;color:#3d4836;font-size:13px;line-height:20px;word-break:break-word;">{{ $application->cv_original_name }}</p>
                                    <p style="margin:0;color:#7c8575;font-size:12px;line-height:19px;">Silakan tinjau CV terlampir untuk melanjutkan proses rekrutmen.</p>
                                </td></tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" class="email-section" style="padding:0 38px 30px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr><td align="center" bgcolor="#268839" style="background-color:#268839;border-radius:6px;mso-padding-alt:13px 24px;">
                                <a href="mailto:{{ $application->email }}" style="display:inline-block;padding:13px 24px;color:#ffffff;font-size:13px;line-height:20px;font-weight:bold;text-decoration:none;border:1px solid #268839;border-radius:6px;mso-padding-alt:0;">Hubungi pelamar</a>
                            </td></tr></table>
                            <p style="margin:12px 0 0;color:#72877a;font-size:11px;line-height:18px;">Anda juga dapat membalas email ini langsung kepada pelamar.</p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" class="email-section" style="padding:22px 38px;border-top:1px solid #dce8df;background-color:#f7fbf8;">
                            <p style="margin:0 0 5px;color:#1d662b;font-size:12px;line-height:19px;font-weight:bold;">{{ $companyName }}</p>
                            <p style="margin:0;color:#72877a;font-size:10px;line-height:17px;">Notifikasi rekrutmen dari website perusahaan &nbsp;&middot;&nbsp; Referensi #{{ $application->id }}</p>
                        </td>
                    </tr>
                </table>
                <!--[if mso]></td></tr></table><![endif]-->
            </td>
        </tr>
    </table>
</body>
</html>
