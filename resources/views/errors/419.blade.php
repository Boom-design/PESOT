{{--
    419 — the form sat open too long.

    Laravel raises this when a submitted form carries a CSRF token the server
    no longer holds, which happens when the page waited longer than
    SESSION_LIFETIME before it was sent. Nothing is broken and nothing was
    saved; the visitor simply has to open the page again.

    Laravel's own 419 page says "Page Expired" on a grey background and offers
    no way forward, which reads as a crash to anyone who is not a developer.
    This one says what happened and points at the door.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('partials.head-brand')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/fill/style.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            min-height: 100vh;
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            background: #14551B;
            color: #fff;
        }

        .wrap {
            min-height: 100vh;
            min-height: 100svh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 20px;
        }

        .card {
            width: 100%;
            max-width: 440px;
            background: #fff;
            color: #1F2937;
            border-radius: 16px;
            padding: 32px 28px;
            text-align: center;
            box-shadow: 0 18px 40px rgba(0, 0, 0, .22);
        }

        .mark {
            width: 64px;
            height: 64px;
            margin: 0 auto 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #FEF3C7;
            color: #B45309;
            font-size: 32px;
        }

        h1 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        p {
            font-size: 14px;
            line-height: 1.6;
            color: #4B5563;
            margin-bottom: 8px;
        }

        .reassure {
            font-size: 13px;
            color: #6B7280;
            margin-bottom: 24px;
        }

        .btn-back {
            display: inline-block;
            width: 100%;
            padding: 12px 20px;
            border-radius: 10px;
            background: #28812F;
            color: #fff;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
        }

        .btn-back:hover { background: #1D6F27; color: #fff; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <div class="mark"><i class="ph-fill ph-clock-countdown"></i></div>

            <h1>This page waited too long</h1>

            <p>
                The form was open for a while before it was sent, so the system
                asked for it again to make sure it came from you.
            </p>

            <p class="reassure">
                Nothing was saved and nothing was lost. Open the page again and
                fill it in once more.
            </p>

            <a href="{{ url('/login') }}" class="btn-back">Go to the login page</a>
        </div>
    </div>
</body>
</html>
