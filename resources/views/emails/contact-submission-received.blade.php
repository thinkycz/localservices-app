<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Submission</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #111827;
            max-width: 700px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #111827;
            color: white;
            padding: 18px 22px;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f9fafb;
            padding: 22px;
            border-radius: 0 0 10px 10px;
            border: 1px solid #e5e7eb;
            border-top: 0;
        }
        .card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 16px;
            margin: 16px 0;
        }
        .row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .row:last-child { border-bottom: 0; }
        .label { color: #6b7280; font-weight: 600; }
        .value { color: #111827; font-weight: 600; }
        .message {
            white-space: pre-wrap;
            background: #f3f4f6;
            border-radius: 10px;
            padding: 14px;
            border: 1px solid #e5e7eb;
            color: #111827;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin: 0;">New Contact Submission</h2>
    </div>
    <div class="content">
        <div class="card">
            <div class="row">
                <span class="label">Type</span>
                <span class="value">{{ $submission->type }}</span>
            </div>
            <div class="row">
                <span class="label">From</span>
                <span class="value">{{ $submission->name }} ({{ $submission->email }})</span>
            </div>
            <div class="row">
                <span class="label">Subject</span>
                <span class="value">{{ $submission->subject }}</span>
            </div>
            <div class="row">
                <span class="label">Submitted</span>
                <span class="value">{{ $submission->created_at?->toDayDateTimeString() }}</span>
            </div>
        </div>

        <h3 style="margin: 0 0 10px 0;">Message</h3>
        <div class="message">{{ $submission->message }}</div>
    </div>
</body>
</html>
