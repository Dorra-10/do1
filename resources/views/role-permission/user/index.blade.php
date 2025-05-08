@extends('layouts.app')

@section('content')
@if (session('success'))
        <div id="success-message" style="
            position: fixed;
            top: 20px;
            right: 20px;
            background-color:rgb(86, 109, 103);
            color: white;
            padding: 15px 25px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            z-index: 9999;
        ">
            {{ session('success') }}
        </div>

        <script>
            setTimeout(function() {
                var message = document.getElementById('success-message');
                if (message) {
                    message.style.display = 'none';
                }
            }, 2000);
        </script>
    @endif
    @if (session('error'))
    <div id="error-message" style="
        position: fixed;
        top: 20px;
        right: 20px;
        background-color:rgb(95, 87, 87); 
        color: white;
        padding: 15px 25px;
        border-radius: 5px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
        z-index: 9999;
    ">
        {{ session('error') }}
    </div>

    <script>
        setTimeout(function() {
            var message = document.getElementById('error-message');
            if (message) {
                message.style.display = 'none';
            }
        }, 2000);
    </script>
@endif
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <div class="mt-5">
                        <h4 class="card-title float-left mt-2">Users</h4>
                        @can('create user')
                        <a href="{{ url('users/create') }}" class="btn btn-primary float-right ">Add user</a>
                        @endcan
                    </div>
                </div>
            </div> 
</div>
            
<div class="row mb-3">
<div class="col-sm-12 col-md-6">
<form method="GET" action="{{ route('role-permission.user.index') }}" class="form-inline">
    <div class="input-group w-100">
        <input type="text" name="search" class="form-control" placeholder="Enter the user name" value="{{ request('search') }}">
        <div class="input-group-append">
            <button type="submit" class="btn btn-success">Search</button>
        </div>
    </div>
</form>
</div>
</div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table">
                    <div class="card-body booking_card">
                        <div class="table-responsive">
                            <table class="datatable table table-stripped table-hover table-center mb-0">
                                <thead>
                                    <tr>
                                        <th>User ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone Number</th>
                                        <th>Roles</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $user)
                                    <tr id="user-row-{{ $user->id }}">
                                        <td>{{ $user->id }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->phone_number }}</td>
                                        <td class="text-left">
                                            @foreach ($user->getRoleNames() as $rolename)
                                                <span class="badge badge-pill bg-success inv-badge">{{ $rolename }}</span>
                                            @endforeach
                                        </td>
                                        <td class="text-right">
                                            @can('update user')  
                                            <a href="{{ url('users/'.$user->id.'/edit') }}">
                                                <i class="fas fa-pencil-alt m-r-5"></i> 
                                            </a>
                                            @endcan
                                            @can('delete user')  
                                            <a href="#" class="delete-user-btn" data-id="{{ $user->id }}" data-toggle="modal" data-target="#delete_modal">
                                                <i class="fas fa-trash-alt m-r-5"></i> 
                                            </a>
                                            @endcan  
                                        </td>
                                    </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5">Aucun utilisateur trouvé</td>
                                        </tr>
                                    @endforelse                              
                                </tbody>
                            </table>
                            <div class="d-flex justify-content-center">
                                {{ $users->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div id="delete_modal" class="modal fade delete-modal" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <h3 class="delete_class">Are you sure you want to delete this user?</h3>
                    <div class="m-t-20">
                        <a href="#" class="btn btn-white" data-dismiss="modal">No</a>
                        <button type="button" id="confirm_delete" class="btn btn-danger">Yes</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script pour gérer la suppression -->
<script>
    $(document).ready(function() {
        let userIdToDelete = null;

        // Ouvrir la popup et stocker l'ID de l'utilisateur
        $('.delete-user-btn').on('click', function() {
            userIdToDelete = $(this).data('id');
        });

        // Lorsque l'admin clique sur "Yes"
        $('#confirm_delete').on('click', function() {
            if (!userIdToDelete) return;

            let deleteUrl = '/users/' + userIdToDelete;
            console.log("Deleting user:", deleteUrl); // Debugging

            $.ajax({
                url: deleteUrl,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    console.log("Server Response:", response); // Debugging

                    // Supprimer la ligne du tableau après suppression
                    $('#user-row-' + userIdToDelete).remove();
                    
                    // Fermer la popup
                    $('#delete_modal').modal('hide');
                },
                error: function(xhr) {
                    console.error("Error deleting user:", xhr.responseText); // Debugging
                    alert("Failed to delete user. Check console.");
                }
            });
        });
    });
</script>
@endsection