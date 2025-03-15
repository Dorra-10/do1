@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <div class="mt-5">
                        <h4 class="card-title float-left mt-2">Permission</h4>
                        @can('create permission')
                        <a href="{{ url('permissions/create') }}" class="btn btn-primary float-right veiwbutton">Add Permission</a>
                        @endcan
                    </div>
                </div>
            </div>
            @if (session('status'))
    <div id="successMessage" class="alert alert-success">
        {{ session('status') }}
    </div>
    <script>
        // Faire disparaître le message après 2 secondes
        setTimeout(function() {
            document.getElementById('successMessage').style.display = 'none';
        }, 2000);
    </script>
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
                                        <th>Permission ID</th>
                                        <th>Name</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($permissions as $permission)
                                    <tr>
                                        <td>{{ $permission->id }}</td>
                                        <td>{{ $permission->name }}</td>
                                        <td class="text-right">
                                          
                                                @can('update permission')
                                                    <a href="{{ url('permissions/'.$permission->id.'/edit') }}">
                                                        <i class="fas fa-pencil-alt m-r-5"></i> 
                                                    </a>
                                                @endcan
                                                @can('delete permission')
                                                    <a href="#" class="delete-btn" data-toggle="modal" data-target="#deletePermissionModal" data-url="{{ route('permissions.destroy', $permission->id) }}">
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
    <!-- Modal de confirmation -->
    <div id="deletePermissionModal" class="modal fade delete-modal" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <h3 class="delete_class">Are you sure you want to delete this Permission?</h3>
                <div class="m-t-20">
                    <a href="#" class="btn btn-white" data-dismiss="modal">No</a>
                    <form id="deletePermissionForm" method="POST" action="" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Yes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    // Lors du clic sur le lien de suppression, mettre à jour l'URL du formulaire
    $('#deletePermissionModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Bouton qui a ouvert le modal
        var url = button.data('url'); // URL de suppression de la permission

        var modal = $(this);
        modal.find('#deletePermissionForm').attr('action', url); // Mettre à jour l'action du formulaire
        
    });
    
</script>

@endsection

