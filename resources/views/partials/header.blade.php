<header class="pc-header">
  <div class="header-wrapper"> 
    <!-- [Mobile Media Block] start -->
    <div class="me-auto pc-mob-drp">
      <ul class="list-unstyled">
        <li class="pc-h-item pc-sidebar-collapse">
          <a href="#" class="pc-head-link ms-0" id="sidebar-hide">
            <i class="ti ti-menu-2"></i>
          </a>
        </li>
        <li class="pc-h-item pc-sidebar-popup">
          <a href="#" class="pc-head-link ms-0" id="mobile-collapse">
            <i class="ti ti-menu-2"></i>
          </a>
        </li>
      </ul>
    </div>
    <!-- [Mobile Media Block end] -->
    <div class="ms-auto">
      <ul class="list-unstyled">
        {{-- @auth --}}

        <li class="dropdown pc-h-item header-user-profile">
          <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" data-bs-auto-close="outside" aria-expanded="false">
            <img src="{{ asset('assets/images/user/avatar-2.jpg') }}" alt="user-image" class="user-avtar">
            <span>{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</span>
          </a>
          <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
            <div class="dropdown-header">
              <div class="d-flex mb-1">
                <div class="flex-shrink-0">
                  <img src="{{ asset('assets/images/user/avatar-2.jpg') }}" alt="user-image" class="user-avtar wid-35">
                </div>
                <div class="flex-grow-1 ms-3">
                  <h6 class="mb-1">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</h6>
                  <span>{{ ucfirst(auth()->user()->role) }}</span>
                </div>
              </div>
            </div>
            <hr class="border-secondary border-opacity-50 my-2">
            <div class="d-grid px-3">
                <button class="btn btn-primary" onclick="event.preventDefault(); document.getElementById('header-logout-form').submit();">
                    <i class="ti ti-power me-2"></i>Logout
                </button>
                 <form id="header-logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
          </div>
        </li>
        {{-- @endauth --}}
      </ul>
    </div>
  </div>
</header>
