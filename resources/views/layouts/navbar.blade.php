<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                <li class="active"> 
				<a href="{{ url('/projects') }}" class="nav-link">
            <i class="fas fa-folder"></i> <span>Projects</span>
        </a>
                </li>
                <li class="list-divider"></li>

                <li class="submenu"> <a href="#"><i class="fas fa-key"></i> <span> Permission </span> <span class="menu-arrow"></span></a>
							<ul class="submenu_class" style="display: none;">
								<li><a href="{{ url('permissions') }}"> All Permission </a></li>
								<li><a href="{{ url('permissions/create') }}"> Add permission </a></li>
							</ul>
						</li>
						<li class="submenu"> <a href="#"><i class="fas fa-user-tag"></i> <span> Role </span> <span class="menu-arrow"></span></a>
							<ul class="submenu_class" style="display: none;">
								<li><a href="{{ url('roles') }}"> All Roles</a></li>
								<li><a href="{{ url('roles/create') }}"> Add Role </a></li>
							</ul>
						</li>
						<li class="submenu"> <a href="#"><i class="fas fa-user"></i> <span> Users </span> <span class="menu-arrow"></span></a>
							<ul class="submenu_class" style="display: none;">
								<li><a href="{{ url('users') }}"> All Users</a></li>
								<li><a href="{{ url('users/create') }}"> Add User </a></li>
							</ul>
						</li>

               
                
            </ul>
        </div>
    </div>
</div>
