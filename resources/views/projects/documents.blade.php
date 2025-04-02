@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <div class="mt-5">
                   <h4> Documents of {{ $project->name }} </h4>
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
                                        <th>Type</th>
                                        
                                        <th>Access</th>
                                        <th>Date</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($documents as $document)
                                <tr>
                                    <td>{{ $document->id }}</td>
                                    <td>
                                        <a href="{{ route('documents.download', $document->id) }}" target="_blank">
                                            {{ $document->name }}
                                        </a>
                                    </td>
                                    <td>{{ $document->file_type }}</td>
                                   
                                    <td>{{ $document->access }}</td>
                                    <td>{{ $document->date_added }}</td>
                                    <td class="text-right">
                                        <a href="#" class="edit-document-btn" data-id="{{ $document->id }}"
                                           data-name="{{ $document->name }}"
                                           data-file_type="{{ $document->file_type }}"
                                           data-project_id="{{ $document->project_id }}"
                                           data-access="{{ $document->access }}"
                                           data-date_added="{{ $document->date_added }}"
                                           data-toggle="modal" data-target="#editDocumentModal">
                                           <i class="fas fa-pencil-alt m-r-5"></i>
                                        </a>
                                        @can('delete project')
                                        <a href="#" class="delete-document-btn" data-id="{{ $document->id }}" data-toggle="modal" data-target="#delete_modal">
                                            <i class="fas fa-trash-alt m-r-5"></i>
                                        </a>
                                        @endcan
                                        <a href="{{ route('documents.revision', $document->id) }}" class="revision-document-btn" data-id="{{ $document->id }}" data-toggle="modal" data-target="#revisionModal">
                                            <i class="fas fa-edit m-r-5"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                                @if($documents->isEmpty())
                                    <tr>
                                        <td colspan="7" class="text-center">Aucun document trouvé.</td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
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
                <h5 class="modal-title" id="addDocumentModalLabel">Ajouter un Document</h5>
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
                        <label for="project_id">Sélectionner un Projet</label>
                        <select class="form-control" id="project_id" name="project_id" required>
                            <option value="">Choisir un projet</option>
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
                            <option value="catia">actia</option>
                        </select>
                    </div>
                     <!-- Access Information -->
                    <div class="form-group">
                        <label for="access">Accès</label>
                        <select class="form-control" id="access" name="access">
                            <option value="read">Lecture seule</option>
                            <option value="read and write">Lecture et écriture</option>
                        </select>
                    </div>
                    <!-- Date Added -->
                    <div class="form-group">
                        <label for="docDate">Date Added</label>
                        <input type="date" class="form-control" id="docDate" name="date_added" required>
                    </div>
                    <!-- File Upload -->
                    <div class="form-group">
                        <label for="document">Télécharger un Document</label>
                        <input type="file" class="form-control" id="document" name="document" required>
                    </div>
                       
                    
                    
            
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Ajouter Document</button>
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
                        <label for="editAccess">Access</label>
                        <select class="form-control" id="editAccess" name="access" required>
                            <option value="">Select access</option>
                            <option value="read">Read</option>
                            <option value="read and write">Read and Write</option>
                        </select>
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
                <p>Are you sure you want to delete this document?</p>
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

<!-- Modal Révision Document -->

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
                <!-- Formulaire d'upload -->
                @if(isset($document))
                    <form action="{{ route('documents.revision', $document->id) }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                        @csrf
                        <div class="form-group">
                            <label for="file">Choose file</label>
                            <input type="file" name="file" id="file" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-success">Submit</button>
                    </form>
                @endif
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
    });
</script>

@endsection