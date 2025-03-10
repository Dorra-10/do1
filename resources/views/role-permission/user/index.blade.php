@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <div class="mt-5">
                        <h4 class="card-title float-left mt-2">Users</h4>
                        @can('create user')
                        <a href="{{ url('users/create') }}" class="btn btn-primary float-right veiwbutton">Add user</a>
                        @endcan
                    </div>
                </div>
            </div>
            @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
            @endif
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
                                        <th>Roles</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $user)
                                    <tr id="user-row-{{ $user->id }}">
                                        <td>{{ $user->id }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
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
                                    @endforeach                               
                                </tbody>
                            </table>
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
