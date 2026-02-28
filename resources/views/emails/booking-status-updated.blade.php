<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Status Update</title>
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
        .status-confirmed {
            background: #d1fae5;
            color: #065f46;
        }
        .status-completed {
            background: #dbeafe;
            color: #1e40af;
        }
        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
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
        .message-box {
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .message-confirmed {
            background: #d1fae5;
            color: #065f46;
        }
        .message-completed {
            background: #dbeafe;
            color: #1e40af;
        }
        .message-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Booking Status Update</h1>
        <p>Your booking status has changed</p>
    </div>
    
    <div class="content">
        <p>Hi {{ $booking->customer->name }},</p>
        
        @php
            $statusClass = match($newStatus) {
                'confirmed' => 'message-confirmed',
                'completed' => 'message-completed',
                'cancelled' => 'message-cancelled',
                default => 'message-confirmed',
            };
            $statusMessage = match($newStatus) {
                'confirmed' => 'Great news! Your booking has been confirmed by the vendor. We look forward to seeing you!',
                'completed' => 'Your service has been completed. Thank you for choosing us!',
                'cancelled' => 'We regret to inform you that your booking has been cancelled.',
                default => 'Your booking status has been updated.',
            };
        @endphp
        
        <div class="message-box {{ $statusClass }}">
            <p style="margin: 0; font-weight: 500;">{{ $statusMessage }}</p>
        </div>
        
        <p>Your booking is now: 
            <span class="status status-{{ $newStatus }}">
                {{ ucfirst($newStatus) }}
            </span>
        </p>
        
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
                <span class="label">Vendor</span>
                <span class="value">{{ $booking->provider->name }}</span>
            </div>
            
            <div class="detail-row">
                <span class="label">Booking ID</span>
                <span class="value">#{{ $booking->id }}</span>
            </div>
        </div>
        
        @if($newStatus === 'confirmed')
        <p><strong>What's next?</strong></p>
        <ul>
            <li>Please arrive 5-10 minutes before your scheduled time</li>
            <li>If you need to reschedule, please contact us at least 24 hours in advance</li>
            <li>Bring any relevant documents or information related to your service</li>
        </ul>
        @endif
        
        @if($newStatus === 'completed')
        <div style="text-align: center; margin: 30px 0;">
            <p>We hope you enjoyed your experience!</p>
            <a href="{{ route('services.show', $booking->service->slug) }}" class="button">Leave a Review</a>
        </div>
        @endif
        
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
