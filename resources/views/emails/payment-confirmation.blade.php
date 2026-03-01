<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Payment Confirmation') }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #111827;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            padding: 26px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }

        .content {
            background: #f9fafb;
            padding: 26px;
            border-radius: 0 0 10px 10px;
            border: 1px solid #e5e7eb;
            border-top: 0;
        }

        .card {
            background: white;
            padding: 18px;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            margin: 16px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .row:last-child {
            border-bottom: 0;
        }

        .label {
            color: #6b7280;
            font-weight: 600;
        }

        .value {
            color: #111827;
            font-weight: 700;
        }

        .button {
            display: inline-block;
            background: #2563eb;
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1 style="margin: 0;">{{ __('Payment Successful') }}</h1>
        <p style="margin: 6px 0 0 0;">{{ __('Thanks for your payment') }}</p>
    </div>

    <div class="content">
        <p>Hi {{ $booking->customer->name }},</p>
        <p>{{ __('Your payment was received successfully.') }}</p>

        <div class="card">
            <div class="row">
                <span class="label">{{ __('Service') }}</span>
                <span class="value">{{ $booking->service->name }}</span>
            </div>
            <div class="row">
                <span class="label">{{ __('Booking') }}</span>
                <span class="value">#{{ $booking->id }}</span>
            </div>
            <div class="row">
                <span class="label">{{ __('Amount') }}</span>
                <span class="value">${{ number_format((float) $booking->total_price, 2) }}</span>
            </div>
            <div class="row">
                <span class="label">{{ __('Paid At') }}</span>
                <span class="value">{{ $booking->paid_at?->toDayDateTimeString() }}</span>
            </div>
        </div>

        <p style="text-align: center; margin: 22px 0 0 0;">
            <a class="button" href="{{ route('bookings.confirmation', $booking->id) }}">{{ __('View Booking') }}</a>
        </p>
    </div>
</body>

</html>