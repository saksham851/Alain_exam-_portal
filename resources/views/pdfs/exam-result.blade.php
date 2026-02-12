<!DOCTYPE html>
<html>
<head>
    <title>Exam Result - {{ $attempt->studentExam->exam->name }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.5; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .score-info { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .score-info td { padding: 10px; border: 1px solid #ddd; }
        .score-box { text-align: center; padding: 20px; background: #f8f9fa; border: 1px solid #000; margin-bottom: 30px; }
        .score-box h1 { margin: 0; font-size: 3rem; }
        .breakdown-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .breakdown-table th { background: #f2f2f2; padding: 10px; text-align: left; border: 1px solid #ddd; }
        .breakdown-table td { padding: 10px; border: 1px solid #ddd; }
        .pass { color: green; font-weight: bold; }
        .fail { color: red; font-weight: bold; }
        .footer { text-align: center; font-size: 0.8rem; color: #777; margin-top: 50px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>SCORE REPORT</h2>
        <h3>{{ $attempt->studentExam->exam->name }}</h3>
    </div>

    <div class="score-box">
        <div style="font-size: 1.2rem; text-transform: uppercase;">Total Points Earned</div>
        <h1>{{ round($attempt->total_score) }}</h1>
        <div style="font-size: 1.5rem; margin-top: 10px;" class="{{ $attempt->is_passed ? 'pass' : 'fail' }}">
            {{ $attempt->is_passed ? 'PASS' : 'FAIL' }}
        </div>
    </div>

    <table class="score-info">
        <tr>
            <td><strong>Student Name:</strong> {{ $attempt->studentExam->student->first_name }} {{ $attempt->studentExam->student->last_name }}</td>
            <td><strong>Date:</strong> {{ $attempt->ended_at ? $attempt->ended_at->format('M d, Y') : $attempt->created_at->format('M d, Y') }}</td>
        </tr>
        <tr>
            <td><strong>Exam Code:</strong> {{ $attempt->studentExam->exam->exam_code }}</td>
            <td><strong>Category:</strong> {{ $attempt->studentExam->exam->category->name ?? 'N/A' }}</td>
        </tr>
    </table>

    @if($attempt->category_breakdown)
    <h4>Category Breakdown</h4>
    <table class="breakdown-table">
        <thead>
            <tr>
                <th>Category</th>
                <th>Earned</th>
                <th>Max</th>
                <th>Passing</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attempt->category_breakdown as $cat)
            <tr>
                <td>{{ $cat['name'] }}</td>
                <td>{{ $cat['earned_points'] }}</td>
                <td>{{ $cat['max_points'] }}</td>
                <td>{{ $cat['threshold_points'] ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($attempt->content_area_breakdown)
    <h4 style="margin-top: 20px;">Content Area Performance</h4>
    <p style="font-size: 0.9rem; color: #666; margin-bottom: 10px;">(Informational only - not used for overall pass/fail status)</p>
    <table class="breakdown-table">
        <thead>
            <tr>
                <th>Content Area</th>
                <th style="text-align: center;">Points Earned</th>
                <th style="text-align: center;">Total Possible</th>
                <th style="text-align: center;">Score %</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attempt->content_area_breakdown as $area)
            @if($area['max_points'] > 0)
            <tr>
                <td>{{ $area['name'] }}</td>
                <td style="text-align: center;">{{ $area['earned_points'] }}</td>
                <td style="text-align: center;">{{ $area['max_points'] }}</td>
                <td style="text-align: center;">{{ $area['percentage'] }}%</td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="footer">
        Generated on {{ now()->format('Y-m-d H:i:s') }}
    </div>
</body>
</html>
