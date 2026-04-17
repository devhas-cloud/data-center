<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }

        .email-container {
            background-color: #ffffff;
            border-radius: 5px;
            padding: 30px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(90deg, #FF8A1A 0%, #01B3BC 20%, #292A49 100%);
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
            margin: -30px -30px 30px -30px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .content {
            margin: 20px 0;
        }

        .button {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(90deg, #01B3BC, #292A49);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }

        .button:hover {
            opacity: 0.9;
        }

        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #01B3BC;
            padding: 15px;
            margin: 20px 0;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
            text-align: center;
        }

        .warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="header">
            <h1>🔐 Reset Password</h1>
        </div>

        <div class="content">
            <p>Halo <strong>{{ $username }}</strong>,</p>

            <p>Kami menerima permintaan untuk mereset password akun Anda. Klik tombol di bawah ini untuk melanjutkan
                proses reset password:</p>

            <div style="text-align: center;">
                <a href="{{ $resetUrl }}" class="button">Reset Password</a>
            </div>

            <div class="info-box">
                <p style="margin: 0;"><strong>Atau salin link berikut ke browser Anda:</strong></p>
                <p style="margin: 5px 0 0 0; word-break: break-all;">
                    <a href="{{ $resetUrl }}">{{ $resetUrl }}</a>
                </p>
            </div>

            <div class="warning">
                <p style="margin: 0;"><strong>⚠️ Penting:</strong></p>
                <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                    <li>Link ini akan kadaluarsa dalam <strong>60 menit</strong></li>
                    <li>Jika Anda tidak meminta reset password, abaikan email ini</li>
                    <li>Jangan bagikan link ini kepada siapapun</li>
                </ul>
            </div>

            <p>Untuk keamanan akun Anda, gunakan password yang kuat dengan kombinasi huruf besar, huruf kecil, angka,
                dan simbol.</p>
        </div>

        <div class="footer">
            <p><strong>PT HAS Environmetal</strong></p>
            <p>Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
            <p>&copy; {{ date('Y') }} Hak Cipta Dilindungi.</p>
        </div>
    </div>
</body>

</html>
