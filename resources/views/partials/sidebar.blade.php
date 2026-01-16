<nav class="pc-sidebar">
  <div class="navbar-wrapper">
    <div class="m-header">
      <a href="/" class="b-brand text-primary">
        <!-- ========   Change your logo from here   ============ -->
        <img src="{{ asset('assets/images/logo-new.png') }}" class="img-fluid logo-lg" alt="logo" style="max-height: 50px;">
      </a>
    </div>
    <div class="navbar-content">
      <ul class="pc-navbar">
        
        @if(auth()->check() && auth()->user()->role === 'admin')
        {{-- ADMIN LINKS --}}
        <li class="pc-item pc-caption">
            <label>Admin Control</label>
            <i class="ti ti-dashboard"></i>
        </li>
        
        <li class="pc-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <a href="{{ route('admin.dashboard') }}" class="pc-link">
                <span class="pc-micon"><i class="ti ti-dashboard"></i></span>
                <span class="pc-mtext">Dashboard</span>
            </a>
        </li>
        <li class="pc-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <a href="{{ route('admin.users.index') }}" class="pc-link">
                <span class="pc-micon"><i class="ti ti-users"></i></span>
                <span class="pc-mtext">Students</span>
            </a>
        </li>
        <li class="pc-item {{ request()->routeIs('admin.exam-categories.*') ? 'active' : '' }}">
            <a href="{{ route('admin.exam-categories.index') }}" class="pc-link">
                <span class="pc-micon"><i class="ti ti-notebook"></i></span>
                <span class="pc-mtext">Exam Categories</span>
            </a>
        </li>
        @php
            $isExamGroupActive = request()->routeIs('admin.exams.*') || 
                               request()->routeIs('admin.sections.*') || 
                               request()->routeIs('admin.case-studies-bank.*') || 
                               request()->routeIs('admin.questions.*') || 
                               request()->routeIs('admin.data.*') || 
                               request()->routeIs('admin.attempts.*');
        @endphp
        <li class="pc-item pc-hasmenu {{ $isExamGroupActive ? 'active pc-trigger' : '' }}">
            <a href="#!" class="pc-link">
                <span class="pc-micon"><i class="ti ti-book"></i></span>
                <span class="pc-mtext">Exams</span>
                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
            </a>
            <ul class="pc-submenu"@if($isExamGroupActive) style="display: block;"@endif>
                <li class="pc-item {{ request()->routeIs('admin.exams.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.exams.index') }}" class="pc-link">All Exams</a>
                </li>
                <li class="pc-item {{ request()->routeIs('admin.sections.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.sections.index') }}" class="pc-link">Sections</a>
                </li>
                <li class="pc-item {{ request()->routeIs('admin.case-studies-bank.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.case-studies-bank.index') }}" class="pc-link">Case Studies Bank</a>
                </li>
                <li class="pc-item {{ request()->routeIs('admin.questions.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.questions.index') }}" class="pc-link">Question Bank</a>
                </li>
                <li class="pc-item {{ request()->routeIs('admin.data.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.data.index') }}" class="pc-link">Data Management</a>
                </li>
                <li class="pc-item {{ request()->routeIs('admin.attempts.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.attempts.index') }}" class="pc-link">Results & Attempts</a>
                </li>
            </ul>
        </li>
        @endif

        @if(auth()->check() && auth()->user()->role === 'student')
        {{-- STUDENT LINKS --}}
        <li class="pc-item pc-caption">
            <label>Student Panel</label>
            <i class="ti ti-school"></i>
        </li>

        <li class="pc-item {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
            <a href="{{ route('student.dashboard') }}" class="pc-link">
                <span class="pc-micon"><i class="ti ti-dashboard"></i></span>
                <span class="pc-mtext">Dashboard</span>
            </a>
        </li>
        <li class="pc-item {{ request()->routeIs('exams.*') ? 'active' : '' }}">
            <a href="{{ route('exams.index') }}" class="pc-link">
                <span class="pc-micon"><i class="ti ti-book"></i></span>
                <span class="pc-mtext">My Exams</span>
            </a>
        </li>
        @endif
        

      </ul>
    </div>
  </div>
</nav>
