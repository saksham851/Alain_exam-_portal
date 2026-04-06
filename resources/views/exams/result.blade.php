@extends('layouts.app')

@section('content')

@php
    $liveStandard    = $attempt->studentExam->exam->examStandard;
    $liveCategories  = $liveStandard ? $liveStandard->categories->keyBy('id') : collect();
    $liveContentAreas = collect();
    if ($liveStandard) {
        foreach ($liveStandard->categories as $c) {
            foreach ($c->contentAreas as $a) {
                $liveContentAreas->put($a->id, $a);
            }
        }
    }

    $totalEarnedAggregated = 0;
    $totalMaxPoints = 0;

    if ($liveStandard) {
        foreach ($liveStandard->categories as $c) {
            $totalMaxPoints += $c->contentAreas->sum('max_points');
        }
    }

    if ($attempt->category_breakdown) {
        foreach ($attempt->category_breakdown as $catId => $cat) {
            $totalEarnedAggregated += $cat['earned_points'] ?? 0;
            if (!$liveStandard) {
                $totalMaxPoints += $cat['max_points'] ?? 0;
            }
        }
    }

    if ($totalMaxPoints <= 0) {
        $totalMaxPoints        = $attempt->max_points ?? 0;
        $totalEarnedAggregated = $attempt->total_score;
    }

    $displayPercentage  = $totalMaxPoints > 0 ? ($totalEarnedAggregated / $totalMaxPoints) * 100 : 0;

    // Overall PASS: use stored DB value first (saved by ExamScoringService which requires ALL categories pass)
    if (isset($attempt->is_passed)) {
        $overallPassed = (bool) $attempt->is_passed;
    } elseif ($attempt->category_breakdown) {
        // Fallback: all categories must individually pass
        $overallPassed = true;
        foreach ($attempt->category_breakdown as $cat) {
            if (!($cat['passed'] ?? false)) {
                $overallPassed = false;
                break;
            }
        }
    } else {
        $overallPassed = $displayPercentage >= ($attempt->studentExam->exam->passing_score_overall ?? 65);
    }

    // Passing threshold = sum of all per-category thresholds
    $passingThreshold = 0;
    if ($attempt->category_breakdown) {
        foreach ($attempt->category_breakdown as $cat) {
            $passingThreshold += $cat['threshold_points'] ?? 0;
        }
    }
    if ($passingThreshold <= 0) {
        $passingThreshold = $attempt->studentExam->exam->passing_score_overall ?? 65;
    }
    $examName           = $attempt->studentExam->exam->name ?? 'Exam';
    $studentName        = $attempt->studentExam->student->name ?? auth()->user()->name ?? 'Student';
    $attemptDate        = $attempt->ended_at ? \Carbon\Carbon::parse($attempt->ended_at)->format('M d, Y • h:i A') : 'N/A';
@endphp

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

    * { box-sizing: border-box; }

    .rp-page {
        font-family: 'Inter', sans-serif;
        background: transparent;
        min-height: 100vh;
        padding: 1rem 1rem 4rem;
    }

    .rp-wrap {
        max-width: 1100px;
        margin: 0 auto;
    }

    /* ── HERO BANNER ─────────────────────────────── */
    .rp-hero {
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 1.5rem;
        position: relative;
        background: #01284E;
        box-shadow: 0 10px 30px rgba(1, 40, 78, 0.15);
    }

    .rp-hero-inner {
        position: relative;
        padding: 2rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 2rem;
        flex-wrap: wrap;
    }

    .rp-hero-left { flex: 1; min-width: 200px; }

    .rp-status-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.45rem 1.2rem;
        border-radius: 99px;
        font-size: 0.85rem;
        font-weight: 800;
        letter-spacing: 1px;
        text-transform: uppercase;
        margin-bottom: 1rem;
        {{ $overallPassed ? 'background: #10b981; color: #ffffff;' : 'background: #ef4444; color: #ffffff;' }}
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .rp-exam-name {
        font-size: 1.7rem;
        font-weight: 800;
        color: #ffffff;
        line-height: 1.2;
        margin-bottom: 0.4rem;
    }

    .rp-student-meta {
        font-size: 0.85rem;
        color: rgba(255,255,255,0.55);
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .rp-student-meta span { display: flex; align-items: center; gap: 0.35rem; }

    /* Score donut */
    .rp-donut-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .rp-donut {
        width: 140px;
        height: 140px;
        position: relative;
    }

    .rp-donut svg { transform: rotate(-90deg); }

    .rp-donut-center {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    .rp-donut-pct {
        font-size: 1.5rem;
        font-weight: 900;
        color: #ffffff;
        line-height: 1;
    }

    .rp-donut-label {
        font-size: 0.62rem;
        font-weight: 600;
        letter-spacing: 1px;
        color: rgba(255,255,255,0.5);
        text-transform: uppercase;
        margin-top: 2px;
    }

    /* ── STAT STRIP ──────────────────────────────── */
    .rp-stats {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0;
        background: #ffffff;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 4px 24px rgba(0,0,0,0.07);
        margin-bottom: 1.5rem;
    }

    .rp-stat {
        padding: 1.4rem 1.5rem;
        text-align: center;
        position: relative;
    }

    .rp-stat + .rp-stat::before {
        content: '';
        position: absolute;
        left: 0; top: 20%; bottom: 20%;
        width: 1px;
        background: #e5e7eb;
    }

    .rp-stat-val {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1.1;
        background: {{ $overallPassed ? '#008B8B' : '#ef4444' }};
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .rp-stat-val.neutral {
        background: linear-gradient(135deg, #374151, #111827);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .rp-stat-label {
        font-size: 0.68rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #9ca3af;
        margin-top: 2px;
    }

    /* ── SECTION HEADING ─────────────────────────── */
    .rp-section-heading {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.2rem;
        padding: 0 0.25rem;
    }

    .rp-section-icon {
        width: 36px; height: 36px;
        border-radius: 10px;
        background: #01284E;
        display: flex; align-items: center; justify-content: center;
        color: white; font-size: 1rem;
        flex-shrink: 0;
    }

    .rp-section-title {
        font-size: 1rem;
        font-weight: 700;
        color: #111827;
        margin: 0;
    }

    .rp-section-sub {
        font-size: 0.75rem;
        color: #9ca3af;
        margin: 0;
    }

    /* ── CATEGORY CARDS ──────────────────────────── */
    .rp-cat-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
        align-items: stretch;
    }

    @media (max-width: 768px) {
        .rp-cat-grid { grid-template-columns: 1fr; }
        .rp-hero-inner { flex-direction: column; align-items: flex-start; }
        .rp-stats { grid-template-columns: 1fr; }
        .rp-stat + .rp-stat::before { display: none; }
        .rp-exam-name { font-size: 1.3rem; }
    }

    .rp-cat-card {
        background: #ffffff;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        transition: transform 0.2s;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .rp-cat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 32px rgba(0,0,0,0.1);
    }

    .rp-cat-header {
        padding: 1.25rem 1.5rem 1rem;
        position: relative;
        background: #ffffff;
        border-bottom: 1px solid #f3f4f6;
    }

    .rp-cat-header.pass  { border-top: 4px solid #10b981; }
    .rp-cat-header.fail  { border-top: 4px solid #ef4444; }

    .rp-cat-num {
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        padding: 0.25rem 0.7rem;
        border-radius: 99px;
        margin-bottom: 0.5rem;
        display: inline-block;
    }

    .rp-cat-header.pass .rp-cat-num { background: rgba(16, 185, 129, 0.1); color: #15803d; }
    .rp-cat-header.fail .rp-cat-num { background: rgba(239, 68, 68, 0.1); color: #be123c; }

    .rp-cat-name {
        font-size: 0.95rem;
        font-weight: 700;
        color: #111827;
        line-height: 1.3;
        margin-bottom: 0.75rem;
    }

    .rp-cat-score-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .rp-cat-score {
        font-size: 2rem;
        font-weight: 900;
        line-height: 1;
    }

    .rp-cat-score.pass { color: #059669; }
    .rp-cat-score.fail { color: #dc2626; }

    .rp-cat-score-max {
        font-size: 1rem;
        font-weight: 500;
        color: #9ca3af;
    }

    .rp-cat-badge {
        font-size: 0.8rem;
        font-weight: 800;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        padding: 0.4rem 1rem;
        border-radius: 99px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .rp-cat-badge.pass { background: #10b981; color: white; }
    .rp-cat-badge.fail { background: #ef4444; color: white; }

    /* Progress bar inside header */
    .rp-cat-progress {
        height: 6px;
        border-radius: 3px;
        margin-top: 0.9rem;
        background: rgba(0,0,0,0.07);
        overflow: hidden;
    }

    .rp-cat-progress-bar {
        height: 100%;
        border-radius: 3px;
        transition: width 1s cubic-bezier(.23,1,.32,1);
    }

    .rp-cat-header.pass .rp-cat-progress-bar { background: #10b981; }
    .rp-cat-header.fail .rp-cat-progress-bar { background: #ef4444; }

    /* Content areas body */
    .rp-areas-body {
        padding: 1rem 1.5rem 1.5rem;
    }

    .rp-areas-label {
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: #9ca3af;
        margin-bottom: 0.85rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .rp-area-row {
        padding: 0.75rem 0;
        border-bottom: 1px solid #f3f4f6;
    }

    .rp-area-row:last-child { border-bottom: none; }

    .rp-area-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.45rem;
        gap: 0.5rem;
    }

    .rp-area-name {
        font-size: 0.8rem;
        font-weight: 600;
        color: #374151;
        flex: 1;
        line-height: 1.3;
    }

    .rp-area-score {
        font-size: 0.78rem;
        font-weight: 700;
        color: #111827;
        white-space: nowrap;
        background: #f3f4f6;
        padding: 0.15rem 0.6rem;
        border-radius: 99px;
    }

    .rp-area-bar-wrap {
        height: 4px;
        background: #f3f4f6;
        border-radius: 2px;
        overflow: hidden;
        position: relative;
    }

    .rp-area-bar {
        height: 100%;
        border-radius: 2px;
        transition: width 1.2s cubic-bezier(.23,1,.32,1);
    }

    /* ── FOOTER ACTIONS ──────────────────────────── */
    .rp-footer {
        margin-top: 1.5rem;
        display: flex;
        justify-content: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .rp-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.75rem;
        border-radius: 12px;
        font-size: 0.875rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
        cursor: pointer;
        border: none;
    }

    .rp-btn-outline {
        background: #ffffff;
        color: #374151;
        border: 2px solid #e5e7eb;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }

    .rp-btn-outline:hover {
        border-color: #01284E;
        color: #01284E;
        transform: translateY(-1px);
        text-decoration: none;
    }

    .rp-btn-primary {
        background: #01284E;
        color: white;
        box-shadow: 0 4px 14px rgba(1, 40, 78, 0.35);
    }

    .rp-btn-primary:hover {
        background: #001a33;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(1, 40, 78, 0.4);
        color: white;
        text-decoration: none;
    }
</style>

<div class="rp-page">
<div class="rp-wrap">

    {{-- ── HERO ─────────────────────────────────── --}}
    <div class="rp-hero">
        <div class="rp-hero-inner">
            <div class="rp-hero-left">
                <div class="rp-status-chip">
                    <i class="ti {{ $overallPassed ? 'ti-trophy' : 'ti-mood-sad' }}"></i>
                    {{ $overallPassed ? 'PASS' : 'FAIL' }}
                </div>
                <div class="rp-exam-name">{{ $examName }}</div>
                <div class="rp-student-meta">
                    <span><i class="ti ti-user"></i> {{ $studentName }}</span>
                    <span><i class="ti ti-calendar"></i> {{ $attemptDate }}</span>
                </div>
            </div>

            {{-- Animated Donut --}}
            <div class="rp-donut-wrap">
                @php
                    $r = 54; $circ = 2 * M_PI * $r;
                    $offset = $circ * (1 - $displayPercentage / 100);
                @endphp
                <div class="rp-donut">
                    <svg width="140" height="140" viewBox="0 0 140 140">
                        <circle cx="70" cy="70" r="{{ $r }}" fill="none"
                                stroke="rgba(255,255,255,0.1)" stroke-width="10"/>
                        <circle cx="70" cy="70" r="{{ $r }}" fill="none"
                                stroke="{{ $overallPassed ? '#10b981' : '#ef4444' }}"
                                stroke-width="10"
                                stroke-dasharray="{{ round($circ, 2) }}"
                                stroke-dashoffset="{{ round($offset, 2) }}"
                                stroke-linecap="round"
                                style="transition: stroke-dashoffset 1.5s cubic-bezier(.23,1,.32,1);"/>
                    </svg>
                    <div class="rp-donut-center">
                        <span class="rp-donut-pct">{{ round($totalEarnedAggregated) }}<small style="font-size:0.65rem;font-weight:600; display:block; margin-top:1px;">pts</small></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── STAT STRIP ───────────────────────────── --}}
    <div class="rp-stats">
        <div class="rp-stat">
            <div class="rp-stat-val">{{ round($totalEarnedAggregated) }}</div>
            <div class="rp-stat-label">Points Earned</div>
        </div>
        <div class="rp-stat">
            <div class="rp-stat-val neutral">{{ $totalMaxPoints }}</div>
            <div class="rp-stat-label">Total Possible</div>
        </div>
    </div>

    {{-- ── DOMAIN BREAKDOWN ─────────────────────── --}}
    @if($attempt->category_breakdown)
        <div class="rp-cat-grid">
            @php $catIdx = 1; @endphp
            @foreach($attempt->category_breakdown as $catId => $data)
                @php
                    $liveCat      = $liveCategories->get($catId);
                    $catMaxPts    = $liveCat ? $liveCat->contentAreas->sum('max_points') : ($data['max_points'] ?? 0);
                    $catPct       = $catMaxPts > 0 ? ($data['earned_points'] / $catMaxPts) * 100 : 0;
                    $passed       = $data['passed'];
                    $childAreas   = collect($attempt->content_area_breakdown)->where('category_id', $catId);
                @endphp
                <div class="rp-cat-card">
                    {{-- Header --}}
                    <div class="rp-cat-header {{ $passed ? 'pass' : 'fail' }}">
                        <div class="rp-cat-num">Score Category {{ $catIdx++ }}</div>
                        <div class="rp-cat-name">{{ $data['name'] }}</div>
                        <div class="rp-cat-score-row">
                            <div>
                                <span class="rp-cat-score {{ $passed ? 'pass' : 'fail' }}">{{ $data['earned_points'] }}</span>
                                <span class="rp-cat-score-max"> / {{ $catMaxPts }} pts</span>
                            </div>
                            <span class="rp-cat-badge {{ $passed ? 'pass' : 'fail' }}">
                                <i class="ti {{ $passed ? 'ti-circle-check' : 'ti-circle-x' }} me-1"></i>
                                {{ $passed ? 'PASS' : 'FAIL' }}
                            </span>
                        </div>

                        {{-- Per-category progress bar --}}
                        <div class="rp-cat-progress">
                            <div class="rp-cat-progress-bar" style="width:{{ min(100, $catPct) }}%;"></div>
                        </div>
                    </div>

                    {{-- Content Areas --}}
                    @if($childAreas->count() > 0)
                        <div class="rp-areas-body">
                            <div class="rp-areas-label">
                                <i class="ti ti-list-details" style="font-size:0.75rem;"></i>
                                Content Area Breakdown
                            </div>
                            @foreach($childAreas as $area)
                                @php
                                    $liveArea    = $liveContentAreas->get($area['id']);
                                    $areaMaxPts  = $liveArea ? $liveArea->max_points : ($area['max_points'] ?? 0);
                                    $areaStr     = $areaMaxPts > 0 ? ($area['earned_points'] / $areaMaxPts) * 100 : 0;
                                    $barColor    = $areaStr >= 100 ? '#10b981' : ($areaStr >= 50 ? '#6366f1' : '#f59e0b');
                                @endphp
                                @if($areaMaxPts > 0)
                                    <div class="rp-area-row">
                                        <div class="rp-area-top">
                                            <span class="rp-area-name">{{ $area['name'] }}</span>
                                            <span class="rp-area-score">{{ $area['earned_points'] }}/{{ $areaMaxPts }}</span>
                                        </div>
                                        <div class="rp-area-bar-wrap">
                                            <div class="rp-area-bar" style="width:{{ min(100,$areaStr) }}%; background:{{ $barColor }};"></div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    {{-- ── FOOTER ───────────────────────────────── --}}
    <div class="rp-footer">
        @if(auth()->user()->isStudent())
            <a href="{{ route('exams.show', $attempt->studentExam->exam->id) }}" class="rp-btn rp-btn-outline">
                <i class="ti ti-arrow-left"></i> Exit Report
            </a>
            @if($attempt->studentExam->attempts_allowed - $attempt->studentExam->attempts_used > 0)
                <a href="{{ route('exams.start', $attempt->studentExam->exam->id) }}" class="rp-btn rp-btn-primary">
                    <i class="ti ti-refresh"></i> Re-Attempt Exam
                </a>
            @endif
        @else
            <a href="{{ url()->previous() }}" class="rp-btn rp-btn-outline">
                <i class="ti ti-arrow-left"></i> Back to Results
            </a>
        @endif
    </div>

</div>
</div>

@endsection
