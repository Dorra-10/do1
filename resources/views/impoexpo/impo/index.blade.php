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
                        <h4 class="card-title float-left mt-2">Imports</h4> 
                        <a href="{{ route('imports.upload') }}" class="btn btn-primary float-right" data-toggle="modal" data-target="#addImportModal">Import Document</a>
                      
                    </div>
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
                                        <th> ID</th>
                                        <th>Name</th>
                                        <th>Project ID</th>
                                        <th>Owner</th>
                                        <th>Company</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($imports as $import)
                                        <tr>
                                            <td>{{ $import->id }}</td>
                                            <td>{{ $import->name }}</td>
                                            <td>{{ $import->project_id }}</td>
                                            <td>{{ $import->owner }}</td>
                                            <td>{{ $import->company }}</td>
                                            <td>
                                                <span class="description-preview">
                                                    {{ Str::limit($import->description, 30) }}
                                                </span>

                                                @if(strlen($import->description) > 30)
                                                    <button class="btn btn-sm btn-link view-full-description" data-description="{{ $import->description }}">
                                                        See More
                                                    </button>
                                                @endif
                                            </td>
                                            
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No import documents found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>


                            </table>
                           
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour importer un document -->

<div class="modal fade" id="addImportModal" tabindex="-1" role="dialog" aria-labelledby="addImportModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addImportModalLabel">Add Import</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('imports.upload') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <!-- Nom de l'import -->
                    <div class="form-group">
                        <label for="importName">Import Name</label>
                        <input type="text" class="form-control" id="importName" name="name" required placeholder="Name">
                    </div>

                    <!-- Projet lié -->
                    <div class="form-group">
                        <label for="project_id">Select Project</label>
                        <select class="form-control" id="project_id" name="project_id" required>
                            <option value="">Select Project</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Propriétaire -->
                    <div class="form-group">
                        <label for="Owner">Owner</label>
                        <input type="text" class="form-control" id="Owner" name="owner" required placeholder="Owner">
                    </div>

                    <!-- Entreprise -->
                    <div class="form-group">
                        <label for="Company">Company</label>
                        <input type="text" class="form-control" id="Company" name="company" required placeholder="Company">
                    </div>

                    <!-- Description -->
                    <div class="form-group">
                        <label for="Description">Description</label>
                        <textarea name="description" rows="4" class="form-control" placeholder="Description"></textarea>
                    </div>

                    <!-- Date d'ajout -->
                    <div class="form-group">
                        <label for="importDate">Date Added</label>
                        <input type="date" class="form-control" id="importDate" name="date_added" required>
                    </div>

                    <!-- Fichier -->
                    <div class="form-group">
                        <label for="document">Upload Import File</label>
                        <input type="file" class="form-control" id="document" name="document" required>
                    </div>
                </div>

                <!-- Boutons -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal pour afficher la description complète -->

<div class="modal fade" id="descriptionModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document"> <!-- Plus grand pour les longues descriptions -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Document Description</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="fullDescriptionContent">
                <!-- Le contenu de la description sera injecté ici en JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script>
    $('.delete-document-btn').click(function () {
        var docId = $(this).data('id');
        var actionUrl = "{{ url('/exports') }}/" + docId;
        $('#deleteForm').attr('action', actionUrl);
    });


    document.addEventListener('DOMContentLoaded', function() {
    // Gestion du clic sur "Voir plus"
    document.querySelectorAll('.view-full-description').forEach(button => {
        button.addEventListener('click', function() {
            const description = this.getAttribute('data-description');
            document.getElementById('fullDescriptionContent').textContent = description;
            $('#descriptionModal').modal('show');
        });
    });
});
</script>


@endsection