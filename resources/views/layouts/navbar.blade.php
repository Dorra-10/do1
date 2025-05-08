<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                <!-- Afficher uniquement Projects pour les employés -->
                @if (Auth::user()->hasRole('employee'))
                    <li class="active">
                        <a href="{{ url('/projects') }}" class="nav-link">
                            <i class="fas fa-folder"></i> <span>Projects</span>
                        </a>
                    </li>
                    <li >
                        <a href="{{ route('documents.index') }}" class="nav-link">
                            <i class="fas fa-file-alt"></i>
                            <span>Documents</span>
                        </a>
                    </li>  
                    
                    <li >
                        <a href="{{ route('history.index') }}" class="nav-link">
                            <i class="fas fa-history"></i> 
                            <span>History</span>
                        </a>
                    </li>
                  
                @elseif (Auth::user()->hasRole('superviseur'))  
				<li class="active">
                        <a href="{{ url('/projects') }}" class="nav-link">
                            <i class="fas fa-folder"></i> <span>Projects</span>
                        </a>
                    </li>
                    <li >
                        <a href="{{ route('documents.index') }}" class="nav-link">
                            <i class="fas fa-file-alt"></i>
                            <span>Documents</span>
                        </a>
                    </li> 
                    <li >
                        <a href="{{ route('history.index') }}" class="nav-link">
                            <i class="fas fa-history"></i> 
                            <span>History</span>
                        </a>
                    </li>
                    <li >
                        <a href="{{ route('access.index') }}" class="nav-link">
                        <i class="fas fa-lock"></i>
                            <span>Access</span>
                        </a>
                    </li> 
				<li class="submenu">
						<a href="#"><i class="fas fa-user"></i> <span>Users</span> <span class="menu-arrow"></span></a>
							<ul class="submenu_class" style="display: none;">
									<li><a href="{{ url('users') }}">All Users
                        </a></li>		
							</ul>
				</li>
                @else 
               
                    <!-- Menu complet pour les autres rôles admin-->
                    <li class="active">
                        <a href="{{ url('/projects') }}" class="nav-link">
                            <i class="fas fa-folder"></i> <span>Projects</span>
                        </a>
                    </li>
                    <li class="list-divider">
                    <li >
                        <a href="{{ route('documents.index') }}" class="nav-link">
                            <i class="fas fa-file-alt"></i>
                            <span>Documents</span>
                        </a>
                    </li>
                    <li >
                        <a href="{{ route('access.index') }}" class="nav-link">
                        <i class="fas fa-lock"></i>
                            <span>Access</span>
                        </a>
                    </li> 
                    <li >
                        <a href="{{ route('history.index') }}" class="nav-link">
                            <i class="fas fa-history"></i> 
                            <span>History</span>
                        </a>
                    </li>
                    
                    <li class="submenu">
                        <a href="#"><i class="fas fa-exchange-alt"></i><span>Impo/Expo</span> <span class="menu-arrow"></span> </a>
                        <ul class="submenu_class" style="display: none;">
                            <li><a href="{{ route('impoexpo.impo.index') }}">Import</a></li>
                            <li><a href="{{ route('impoexpo.expo.index') }}">Export</a></li>
                           
                        </ul>
                    </li> 
                    <li >
                        <a href="{{ route('dashboard') }}" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    <li class="submenu">
                        <a href="#"><i class="fas fa-user"></i> <span>Users</span> <span class="menu-arrow"></span></a>
                        <ul class="submenu_class" style="display: none;">
                            <li><a href="{{ url('users') }}">All Users</a></li>
                            <li><a href="{{ url('users/create') }}">Add User</a></li>
                        </ul>
                    </li>

                    <li class="submenu">
                        <a href="#"><i class="fas fa-user-tag"></i> <span>Role</span> <span class="menu-arrow"></span></a>
                        <ul class="submenu_class" style="display: none;">
                            <li><a href="{{ url('roles') }}">All Roles</a></li>
                           
                        </ul>
                    </li>
                    
                   
                    
                    
                
                    @endif    
            </ul>
        </div>
    </div>
</div>