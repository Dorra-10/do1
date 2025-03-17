<div class="header">
    <div class="header-left p-3">
        <a href="/" class="logo"> <img src="{{asset('assets/img/logo.png')}}" width="20" height="50" alt="logo"> <span class="logoclass"></span> </a>
        <a href="/" class="logo logo-small"> <img src="{{asset('assets/img/logo.png')}}" alt="Logo" width="30" height="30"> </a>
    </div>
    <a href="javascript:void(0);" id="toggle_btn"> <i class="fe fe-text-align-left"></i> </a>
    <a class="mobile_btn" id="mobile_btn"> <i class="fas fa-bars"></i> </a>
    <ul class="nav user-menu">

    <li class="nav-item dropdown has-arrow">
    <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown"> 
        <span class="user-img">
            <img class="rounded-circle" src="{{ asset('assets/img/profiles/avatar-01.jpg') }}" width="31" alt="Soeng Souy">
        </span> 
    </a>
    <div class="dropdown-menu">
        <div class="user-header">
            <div class="avatar avatar-sm"> 
                <img src="{{ asset('assets/img/profiles/avatar-01.jpg') }}" alt="User Image" class="avatar-img rounded-circle"> 
            </div>
            <div class="user-text">
                <h6>{{ Auth::user()->name }}</h6>
                <p class="text-muted mb-0">Admin</p>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <a class="dropdown-item" href="{{ route('logout') }}"
                onclick="event.preventDefault();
                                this.closest('form').submit();">Logout</a>
        </form>
    </div>
</li>

    </ul>
    
</div>