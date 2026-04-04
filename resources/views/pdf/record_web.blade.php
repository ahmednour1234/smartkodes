<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
            margin: 0;
            padding: 20px;
        }
        .header {
            border-bottom: 2px solid #e5e7eb;
            margin-bottom: 16px;
            padding-bottom: 12px;
        }
        .title {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 6px 0;
        }
        .meta {
            color: #4b5563;
            font-size: 11px;
            margin: 0;
        }
        .section-title {
            font-size: 13px;
            font-weight: 700;
            margin: 14px 0 8px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
        }
        th {
            background: #f3f4f6;
            font-weight: 700;
        }
        .empty {
            color: #6b7280;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">{{ $formName }}</h1>
        <p class="meta">
            Record ID: {{ $recordId }} |
            Submitted: {{ $submittedAt }} |
            Work Order: {{ $workOrderId }}
        </p>
    </div>

    <div class="section-title">Form Data</div>
    <table>
        <thead>
            <tr>
                <th style="width: 35%;">Field</th>
                <th style="width: 65%;">Value</th>
            </tr>
        </thead>
        <tbody>
            @forelse($fieldRows as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td>{{ $row['value'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="empty">No form fields available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Attachments</div>
    <table>
        <thead>
            <tr>
                <th style="width: 30%;">Field</th>
                <th style="width: 40%;">File</th>
                <th style="width: 30%;">Link</th>
            </tr>
        </thead>
        <tbody>
            @forelse($fileRows as $file)
                <tr>
                    <td>{{ $file['field'] }}</td>
                    <td>{{ $file['name'] }}</td>
                    <td>
                        @if(!empty($file['url']))
                            {{ $file['url'] }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="empty">No attachments available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
