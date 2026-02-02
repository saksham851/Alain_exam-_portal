@extends('layouts.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Exam Result</h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ route('exams.index') }}">My Exams</a></li>
          <li class="breadcrumb-item"><a href="{{ route('exams.show', $attempt->studentExam->exam->id) }}">{{ $attempt->studentExam->exam->name }}</a></li>
          <li class="breadcrumb-item" aria-current="page">Result</li>
        </ul>
      </div>
    </div>
  </div>
</div>
<!-- [ breadcrumb ] end -->

<div class="row justify-content-center">
    <!-- Score Summary Card -->
    <div class="col-12 col-lg-10">
        <div class="card border-0 shadow-sm" style="border-radius: 0;">
            <div class="card-body p-4 p-md-5" style="background-color: #fff; color: #000; font-family: 'Arial', sans-serif;">
                
                <!-- Report Header -->
                <div class="text-center mb-4">
                    <h2 style="font-weight: 800; font-size: 1.8rem; margin-bottom: 0.5rem; text-transform: uppercase;">Score Report Summary</h2>
                    <h3 style="font-weight: 400; font-size: 1.4rem; margin: 0;">{{ $attempt->studentExam->exam->name }}</h3>
                </div>

                <!-- Main Scores Table -->
                <div class="table-responsive">
                    <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; margin-bottom: 2rem;">
                        <thead>
                            <tr>
                                <th style="width: 50%; border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 10px; text-align: center; font-weight: 700; font-size: 1rem;">Information Gathering (IM)</th>
                                <th style="width: 50%; border-bottom: 1px solid #000; padding: 10px; text-align: center; font-weight: 700; font-size: 1rem;">Decision Making (DM)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Percentages Row -->
                            <tr>
                                <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; text-align: center; padding: 15px;">
                                    <span style="font-size: 3.5rem; line-height: 1; display: block; color: #000;">{{ round($attempt->ig_score ?? 0) }}%</span>
                                </td>
                                <td style="border-bottom: 1px solid #000; text-align: center; padding: 15px;">
                                    <span style="font-size: 3.5rem; line-height: 1; display: block; color: #000;">{{ round($attempt->dm_score ?? 0) }}%</span>
                                </td>
                            </tr>
                            
                            <!-- Detailed Breakdown Row -->
                            <tr>
                                <td style="border-right: 1px solid #000; vertical-align: top; padding: 30px 10px;">
                                    <div class="d-flex flex-column align-items-center position-relative" style="gap: 20px;">
                                        <!-- Vertical Line Background -->
                                        <div style="position: absolute; top: 0; bottom: 0; width: 6px; background-color: transparent; z-index: 0;"></div>
                                        
                                        <!-- Your Score -->
                                        <div class="text-center position-relative" style="z-index: 1;">
                                            <div style="font-size: 0.85rem; margin-bottom: 2px;">Your Score:</div>
                                            <div style="padding: 2px 10px; font-weight: 600; font-size: 0.95rem;">124</div>
                                        </div>

                                        <!-- Passing Score -->
                                        <div class="text-center position-relative" style="z-index: 1;">
                                            <div style="font-size: 0.85rem; margin-bottom: 2px;">Passing Score:</div>
                                            <div style="padding: 2px 10px; font-weight: 600; font-size: 0.95rem;">100</div>
                                        </div>

                                        <!-- Max Score -->
                                        <div class="text-center position-relative" style="z-index: 1;">
                                            <div style="font-size: 0.85rem; margin-bottom: 2px;">Max Score:</div>
                                            <div style="padding: 2px 10px; font-weight: 600; font-size: 0.95rem;">154</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="vertical-align: top; padding: 30px 10px;">
                                    <div class="d-flex flex-column align-items-center position-relative" style="gap: 20px;">
                                        <!-- Vertical Line Background -->
                                        <div style="position: absolute; top: 0; bottom: 0; width: 6px; background-color: transparent; z-index: 0;"></div>
                                        
                                        <!-- Your Score -->
                                        <div class="text-center position-relative" style="z-index: 1;">
                                            <div style="font-size: 0.85rem; margin-bottom: 2px;">Your Score:</div>
                                            <div style="padding: 2px 10px; font-weight: 600; font-size: 0.95rem;">117</div>
                                        </div>

                                        <!-- Passing Score -->
                                        <div class="text-center position-relative" style="z-index: 1;">
                                            <div style="font-size: 0.85rem; margin-bottom: 2px;">Passing Score:</div>
                                            <div style="padding: 2px 10px; font-weight: 600; font-size: 0.95rem;">111</div>
                                        </div>

                                        <!-- Max Score -->
                                        <div class="text-center position-relative" style="z-index: 1;">
                                            <div style="font-size: 0.85rem; margin-bottom: 2px;">Max Score:</div>
                                            <div style="padding: 2px 10px; font-weight: 600; font-size: 0.95rem;">149</div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Overall Score Table -->
                <div class="mt-4">
                    <table style="width: 100%; border-collapse: collapse; border: 1px solid #000;">
                        <thead>
                            <tr>
                                <th style="border-bottom: 1px solid #000; padding: 10px; text-align: center;">
                                    <span style="padding: 4px 15px; font-weight: 700; font-size: 0.95rem;">Overall Score</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="text-align: center; padding: 20px; border-bottom: 1px solid #000;">
                                     <span style="padding: 5px 25px; font-size: 3rem; font-weight: 500; display: inline-block;">{{ round($attempt->total_score) }}%</span>
                                </td>
                            </tr>
                            <tr style="background-color: {{ $attempt->is_passed ? '#bcdfa1' : '#f8b4b4' }};">
                                <td style="text-align: center; padding: 15px;">
                                     <span style="font-size: 2.5rem; font-weight: 500; text-transform: uppercase; color: #000; letter-spacing: 2px;">
                                        {{ $attempt->is_passed ? 'PASS' : 'FAIL' }}
                                     </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>


    <!-- Bottom Actions -->
    <div class="col-12 mt-3 text-center">
        <div class="d-flex flex-wrap gap-2 justify-content-center py-2">
            <a href="{{ route('exams.show', $attempt->studentExam->exam->id) }}" class="btn btn-outline-primary px-3 py-2" style="font-size: 0.9rem;">
                <i class="ti ti-arrow-left me-2"></i>Back to Exam Details
            </a>

            @if($attempt->studentExam->attempts_allowed - $attempt->studentExam->attempts_used > 0)
                <a href="{{ route('exams.start', $attempt->studentExam->exam->id) }}" class="btn btn-primary px-3 py-2 shadow-sm" style="font-size: 0.9rem;">
                    <i class="ti ti-refresh me-2"></i>Attempt Again
                </a>
            @endif
        </div>
    </div>
</div>

@endsection
