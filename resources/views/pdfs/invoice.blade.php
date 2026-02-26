<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice</title>
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
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            border-bottom: 3px solid #4f46e5;
            padding-bottom: 30px;
        }
        .company-info h1 {
            color: #4f46e5;
            font-size: 32px;
            margin: 0 0 10px 0;
        }
        .company-info p {
            color: #666;
            margin: 5px 0;
        }
        .invoice-info {
            text-align: right;
        }
        .invoice-info h2 {
            color: #4f46e5;
            font-size: 24px;
            margin: 0 0 15px 0;
        }
        .invoice-info p {
            margin: 5px 0;
            color: #666;
        }
        .invoice-number {
            font-size: 18px;
            font-weight: bold;
            color: #111827;
        }
        .section {
            margin: 25px 0;
        }
        .section-title {
            color: #4f46e5;
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 15px 0;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 10px;
        }
        .info-grid {
            display: flex;
            gap: 40px;
        }
        .info-block {
            flex: 1;
        }
        .info-block h3 {
            color: #6b7280;
            font-size: 12px;
            text-transform: uppercase;
            margin: 0 0 10px 0;
        }
        .info-block p {
            margin: 5px 0;
            color: #111827;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background: #f3f4f6;
            color: #4f46e5;
            font-weight: bold;
            text-align: left;
            padding: 12px;
            border-bottom: 2px solid #e5e7eb;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .text-right {
            text-align: right;
        }
        .totals {
            margin-top: 30px;
            width: 300px;
            margin-left: auto;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .total-row.final {
            border-top: 3px solid #4f46e5;
            border-bottom: none;
            font-size: 18px;
            font-weight: bold;
            color: #4f46e5;
            padding-top: 15px;
        }
        .payment-status {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-paid {
            background: #d1fae5;
            color: #065f46;
        }
        .qr-section {
            text-align: center;
            margin: 40px 0;
            padding: 30px;
            background: #f9fafb;
            border-radius: 8px;
        }
        .qr-code {
            width: 120px;
            height: 120px;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #9ca3af;
            font-size: 12px;
        }
        .notes {
            background: #fef3c7;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .notes h4 {
            color: #92400e;
            margin: 0 0 10px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-info">
            <h1>Local Services</h1>
            <p>123 Business Street</p>
            <p>San Francisco, CA 94102</p>
            <p>support@localservices.com</p>
            <p>(555) 123-4567</p>
        </div>
        <div class="invoice-info">
            <h2>INVOICE</h2>
            <p class="invoice-number">{{ $invoiceNumber }}</p>
            <p>Date: {{ $invoiceDate }}</p>
            <p>Booking ID: #{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}</p>
            <p style="margin-top: 15px;">
                <span class="payment-status status-paid">✓ Paid</span>
            </p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Bill To</div>
        <div class="info-grid">
            <div class="info-block">
                <h3>Customer</h3>
                <p><strong>{{ $booking->customer->name }}</strong></p>
                <p>{{ $booking->customer->email }}</p>
                @if($booking->customer->phone)
                <p>{{ $booking->customer->phone }}</p>
                @endif
            </div>
            <div class="info-block">
                <h3>Service Provider</h3>
                <p><strong>{{ $booking->provider->name }}</strong></p>
                <p>{{ $booking->provider->email }}</p>
                @if($booking->provider->phone)
                <p>{{ $booking->provider->phone }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Service Details</div>
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>{{ $booking->service->name }}</strong><br>
                        <span style="color: #6b7280; font-size: 12px;">{{ $booking->offering->name }}</span>
                    </td>
                    <td>{{ $booking->booking_date->format('M d, Y') }}</td>
                    <td>{{ $booking->start_time->format('g:i A') }} - {{ $booking->end_time->format('g:i A') }}</td>
                    <td class="text-right">${{ number_format($booking->total_price, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="totals">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>${{ number_format($booking->total_price, 2) }}</span>
            </div>
            <div class="total-row">
                <span>Tax (0%):</span>
                <span>$0.00</span>
            </div>
            <div class="total-row final">
                <span>Total Paid:</span>
                <span>${{ number_format($booking->total_price, 2) }}</span>
            </div>
        </div>
    </div>

    @if($booking->payment_method)
    <div class="notes">
        <h4>Payment Information</h4>
        <p>Method: {{ ucfirst($booking->payment_method) }}</p>
        @if($booking->paid_at)
        <p>Paid on: {{ $booking->paid_at->format('F j, Y \a\t g:i A') }}</p>
        @endif
    </div>
    @endif

    <div class="qr-section">
        <h3>Verify This Invoice</h3>
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode($qrData) }}" alt="QR Code" class="qr-code">
        <p style="margin-top: 15px; font-size: 12px; color: #6b7280;">Scan to verify booking authenticity</p>
    </div>

    <div class="footer">
        <p>Thank you for your business!</p>
        <p>This invoice was generated automatically by Local Services</p>
        <p style="margin-top: 10px;">For questions about this invoice, please contact support@localservices.com</p>
    </div>
</body>
</html>
