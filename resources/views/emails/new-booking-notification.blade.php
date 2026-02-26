<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Booking Received</title>
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
        .highlight {
            background: #fef3c7;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #f59e0b;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>New Booking Received!</h1>
        <p>You have a new booking request</p>
    </div>
    
    <div class="content">
        <p>Hi {{ $booking->provider->name }},</p>
        
        <div class="highlight">
            <p style="margin: 0; font-weight: 600; color: #92400e;">🎉 You have a new booking that requires your confirmation!</p>
        </div>
        
        <p>A customer has booked your service. Please review the details below and confirm or decline the booking.</p>
        
        <div class="booking-details">
            <h3 style="margin-top: 0; color: #111827;">Customer Information</h3>
            
            <div class="detail-row">
                <span class="label">Name</span>
                <span class="value">{{ $booking->customer->name }}</span>
            </div>
            
            <div class="detail-row">
                <span class="label">Email</span>
                <span class="value">{{ $booking->customer->email }}</span>
            </div>
            
            <div class="detail-row">
                <span class="label">Phone</span>
                <span class="value">{{ $booking->customer->phone ?? 'Not provided' }}</span>
            </div>
        </div>
        
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
                <span class="value">{{ $booking->start_time }} - {{ $booking->end_time }}</span>
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
                <span class="label">Status</span>
                <span class="status status-pending">Pending</span>
            </div>
            
            <div class="detail-row">
                <span class="label">Booking ID</span>
                <span class="value">#{{ $booking->id }}</span>
            </div>
        </div>
        
        @if($booking->customer_notes)
        <div class="booking-details" style="background: #eff6ff;">
            <h3 style="margin-top: 0; color: #1e40af;">Customer Notes</h3>
            <p style="color: #1e40af; margin: 0;">{{ $booking->customer_notes }}</p>
        </div>
        @endif
        
        <div style="text-align: center;">
            <a href="{{ route('vendor.bookings.show', $booking->id) }}" class="button">View & Manage Booking</a>
        </div>
        
        <div style="margin-top: 30px; padding: 15px; background: #f3f4f6; border-radius: 8px;">
            <p style="margin: 0; font-size: 14px; color: #4b5563;">
                <strong>Quick Actions:</strong><br>
                • Confirm the booking to secure the appointment<br>
                • Contact the customer if you need more information<br>
                • Decline if you're not available
            </p>
        </div>
        
        <div class="footer">
            <p>This is an automated notification from your LocalServices vendor dashboard.</p>
            <p>&copy; {{ date('Y') }} LocalServices. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
