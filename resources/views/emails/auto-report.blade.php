<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    body {
        font-family: Arial, sans-serif;
        font-size: 14px;
        color: #333;
        background: #f4f6f8;
        margin: 0;
        padding: 0;
    }
    .wrapper {
        max-width: 600px;
        margin: 30px auto;
        background: #fff;
        border-radius: 6px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .header {
        background: #2c3e50;
        color: #fff;
        padding: 24px 32px;
    }
    .header h1 {
        font-size: 20px;
        margin: 0 0 4px;
    }
    .header p {
        font-size: 13px;
        margin: 0;
        color: #c0cfe0;
    }
    .body {
        padding: 28px 32px;
    }
    .info-table {
        width: 100%;
        border-collapse: collapse;
        margin: 16px 0;
        font-size: 13px;
    }
    .info-table td {
        padding: 8px 12px;
        border-bottom: 1px solid #eee;
    }
    .info-table td:first-child {
        font-weight: bold;
        color: #555;
        width: 130px;
    }
    .badge {
        display: inline-block;
        background: #2c3e50;
        color: #fff;
        border-radius: 4px;
        padding: 2px 10px;
        font-size: 12px;
        text-transform: capitalize;
    }
    .note {
        margin-top: 20px;
        padding: 12px 16px;
        background: #f0f4f8;
        border-left: 4px solid #2c3e50;
        font-size: 13px;
        color: #555;
        border-radius: 0 4px 4px 0;
    }
    .footer {
        padding: 16px 32px;
        background: #f9f9f9;
        font-size: 11px;
        color: #999;
        border-top: 1px solid #eee;
    }
</style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>Hasportal — Automated Report</h1>
        <p>Your scheduled summary report is attached to this email.</p>
    </div>
    <div class="body">
        <p>Dear Recipient,</p>
        <p>Please find the attached <strong>Summary Report</strong> for the following configuration:</p>

        <table class="info-table">
            <tr>
                <td>Device</td>
                <td>{{ $deviceCategory }} <span style="color:#999;">({{ $deviceId }})</span></td>
            </tr>
            <tr>
                <td>Schedule Type</td>
                <td><span class="badge">{{ $scheduleType }}</span></td>
            </tr>
            <tr>
                <td>Period Start</td>
                <td>{{ $startDate }}</td>
            </tr>
            <tr>
                <td>Period End</td>
                <td>{{ $endDate }}</td>
            </tr>
            <tr>
                <td>Generated At</td>
                <td>{{ $generatedAt }}</td>
            </tr>
        </table>

        <div class="note">
            The PDF attachment contains the full statistical summary, hourly average table, and 24-hour profile charts.
            You can also view live data on the Hasportal dashboard.
        </div>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} Hasportal. This is an automated message — please do not reply.
    </div>
</div>
</body>
</html>
