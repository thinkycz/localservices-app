<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f9fafb;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .booking-details {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .label {
            color: #6b7280;
            font-weight: 500;
        }
        .value {
            color: #111827;
            font-weight: 600;
        }
        .status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #6b7280;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            background: #4f46e5;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Booking Confirmation</h1>
        <p>Thank you for booking with us!</p>
    </div>
    
    <div class="content">
        <p>Hi {{ $booking->customer->name }},</p>
        
        <p>Your booking has been received and is currently <span class="status status-pending">Pending</span>. We'll notify you once the vendor confirms your appointment.</p>
        
        <div class="booking-details">
            <h3 style="margin-top: 0; color: #111827;">Booking Details</h3>
            
            <div class="detail-row">
                <span class="label">Service</span>
                <span class="value">{{ $booking->service->name }}</span>
            </div>
            
            <div class="detail-row">
                <span class="label">Offering</span>
                <span class="value">{{ $booking->offering->name }}</span>
            </div>
            
            <div class="detail-row">
                <span class="label">Date</span>
                <span class="value">{{ $booking->booking_date->format('l, F j, Y') }}</span>
            </div>
            
            <div class="detail-row">
                <span class="label">Time</span>
                <span class="value">{{ \Carbon\Carbon::parse($booking->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('g:i A') }}</span>
            </div>
            
            <div class="detail-row">
                <span class="label">Duration</span>
                <span class="value">{{ $booking->offering->duration_minutes }} minutes</span>
            </div>
            
            <div class="detail-row">
                <span class="label">Total Price</span>
                <span class="value">${{ number_format($booking->total_price, 2) }}</span>
            </div>
            
            <div class="detail-row">
                <span class="label">Vendor</span>
                <span class="value">{{ $booking->provider->name }}</span>
            </div>
            
            <div class="detail-row">
                <span class="label">Booking ID</span>
                <span class="value">#{{ $booking->id }}</span>
            </div>
        </div>
        
        @if($booking->customer_notes)
        <div class="booking-details" style="background: #fffbeb;">
            <h3 style="margin-top: 0; color: #92400e;">Your Notes</h3>
            <p style="color: #92400e; margin: 0;">{{ $booking->customer_notes }}</p>
        </div>
        @endif
        
        <p>You can view and manage your bookings anytime by visiting your account:</p>
        
        <div style="text-align: center;">
            <a href="{{ route('bookings.index') }}" class="button">View My Bookings</a>
        </div>
        
        <div class="footer">
            <p>If you have any questions, please don't hesitate to contact us.</p>
            <p>&copy; {{ date('Y') }} LocalServices. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
