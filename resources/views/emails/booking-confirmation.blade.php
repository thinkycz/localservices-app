<!doctype html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Přijetí rezervace</title>
</head>
<body style="margin:0;background:#F7F6F2;color:#17211F;font-family:Arial,sans-serif;line-height:1.6">
    <div style="max-width:620px;margin:0 auto;padding:24px">
        <div style="border-radius:16px 16px 0 0;background:#0F766E;padding:24px;color:#fff">
            <strong style="font-size:22px">Domluveno</strong>
            <h1 style="margin:16px 0 0;font-size:26px">Rezervaci jsme přijali</h1>
        </div>
        <div style="border:1px solid #DDE3DF;border-top:0;border-radius:0 0 16px 16px;background:#fff;padding:24px">
            <p>Dobrý den, {{ $booking->customer_display_name }},</p>
            <p>požadavek čeká na potvrzení poskytovatelem. O změně stavu vám dáme vědět.</p>

            <table role="presentation" style="width:100%;margin:24px 0;border-collapse:collapse">
                <tr><td style="border-bottom:1px solid #DDE3DF;padding:10px 0;color:#66736F">Provozovna</td><td style="border-bottom:1px solid #DDE3DF;padding:10px 0;text-align:right;font-weight:700">{{ $booking->shop->name }}</td></tr>
                <tr><td style="border-bottom:1px solid #DDE3DF;padding:10px 0;color:#66736F">Služba</td><td style="border-bottom:1px solid #DDE3DF;padding:10px 0;text-align:right;font-weight:700">{{ $booking->service->name }}</td></tr>
                <tr><td style="border-bottom:1px solid #DDE3DF;padding:10px 0;color:#66736F">Termín</td><td style="border-bottom:1px solid #DDE3DF;padding:10px 0;text-align:right;font-weight:700">{{ $booking->booking_date->locale('cs')->translatedFormat('j. F Y') }}, {{ substr($booking->start_time, 0, 5) }}</td></tr>
                <tr><td style="border-bottom:1px solid #DDE3DF;padding:10px 0;color:#66736F">Délka</td><td style="border-bottom:1px solid #DDE3DF;padding:10px 0;text-align:right;font-weight:700">{{ $booking->service->duration_minutes }} min</td></tr>
                <tr><td style="padding:10px 0;color:#66736F">Cena</td><td style="padding:10px 0;text-align:right;font-weight:700">{{ number_format((float) $booking->total_price, 2, ',', ' ') }} {{ $booking->currency }}</td></tr>
            </table>

            <p style="font-size:14px;color:#66736F">Bezplatné zrušení je možné nejpozději 24 hodin před začátkem. Časy jsou v pásmu {{ $booking->timezone }}. Domluveno nezpracovává platby.</p>
            <p style="text-align:center;margin:24px 0 8px"><a href="{{ $manageUrl ?? route('bookings.index') }}" style="display:inline-block;border-radius:12px;background:#0F766E;padding:12px 20px;color:#fff;text-decoration:none;font-weight:700">Spravovat rezervaci</a></p>
            @if($manageUrl)
                <p style="font-size:12px;color:#66736F">Tento odkaz je určený jen vám. Nepřeposílejte ho dalším osobám.</p>
            @endif
        </div>
        <p style="text-align:center;font-size:12px;color:#66736F">© {{ date('Y') }} Domluveno</p>
    </div>
</body>
</html>
