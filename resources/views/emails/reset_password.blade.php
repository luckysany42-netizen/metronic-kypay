<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Reset Password</title>
</head>
<body style="margin:0; padding:0; background:#f1f5f9; font-family: 'Segoe UI', Arial, sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9; padding: 40px 0;">
    <tr>
      <td align="center">
        <table width="520" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:16px; overflow:hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.07);">

          <!-- Header -->
          <tr>
            <td style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); padding: 36px 40px; text-align:center;">
              <h1 style="margin:0; color:#ffffff; font-size:22px; font-weight:700; letter-spacing:-0.3px;">
                {{ config('app.name') }}
              </h1>
              <p style="margin:8px 0 0; color:rgba(255,255,255,0.8); font-size:13px;">
                Permintaan Reset Password
              </p>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding: 40px 40px 32px;">
              <p style="margin:0 0 8px; color:#374151; font-size:15px; font-weight:600;">
                Halo, {{ $userName }} 👋
              </p>
              <p style="margin:0 0 24px; color:#6b7280; font-size:14px; line-height:1.6;">
                Kami menerima permintaan untuk mereset password akun kamu. Klik tombol di bawah untuk membuat password baru.
              </p>

              <!-- CTA Button -->
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td align="center" style="padding: 8px 0 28px;">
                    <a href="{{ $resetUrl }}"
                       style="display:inline-block; background: linear-gradient(135deg, #3b82f6, #1d4ed8); color:#ffffff; text-decoration:none; font-size:15px; font-weight:600; padding:14px 36px; border-radius:10px; box-shadow: 0 4px 14px rgba(59,130,246,0.35);">
                      Reset Password Sekarang
                    </a>
                  </td>
                </tr>
              </table>

              <!-- Warning -->
              <div style="background:#fef9c3; border:1px solid #fde68a; border-radius:8px; padding:14px 16px; margin-bottom:24px;">
                <p style="margin:0; color:#92400e; font-size:13px; line-height:1.5;">
                  ⚠️ Link ini hanya berlaku selama <strong>60 menit</strong>. Jika kamu tidak meminta reset password, abaikan email ini.
                </p>
              </div>

              <!-- URL fallback -->
              <p style="margin:0 0 6px; color:#9ca3af; font-size:12px;">
                Atau copy link berikut ke browser kamu:
              </p>
              <p style="margin:0; word-break:break-all; color:#3b82f6; font-size:12px;">
                {{ $resetUrl }}
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background:#f8fafc; border-top:1px solid #e2e8f0; padding:20px 40px; text-align:center;">
              <p style="margin:0; color:#9ca3af; font-size:12px;">
                Email ini dikirim otomatis oleh sistem <strong>{{ config('app.name') }}</strong>.<br/>
                Jangan balas email ini.
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>