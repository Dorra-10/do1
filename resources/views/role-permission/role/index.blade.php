@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <div class="mt-5">
                        <h4 class="card-title float-left mt-2">Roles</h4>
                       
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
                                        <th>Role ID</th>
                                        <th>Name</th>
                                       
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($roles as $role)
                                    <tr>
                                        <td>{{ $role->id }}</td>
                                        <td>{{ $role->name }}</td>
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
    <div id="deletRoleModal" class="modal fade delete-modal" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <h3 class="delete_class">Are you sure you want to delete this Role?</h3>
                <div class="m-t-20">
                    <button type="button" class="btn btn-white" data-dismiss="modal">No</button>
                    <form id="deleteRoleForm" method="POST" action="" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Yes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

<script>
    // Lors du clic sur le lien de suppression, mettre à jour l'URL du formulaire
    $('#deletRoleModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Bouton qui a ouvert le modal
        var url = button.data('url'); // URL de suppression du rôle

        var modal = $(this);
        modal.find('#deleteRoleForm').attr('action', url); // Mettre à jour l'action du formulaire
        
        // Ajouter un log pour déboguer l'URL
        console.log("URL de suppression : " + url);
    });
    
    // Vérification de l'URL du formulaire avant soumission
    $('#deleteRoleForm').submit(function (e) {
        var actionUrl = $(this).attr('action');
        if (!actionUrl) {
            e.preventDefault();
            alert("L'URL de suppression est vide !");
        }
    });
</script>
@endsection