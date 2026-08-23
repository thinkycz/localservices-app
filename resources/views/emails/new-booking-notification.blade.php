<!doctype html>
<html lang="cs">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Nová rezervace</title></head>
<body style="margin:0;background:#F7F6F2;color:#17211F;font-family:Arial,sans-serif;line-height:1.6">
    <div style="max-width:620px;margin:0 auto;padding:24px">
        <div style="border-radius:16px 16px 0 0;background:#0F766E;padding:24px;color:#fff"><strong style="font-size:22px">Domluveno</strong><h1 style="margin:16px 0 0;font-size:26px">Nová rezervace čeká na potvrzení</h1></div>
        <div style="border:1px solid #DDE3DF;border-top:0;border-radius:0 0 16px 16px;background:#fff;padding:24px">
            <p>Dobrý den, {{ $booking->provider->name }},</p>
            <p>zkontrolujte termín a rezervaci potvrďte, nebo ji zrušte s krátkým důvodem.</p>
            <table role="presentation" style="width:100%;margin:24px 0;border-collapse:collapse">
                <tr><td style="border-bottom:1px solid #DDE3DF;padding:10px 0;color:#66736F">Zákazník</td><td style="border-bottom:1px solid #DDE3DF;padding:10px 0;text-align:right;font-weight:700">{{ $booking->customer_display_name }}</td></tr>
                <tr><td style="border-bottom:1px solid #DDE3DF;padding:10px 0;color:#66736F">Kontakt</td><td style="border-bottom:1px solid #DDE3DF;padding:10px 0;text-align:right">{{ $booking->customer_contact_email }}<br>{{ $booking->customer_phone }}</td></tr>
                <tr><td style="border-bottom:1px solid #DDE3DF;padding:10px 0;color:#66736F">Služba</td><td style="border-bottom:1px solid #DDE3DF;padding:10px 0;text-align:right;font-weight:700">{{ $booking->service->name }}</td></tr>
                <tr><td style="padding:10px 0;color:#66736F">Termín</td><td style="padding:10px 0;text-align:right;font-weight:700">{{ $booking->booking_date->locale('cs')->translatedFormat('j. F Y') }}, {{ substr($booking->start_time, 0, 5) }}</td></tr>
            </table>
            @if($booking->customer_notes)<p><strong>Poznámka:</strong> {{ $booking->customer_notes }}</p>@endif
            <p style="text-align:center;margin:24px 0 8px"><a href="{{ route('vendor.bookings.show', $booking->id) }}" style="display:inline-block;border-radius:12px;background:#0F766E;padding:12px 20px;color:#fff;text-decoration:none;font-weight:700">Otevřít rezervaci</a></p>
        </div>
        <p style="text-align:center;font-size:12px;color:#66736F">Automatické upozornění z Domluveno · © {{ date('Y') }}</p>
    </div>
</body>
</html>
