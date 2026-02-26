<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Booking Confirmation</title>
    <style>
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #4f46e5;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #4f46e5;
            font-size: 28px;
            margin: 0 0 10px 0;
        }
        .header p {
            color: #666;
            margin: 0;
        }
        .confirmation-badge {
            background: #10b981;
            color: white;
            padding: 10px 20px;
            border-radius: 20px;
            display: inline-block;
            font-weight: bold;
            margin: 20px 0;
        }
        .section {
            margin: 25px 0;
            padding: 20px;
            background: #f9fafb;
            border-radius: 8px;
        }
        .section h2 {
            color: #4f46e5;
            font-size: 18px;
            margin: 0 0 15px 0;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 10px;
        }
        .info-row {
            display: flex;
            margin: 10px 0;
        }
        .info-label {
            font-weight: bold;
            width: 150px;
            color: #6b7280;
        }
        .info-value {
            flex: 1;
            color: #111827;
        }
        .qr-section {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background: #f3f4f6;
            border-radius: 8px;
        }
        .qr-code {
            width: 150px;
            height: 150px;
            margin: 0 auto;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #9ca3af;
            font-size: 12px;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-confirmed { background: #d1fae5; color: #065f46; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-completed { background: #dbeafe; color: #1e40af; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .price-highlight {
            font-size: 24px;
            font-weight: bold;
            color: #4f46e5;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Local Services</h1>
        <p>Your trusted service marketplace</p>
        <div class="confirmation-badge">✓ Booking Confirmed</div>
    </div>

    <div class="section">
        <h2>Booking Details</h2>
        <div class="info-row">
            <span class="info-label">Booking ID:</span>
            <span class="info-value">#{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Status:</span>
            <span class="info-value">
                <span class="status-badge status-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span>
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Service:</span>
            <span class="info-value">{{ $booking->service->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Service Type:</span>
            <span class="info-value">{{ $booking->offering->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Date:</span>
            <span class="info-value">{{ $booking->booking_date->format('l, F j, Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Time:</span>
            <span class="info-value">{{ $booking->start_time->format('g:i A') }} - {{ $booking->end_time->format('g:i A') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Total Price:</span>
            <span class="info-value price-highlight">${{ number_format($booking->total_price, 2) }}</span>
        </div>
    </div>

    <div class="section">
        <h2>Service Provider</h2>
        <div class="info-row">
            <span class="info-label">Name:</span>
            <span class="info-value">{{ $booking->provider->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span class="info-value">{{ $booking->provider->email }}</span>
        </div>
        @if($booking->provider->phone)
        <div class="info-row">
            <span class="info-label">Phone:</span>
            <span class="info-value">{{ $booking->provider->phone }}</span>
        </div>
        @endif
    </div>

    <div class="section">
        <h2>Customer Information</h2>
        <div class="info-row">
            <span class="info-label">Name:</span>
            <span class="info-value">{{ $booking->customer->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span class="info-value">{{ $booking->customer->email }}</span>
        </div>
        @if($booking->customer->phone)
        <div class="info-row">
            <span class="info-label">Phone:</span>
            <span class="info-value">{{ $booking->customer->phone }}</span>
        </div>
        @endif
    </div>

    @if($booking->notes)
    <div class="section">
        <h2>Additional Notes</h2>
        <p>{{ $booking->notes }}</p>
    </div>
    @endif

    <div class="qr-section">
        <h3>Scan for Quick Access</h3>
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($qrData) }}" alt="QR Code" class="qr-code">
        <p style="margin-top: 10px; font-size: 12px; color: #6b7280;">Scan this code to view your booking online</p>
    </div>

    <div class="footer">
        <p>Thank you for choosing Local Services!</p>
        <p>Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
        <p style="margin-top: 10px;">For support, contact us at support@localservices.com</p>
    </div>
</body>
</html>
