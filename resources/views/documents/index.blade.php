@extends('layouts.app')

@section('content')
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
                                        <th>Type</th>
                                        <th>Project ID</th>
                                        
                                        <th>Date</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse($documents as $document)
                                <tr>
                                    <td>{{ $document->id }}</td>
                                    <td>
                                        
                                            {{ $document->name }}
                                    </td>
                                    <td>{{ $document->file_type }}</td>
                                    <td>{{ $document->project_id }}</td>
                                    
                                    <td>{{ $document->updated_at->format('d/m/Y H:i') }}</td>

                                    <td class="text-right">
                                    @can('update document')
                                        <a href="#" class="edit-document-btn" data-id="{{ $document->id }}"
                                           data-name="{{ $document->name }}"
                                           data-file_type="{{ $document->file_type }}"
                                           data-project_id="{{ $document->project_id }}"
                                           data-access="{{ $document->access }}"
                                           data-date_added="{{ $document->date_added }}"
                                           data-toggle="modal" data-target="#editDocumentModal">
                                           <i class="fas fa-pencil-alt m-r-5"></i>
                                        </a>
                                        @endcan('update document')
                                        @can('delete document')
                                        <a href="#" class="delete-document-btn" data-id="{{ $document->id }}" data-toggle="modal" data-target="#delete_modal">
                                            <i class="fas fa-trash-alt m-r-5"></i>
                                        </a>
                                        @endcan

                                    {{-- Icône modification/révision - visible uniquement si admin/superviseur OU accès 'write' --}}
                                    @php
                                        $user = auth()->user();
                                        
                                        // Vérification si l'utilisateur a un accès en écriture (write) sur ce document
                                        $hasWriteAccess = $document->accesses
                                            ->where('user_id', $user->id)
                                            ->where('permission', 'write')
                                            ->isNotEmpty();

                                        // Vérification si l'utilisateur a un accès en lecture (read) sur ce document
                                        $hasReadAccess = $document->accesses
                                            ->where('user_id', $user->id)
                                            ->where('permission', 'read')
                                            ->isNotEmpty();
                                    @endphp

                                    {{-- Icône de téléchargement - visible uniquement si admin/superviseur ou write --}}
                                    @if ($user->hasRole('admin') || $user->hasRole('superviseur') || $hasWriteAccess || $hasReadAccess )
                                        <a href="{{ route('documents.download', $document->id) }}" class="download-document-btn">
                                            <i class="fas fa-download m-r-5"></i> 
                                        </a>
                                    @endif

                                    {{-- Icône de révision - visible uniquement si admin/superviseur ou write --}}
                                    @if ($user->hasRole('admin') || $user->hasRole('superviseur') || $hasWriteAccess)
                                        <a href="{{ route('documents.revision', $document->id) }}" class="revision-document-btn" 
                                        data-id="{{ $document->id }}" data-toggle="modal" data-target="#revisionModal">
                                            <i class="fas fa-edit m-r-5"></i> 
                                        </a>
                                    @endif

    
                                </tr>
                                   <!-- Vos lignes de document existantes -->
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            @if(!empty($searchTerm))
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
                                <input type="text" class="form-control" id="docName" name="name" required>
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
                        <label for="editFileType">Type</label>
                        <select class="form-control" id="editFileType" name="file_type" required>
                            <option value="">Select type</option>
                            <option value="pdf">pdf</option>
                            <option value="docx">docx</option>
                            <option value="ppt">ppt</option>
                            <option value="excel">excel</option>
                            <option value="catia">catia</option>
                        </select>
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




<!-- Modal Edit Document -->
<div class="modal fade" id="editDocumentModal" tabindex="-1" role="dialog" aria-labelledby="editDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editDocumentModalLabel">Edit Document</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form id="editDocumentForm" method="POST" action="{{ route('documents.update', ':id') }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="editName">Document Name</label>
                        <input type="text" class="form-control" id="editName" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="editFileType">Type</label>
                        <select class="form-control" id="editFileType" name="type" required>
                            <option value="">Select type</option>
                            <option value="pdf">pdf</option>
                            <option value="docx">docx</option>
                            <option value="ppt">ppt</option>
                            <option value="excel">excel</option>
                            <option value="catia">catia</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="editProjectId">Project ID</label>
                        <input type="number" class="form-control" id="editProjectId" name="project_id" required>
                    </div>


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
    $(document).ready(function() {
        // Edit document modal
        $('.edit-document-btn').click(function() {
            var doc = $(this).data();
            $('#editDocumentModal form').attr('action', '/documents/' + doc.id); // Update form action with the document ID
            $('#editName').val(doc.name);
            $('#editFileType').val(doc.file_type);
            $('#editProjectId').val(doc.project_id);
            $('#editAccess').val(doc.access);
            $('#editDate').val(doc.date_added);
        });

        // Delete document modal
        $('.delete-document-btn').click(function() {
            var docId = $(this).data('id');
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
    });
</script>
@endsection