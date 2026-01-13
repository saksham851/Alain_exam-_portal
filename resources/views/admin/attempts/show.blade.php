@extends('layouts.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="m-b-10">Attempt Details</h5>
        </div>

      </div>
    </div>
  </div>
</div>
<!-- [ breadcrumb ] end -->

<div class="row">
    {{-- Summary Card --}}
    <div class="col-md-12">
        <div class="card bg-white shadow-none border">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
                <div>
                   <h4 class="mb-1">{{ $attemptData->student->name }} <span class="badge bg-light-primary text-primary f-12 ms-2">{{ $attemptData->exam->name }}</span></h4>
                   <p class="mb-0 text-muted">Submitted on {{ $attemptData->created_at->format('M d, Y H:i A') }}</p>
                </div>
                <div class="text-end mt-3 mt-md-0">
                    @php
                        $isPassed = $attemptData->total_score >= 65;
                    @endphp
                    <h3 class="mb-0 {{ $isPassed ? 'text-success' : 'text-danger' }}">
                        {{ round($attemptData->total_score, 1) }}% 
                        <span class="f-14 fw-normal text-muted">({{ $isPassed ? 'Passed' : 'Failed' }})</span>
                    </h3>
                    <div class="mt-2">
                        <small class="text-muted">IG: {{ round($attemptData->ig_score, 1) }}% | DM: {{ round($attemptData->dm_score, 1) }}%</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Questions Breakdown --}}
    <div class="col-md-12">
        <div class="vstack gap-3">
            @foreach($answers as $index => $answer)
                <div class="card {{ $answer->is_correct ? 'border-success' : 'border-danger' }} shadow-none" style="border-left-width: 5px !important;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                             <h6 class="text-muted text-uppercase f-12 fw-bold">Question {{ $index + 1 }}</h6>
                             @if($answer->is_correct)
                                <span class="badge bg-light-success text-success">Correct</span>
                             @else
                                <span class="badge bg-light-danger text-danger">Incorrect</span>
                             @endif
                        </div>
                        
                        <h5 class="mb-4">{!! $answer->question_text !!}</h5>

                        <ul class="list-group list-group-flush">
                            @foreach($answer->options as $option)
                                @php
                                    $isOptionCorrect = $option->is_correct == 1;
                                    
                                    // Handle selected_options which is cast to array in model
                                    $selectedData = $answer->selected_options;
                                    if (is_string($selectedData)) {
                                        $selectedData = json_decode($selectedData, true);
                                    }
                                    // Ensure it is an array
                                    $selectedOptions = is_array($selectedData) ? $selectedData : [];
                                    
                                    $isStudentSelect = in_array($option->option_key, $selectedOptions);
                                @endphp
                                <li class="list-group-item d-flex justify-content-between align-items-center px-3 py-2 rounded mb-1 border-0
                                    {{ $isOptionCorrect ? 'bg-light-success' : '' }}
                                    {{ $isStudentSelect && !$answer->is_correct ? 'bg-light-danger' : '' }}
                                ">
                                    <div>
                                        @if($isStudentSelect) 
                                            <i class="ti ti-circle-check-filled me-2 {{ $answer->is_correct ? 'text-success' : 'text-danger' }}"></i> 
                                        @else 
                                            <i class="ti ti-circle me-2 text-muted"></i> 
                                        @endif
                                        <span class="{{ $isOptionCorrect ? 'fw-bold text-success' : '' }}">{{ $option->option_key }}. {{ $option->option_text }}</span>
                                    </div>
                                    @if($isOptionCorrect) <i class="ti ti-check text-success"></i> @endif
                                    @if($isStudentSelect && !$answer->is_correct) <i class="ti ti-x text-danger"></i> @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
