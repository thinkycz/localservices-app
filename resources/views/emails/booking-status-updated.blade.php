<!doctype html>
<html lang="cs">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Změna rezervace</title></head>
<body style="margin:0;background:#F7F6F2;color:#17211F;font-family:Arial,sans-serif;line-height:1.6">
    @php
        $status = match($newStatus) {
            'confirmed' => ['Rezervace je potvrzená', 'Poskytovatel potvrdil váš termín.'],
            'completed' => ['Rezervace je dokončená', 'Služba byla označena jako dokončená. Ve svém účtu nyní můžete přidat hodnocení.'],
            'cancelled' => ['Rezervace byla zrušena', 'Termín už není aktivní. Důvod zrušení najdete níže, pokud ho poskytovatel uvedl.'],
            default => ['Stav rezervace se změnil', 'Podívejte se na aktuální informace k rezervaci.'],
        };
    @endphp
    <div style="max-width:620px;margin:0 auto;padding:24px">
        <div style="border-radius:16px 16px 0 0;background:#0F766E;padding:24px;color:#fff"><strong style="font-size:22px">Domluveno</strong><h1 style="margin:16px 0 0;font-size:26px">{{ $status[0] }}</h1></div>
        <div style="border:1px solid #DDE3DF;border-top:0;border-radius:0 0 16px 16px;background:#fff;padding:24px">
            <p>Dobrý den, {{ $booking->customer_display_name }},</p>
            <p>{{ $status[1] }}</p>
            <div style="margin:20px 0;border-radius:12px;background:#F7F6F2;padding:16px">
                <strong>{{ $booking->service->name }}</strong><br>
                {{ $booking->shop->name }}<br>
                {{ $booking->booking_date->locale('cs')->translatedFormat('j. F Y') }}, {{ substr($booking->start_time, 0, 5) }} · {{ number_format((float) $booking->total_price, 2, ',', ' ') }} {{ $booking->currency }}
            </div>
            @if($newStatus === 'cancelled' && $booking->cancellation_reason)
                <p><strong>Důvod zrušení:</strong> {{ $booking->cancellation_reason }}</p>
            @endif
            @if($booking->user_id)
                <p style="text-align:center;margin:24px 0 8px"><a href="{{ route('bookings.index') }}" style="display:inline-block;border-radius:12px;background:#0F766E;padding:12px 20px;color:#fff;text-decoration:none;font-weight:700">Otevřít moje rezervace</a></p>
            @endif
        </div>
        <p style="text-align:center;font-size:12px;color:#66736F">© {{ date('Y') }} Domluveno</p>
    </div>
</body>
</html>
