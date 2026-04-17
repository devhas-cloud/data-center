<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Device Report - {{ $device_id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
        }
        
        .header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #333;
        }
        
        .header h2 {
            font-size: 18pt;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .header label {
            font-size: 14px;
            color: #555;
        }
        
        .logo {
            height: 50px;
            width: auto;
        }
        
        .info-section {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        
        .info-section table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .info-section td {
            padding: 5px;
        }
        
        .info-section td:first-child {
            font-weight: bold;
            width: 150px;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .data-table th {
            background-color: #333;
            color: white;
            padding: 10px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #333;
        }
        
        .data-table td {
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
        }
        
        .data-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #333;
            text-align: center;
            font-size: 9pt;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="header">
       
    </div>
    
    <div class="info-section">
        @php
            $total_parameters = count($parameters);
            $colspan_value = 3 + $total_parameters;
        @endphp
        <table>
            <tr>
                <th colspan="{{ $colspan_value }}" style="text-align: center; font-size: 18pt; font-weight: bold; padding: 10px;">DATA REPORT</th>
            </tr>
            <tr>
                <td colspan="{{ $colspan_value }}" style="text-align: center; padding: 8px; color: #555;">{{ collect($parameters)->pluck('parameter_label')->join(', ') }}</td>
            </tr>
            <tr>
                <td colspan="{{ $colspan_value }}" style="padding: 12px 10px; line-height: 1.8;">
                    Device ID: {{ $device_id }} 
                    &nbsp;&nbsp;&nbsp;&nbsp;<br>
                    Category: {{ $device_category }}<br>
                    Report Periode: {{ $date_range['start'] }} to {{ $date_range['end'] }} 
                    &nbsp;&nbsp;&nbsp;&nbsp;<br>
                    Total Records: {{ $total_records }}<br>
                    Generated Date: {{ date('Y-m-d H:i:s') }} 
                    &nbsp;&nbsp;&nbsp;&nbsp;</br>
                    Powered by PT. Has Environmental &nbsp;&nbsp;&nbsp;&nbsp;
                </td>
            </tr>
        </table>
    </div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Date</th>
                <th>Time</th>
                @foreach($parameters as $param)
                    <th>{{ $param['parameter_label'] }}<br>({{ $param['parameter_unit'] }})</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                <td>{{ $row['no'] }}</td>
                <td>{{ $row['date'] }}</td>
                <td>{{ $row['time'] }}</td>
                @foreach($parameters as $param)
                    <td>{{ isset($row[$param['parameter_name']]) && $row[$param['parameter_name']] !== null ? $row[$param['parameter_name']] : '-' }}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
