{{-- Email reset password SetorJelantah --}}
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password SetorJelantah</title>
</head>
<body style="margin:0;padding:0;background-color:#FBF8F2;font-family:Inter,Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#FBF8F2;padding:32px 16px;">
  <tr>
    <td align="center">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background-color:#FFFFFF;border-radius:12px;overflow:hidden;border:1px solid #E7E0D2;">
        {{-- Header --}}
        <tr>
<td style="background-color:#BE7E1D;padding:28px 32px;text-align:center;">
            <img src="{{ $message->embed(public_path('img/logo-drop.png')) }}" alt="SetorJelantah" width="64" height="64" style="display:inline-block;border:0;">
            <h1 style="margin:12px 0 0;color:#FFFFFF;font-size:24px;font-weight:700;">SetorJelantah</h1>
            <p style="margin:6px 0 0;color:#FBF0DD;font-size:13px;">Kelola setoran minyak jelantah rumah tangga</p>
          </td>
        </tr>
        {{-- Body --}}
        <tr>
          <td style="padding:32px;color:#241A0F;">
            <p style="margin:0 0 16px;font-size:15px;">Halo <strong>{{ $name }}</strong>,</p>
            <p style="margin:0 0 16px;font-size:14px;line-height:1.6;">
              Kami menerima permintaan untuk mengatur ulang password akun <strong>{{ $email }}</strong>.
              Klik tombol di bawah untuk membuat password baru.
            </p>

            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0;">
              <tr>
                <td align="center">
                  <a href="{{ $url }}"
                     style="display:inline-block;background-color:#BE7E1D;color:#FFFFFF;text-decoration:none;font-weight:700;font-size:15px;padding:14px 36px;border-radius:6px;">
                    Reset Password
                  </a>
                </td>
              </tr>
            </table>

            <p style="margin:0 0 12px;font-size:13px;color:#6E6152;line-height:1.6;">
              Link ini berlaku selama <strong>{{ $expireMinutes }} menit</strong>.
              Jika tombol di atas tidak berfungsi, salin tautan berikut ke browser Anda:
            </p>
            <p style="margin:0 0 20px;font-size:12px;color:#6E6152;word-break:break-all;background-color:#FBF0DD;padding:10px 12px;border-radius:6px;border:1px dashed #E7E0D2;">{{ $url }}</p>

            <p style="margin:0 0 12px;font-size:13px;color:#6E6152;line-height:1.6;">
              Jika Anda tidak meminta reset password, abaikan email ini — password Anda tetap aman.
            </p>
          </td>
        </tr>
        {{-- Divider --}}
        <tr>
          <td style="padding:0 32px;">
            <hr style="border:none;border-top:1px solid #E7E0D2;margin:0;">
          </td>
        </tr>
        {{-- Footer --}}
        <tr>
          <td style="padding:20px 32px;text-align:center;background-color:#FBF8F2;">
            <p style="margin:0;font-size:12px;color:#6E6152;">© {{ date('Y') }} Kelompok 3 Golongan D - PK3K</p>
            <p style="margin:6px 0 0;font-size:12px;color:#BE7E1D;">Email ini dikirim otomatis, mohon tidak membalas.</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>