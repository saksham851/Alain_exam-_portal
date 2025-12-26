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
                <span class="pc-mtext">Users</span>
            </a>
        </li>
        <li class="pc-item {{ request()->routeIs('admin.exam-categories.*') ? 'active' : '' }}">
            <a href="{{ route('admin.exam-categories.index') }}" class="pc-link">
                <span class="pc-micon"><i class="ti ti-notebook"></i></span>
                <span class="pc-mtext">Exam Categories</span>
            </a>
        </li>
        <li class="pc-item {{ request()->routeIs('admin.exams.*') ? 'active' : '' }}">
            <a href="{{ route('admin.exams.index') }}" class="pc-link">
                <span class="pc-micon"><i class="ti ti-book"></i></span>
                <span class="pc-mtext">Exams</span>
            </a>
        </li>
        <li class="pc-item {{ request()->routeIs('admin.case-studies.*') ? 'active' : '' }}">
            <a href="{{ route('admin.case-studies.index') }}" class="pc-link">
                <span class="pc-micon"><i class="ti ti-folders"></i></span>
                <span class="pc-mtext">Sections</span>
            </a>
        </li>
        <li class="pc-item {{ request()->routeIs('admin.questions.*') ? 'active' : '' }}">
            <a href="{{ route('admin.questions.index') }}" class="pc-link">
                <span class="pc-micon"><i class="ti ti-question-mark"></i></span>
                <span class="pc-mtext">Question Bank</span>
            </a>
        </li>
        <li class="pc-item {{ request()->routeIs('admin.data.*') ? 'active' : '' }}">
            <a href="{{ route('admin.data.index') }}" class="pc-link">
                <span class="pc-micon"><i class="ti ti-database-export"></i></span>
                <span class="pc-mtext">Data Management</span>
            </a>
        </li>
        <li class="pc-item {{ request()->routeIs('admin.attempts.*') ? 'active' : '' }}">
            <a href="{{ route('admin.attempts.index') }}" class="pc-link">
                <span class="pc-micon"><i class="ti ti-report-analytics"></i></span>
                <span class="pc-mtext">Results & Attempts</span>
            </a>
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
        
        <li class="pc-item pc-caption">
          <label>Account</label>
          <i class="ti ti-user-circle"></i>
        </li>
        <li class="pc-item">
            <a href="#" class="pc-link" onclick="event.preventDefault(); document.getElementById('sidebar-logout-form').submit();">
                <span class="pc-micon"><i class="ti ti-power"></i></span>
                <span class="pc-mtext">Logout</span>
            </a>
            <form id="sidebar-logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </li>
      </ul>
    </div>
  </div>
</nav>
