<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Answer Key - {{ $exam->name }}</title>
    <style>
        @page {
            margin: 20mm 15mm;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #000;
            font-size: 11pt;
        }
        
        .header {
            text-align: center;
            padding: 15px 0;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
        }
        
        .header h1 {
            margin: 0 0 5px 0;
            font-size: 18pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .header .exam-name {
            font-size: 12pt;
            margin-top: 5px;
            font-weight: 600;
        }
        
        .info-section {
            margin-bottom: 15px;
            padding: 8px 12px;
            background: #f5f5f5;
            border: 1px solid #ddd;
            font-size: 10pt;
        }
        
        .info-section strong {
            font-weight: bold;
        }
        
        /* Grid Layout for Answer Key */
        .answer-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        
        .answer-row {
            display: table-row;
        }
        
        .answer-cell {
            display: table-cell;
            width: 25%;
            padding: 8px 12px;
            font-size: 11pt;
            vertical-align: middle;
            border-bottom: 1px solid #e0e0e0;
            font-weight: normal;
        }
        
        .answer-cell:not(:last-child) {
            border-right: 1px solid #e0e0e0;
        }
        
        .question-num {
            font-weight: bold;
            color: #000;
        }
        
        .answer-value {
            color: #000;
            font-weight: normal;
            margin-left: 15px;
        }
        
        /* Category indicator (optional small badge) */
        .category-indicator {
            font-size: 7pt;
            color: #666;
            margin-left: 3px;
            font-weight: normal;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 9pt;
            color: #666;
        }
        
        .footer p {
            margin: 3px 0;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Answer Key</h1>
        <h2>{{ $exam->name }}</h2>
    </div>

    <!-- Info Section -->
    <div class="info-section">
        <strong>Total Questions:</strong> {{ count($answerKey) }} | 
        <strong>Generated:</strong> {{ date('d M Y, h:i A') }}
    </div>

    <!-- Answer Grid (4 columns) -->
    <div class="answer-grid">
        @php
            $chunks = array_chunk($answerKey, 4);
        @endphp
        
        @foreach($chunks as $chunk)
            <div class="answer-row">
                @foreach($chunk as $item)
                    <div class="answer-cell">
                        <span class="question-num">{{ $item['number'] }}.</span>
                        <span class="answer-value">{{ implode(', ', $item['correct_answers']) }}</span>
                    </div>
                @endforeach
                
                @php
                    $remaining = 4 - count($chunk);
                @endphp
                
                @for($i = 0; $i < $remaining; $i++)
                    <div class="answer-cell">&nbsp;</div>
                @endfor
            </div>
        @endforeach
    </div>

    <!-- Footer -->
    <div class="footer">
        <p><strong>{{ config('app.name') }}</strong></p>
        <p>&copy; {{ date('Y') }} All Rights Reserved</p>
    </div>
</body>
</html>
