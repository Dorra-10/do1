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
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <div class="mt-5">
                        <h4 class="card-title float-left mt-2">Documents</h4> 
                       @can('upload document')
                        <a href="{{ route('documents.upload') }}" class="btn btn-primary float-right" data-toggle="modal" data-target="#addDocumentModal">Add Document</a>
                       @endcan('upload document')
                    </div>
                </div>
            </div>
        </div>
        <!-- Search Filter -->
        <div class="row mb-3">
            <div class="col-sm-12 col-md-6">
                <form method="GET" action="{{ route('documents.index') }}">
                    <div class="input-group w-100">
                        <input type="text" name="search" class="form-control" 
                            placeholder="Search document name, type or project" 
                            value="{{ $searchTerm ?? '' }}">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-success">Search</button>
                            @if(!empty($searchTerm))
                                <a href="{{ route('documents.index') }}" class="btn btn-secondary ml-2">Reset</a>
                            @endif
                        </div>
                    </div>
                </form>
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
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($documents as $document)
                                        <tr>
                                            <td>{{ $document->id }}</td>
                                            <td>{{ $document->name }}.{{ $document->file_type }}{{ $document->updated_at->format('d/m/Y H:i') }}</td>
                                            <td>{{ $document->project_id }}</td>
                                            <td>{{ $document->owner }}</td>
                                            <td>{{ $document->company }}</td>
                                            <td>
                                                <span class="description-preview">
                                                    {{ Str::limit($document->description, 20) }} <!-- Limite à 50 caractères -->
                                                </span>
                                                
                                                @if(strlen($document->description) > 50)
                                                <button class="btn btn-sm btn-link view-full-description" 
                                                        data-description="{{ $document->description }}">
                                                    See More
                                                </button>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                {{-- Bouton de modification --}}
                                                @can('update document')
                                                    <a href="#" class="edit-document-btn"
                                                        data-id="{{ $document->id }}"
                                                        data-name="{{ $document->name }}"
                                                        data-project_id="{{ $document->project_id }}"
                                                        data-owner="{{ $document->owner }}"
                                                        data-company="{{ $document->company }}"
                                                        data-description="{{ $document->description }}"
                                                        data-date_added="{{ $document->date_added }}"
                                                        data-toggle="modal"
                                                        data-target="#editDocumentModal">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </a>
                                                @endcan

                                                {{-- Bouton de suppression --}}
                                                @can('delete document')
                                                    <a href="#" class="delete-document-btn"
                                                        data-id="{{ $document->id }}"
                                                        data-toggle="modal"
                                                        data-target="#delete_modal">
                                                        <i class="fas fa-trash-alt m-r-5"></i>
                                                    </a>
                                                @endcan

                                                {{-- Icônes conditionnelles selon les accès --}}
                                                @php
                                                    $user = auth()->user();

                                                    $hasWriteAccess = $document->accesses
                                                        ->where('user_id', $user->id)
                                                        ->where('permission', 'write')
                                                        ->isNotEmpty();

                                                    $hasReadAccess = $document->accesses
                                                        ->where('user_id', $user->id)
                                                        ->where('permission', 'read')
                                                        ->isNotEmpty();
                                                @endphp

                                                {{-- Téléchargement autorisé --}}
                                                @if ($user->hasRole('admin') || $user->hasRole('superviseur') || $hasWriteAccess || $hasReadAccess)
                                                    <a href="{{ route('documents.download', $document->id) }}" class="download-document-btn">
                                                        <i class="fas fa-download m-r-5"></i>
                                                    </a>
                                                @endif

                                                {{-- Révision autorisée --}}
                                                @if ($user->hasRole('admin') || $user->hasRole('superviseur') || $hasWriteAccess)
                                                    <a href="{{ route('documents.revision', $document->id) }}"
                                                        class="revision-document-btn"
                                                        data-id="{{ $document->id }}"
                                                        data-toggle="modal"
                                                        data-target="#revisionModal">
                                                        <i class="fas fa-edit m-r-5"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">
                                                @if (!empty($searchTerm))
                                                    <div class="alert alert-info">
                                                        <i class="fas fa-search"></i> No documents found for "{{ $searchTerm }}"
                                                    </div>
                                                    <a href="{{ route('documents.index') }}" class="btn btn-sm btn-outline-primary">
                                                        Show all documents
                                                    </a>
                                                @else
                                                    <div class="alert alert-info">
                                                        <i class="fas fa-info-circle"></i> No documents available
                                                    </div>
                                                    @can('upload document')
                                                        <a href="#" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addDocumentModal">
                                                            <i class="fas fa-plus"></i> Add document
                                                        </a>
                                                    @endcan
                                                @endif
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>

                            </table>
                            <div class="d-flex justify-content-center mt-3">
                                {{ $documents->appends(['search' => $searchTerm])->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Add Document -->

<div class="modal fade" id="addDocumentModal" tabindex="-1" role="dialog" aria-labelledby="addDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addDocumentModalLabel">Add Document</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('documents.upload') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                <!-- Project ID -->
                <div class="form-group">
                        <div class="form-group">
                                <label for="docName">Document Name</label>
                                <input type="text" class="form-control" id="docName" name="name" required placeholder="Name">
                            </div>
                        <label for="project_id">Select Project</label>
                        <select class="form-control" id="project_id" name="project_id" required>
                            <option value="">Select Project</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                                <label for="Owner">Owner</label>
                                <input type="text" class="form-control" id="Owner" name="owner" required placeholder="Owner">
                    </div>
                    <div class="form-group">
                                <label for="Company">Company</label>
                                <input type="text" class="form-control" id="Company" name="company" required placeholder="Company">
                    </div>
                    <div class="form-group">
                                <label for="Description">Description</label>
                                <textarea name="description" rows="4" class="form-control" placeholder="Description"></textarea>
                            </div>
                    <!-- Date Added -->
                    <div class="form-group">
                        <label for="docDate">Date Added</label>
                        <input type="date" class="form-control" id="docDate" name="date_added" required>
                    </div>
                    <!-- File Upload -->
                    <div class="form-group">
                        <label for="document">Upload Document</label>
                        <input type="file" class="form-control" id="document" name="document" required>
                    </div>
                       
                    
                    
            
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Document</button>
                </div>
                </div>
            </form>
        </div>
    </div>
</div>



<!-- Edit Document -->
<div class="modal fade" id="editDocumentModal" tabindex="-1" role="dialog" aria-labelledby="editDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editDocumentModalLabel">Edit Document</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>

            <form id="editDocumentForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <!-- Document Name -->
                    <div class="form-group">
                        <label for="editName">Document Name</label>
                        <input type="text" class="form-control" id="editName" name="name" required>
                    </div>

                    <!-- Project Selection -->
                    <div class="form-group">
                        <label for="editProjectId">Select Project</label>
                        <select class="form-control" id="editProjectId" name="project_id" required>
                            <option value="">Select Project</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="editOwner">Owner</label>
                        <input type="text" class="form-control" id="editOwner" name="owner" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="editCompany">Company</label>
                        <input type="text" class="form-control" id="editCompany" name="company" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="editDescription">Description</label>
                        <textarea id="editDescription" name="description" rows="4" class="form-control"></textarea>
                    </div>

                    <!-- Date Added -->
                    <div class="form-group">
                        <label for="editDate">Date Added</label>
                        <input type="date" class="form-control" id="editDate" name="date_added" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Delete Document -->
<div class="modal fade" id="delete_modal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this document ?</p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour afficher la description complète -->
<div class="modal fade" id="descriptionModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Description</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="fullDescriptionContent">
                <!-- Le contenu sera inséré ici par JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<!-- Modal Revision -->
<div class="modal fade" id="revisionModal" tabindex="-1" role="dialog" aria-labelledby="revisionModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="revisionModalLabel">Edit Content</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" enctype="multipart/form-data" id="revisionForm">
                    @csrf
                    <div class="form-group">
                        <label for="file">Choose file</label>
                        <input type="file" name="file" id="file" class="form-control" required accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.catpart,.catproduct,.cgr">
                        <div id="fileError" class="text-danger"></div>
                    </div>
                    <button type="submit" class="btn btn-success">Submit</button>
                    <div id="loading" style="display: none;">Uploading...</div>
                </form>
            </div>
        </div>
    </div>
</div>


<script>

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.edit-document-btn').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const projectId = this.dataset.projectId || this.dataset.project_id; // les deux formats possibles
            const dateAdded = this.dataset.dateAdded || this.dataset.date_added;
            const owner = this.dataset.owner || '';
            const company = this.dataset.company || '';
            const description = this.dataset.description || '';

            // Remplir les champs
            document.getElementById('editName').value = name;
            document.getElementById('editOwner').value = owner;
            document.getElementById('editCompany').value = company;
            document.getElementById('editDescription').value = description; // Pour textarea, on utilise .value

            // Projet
            if (projectId) {
                document.getElementById('editProjectId').value = projectId;
            }

            // Date - format YYYY-MM-DD
            if (dateAdded) {
                const dateObj = new Date(dateAdded);
                const formattedDate = dateObj.toISOString().split('T')[0];
                document.getElementById('editDate').value = formattedDate;
            }

            // Mettre à jour l'action du formulaire
            document.getElementById('editDocumentForm').action = `/documents/${id}`;
            
            // Ouvrir le modal
            $('#editDocumentModal').modal('show');
        });
    });
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





        // Delete document modal
        $('.delete-document-btn').click(function() {
    var docId = $(this).data('id');
    // Mettre à jour l'attribut action du formulaire pour correspondre à l'URL de suppression
    $('#deleteForm').attr('action', '/documents/' + docId);
});


        // Revision document modal
        $('.revision-document-btn').click(function() {
            let documentId = $(this).data('id');
            console.log("ID du document : " + documentId);
            $('#revisionForm').attr('action', '/documents/revision/' + documentId); // Set correct action
            $('#revisionModal').modal('show');
        });

        // AJAX request for document revision
        $('#revisionForm').submit(function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            let documentId = $('#revisionForm').attr('action').split('/').pop();
            
            $.ajax({
                url: '/documents/' + documentId + '/revision', 
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    console.log("Document mis à jour avec succès");
                    $('#revisionModal').modal('hide');
                    location.reload(); // Reload the page to show updated content
                },
                error: function(xhr, status, error) {
                    console.log("Erreur lors de la mise à jour du document");
                    $('#fileError').text("Erreur de mise à jour du document.");
                }
            });
        });

</script>
@endsection