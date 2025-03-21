@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <div class="mt-5">
                        <h4 class="card-title float-left mt-2">Documents</h4> 
                       
                        <a href="#" class="btn btn-primary float-right veiwbutton" data-toggle="modal" data-target="#addDocumentModal">Add Document</a> </div>
                       
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table">
                    <div class="card-body booking_card">
                        <div class="table-responsive">
                            <table class="datatable table table-stripped table table-hover table-center mb-0">
                                <thead>
                                    <tr>
                                        <th> ID</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Project ID</th>
                                        <th>Access</th>
                                        <th>Date</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($documents as $document)
                                <tr>
                                    <td>{{ $document->id }}</td>
                                    <td>{{ $document->name }}</td>
                                    <td>{{ $document->type }}</td>
                                    <td>{{ $document->project_id }}</td>
                                    <td>{{ $document->acces }}</td>
                                    <td>{{ $document->date_added }}</td>
                                    <td class="text-right">
                                    @can('update project')
                                        <a href="#" class="edit-document-btn" data-id="{{ $document->id }}" data-name="{{ $document->name }}" data-type="{{ $document->type }}" data-project_id="{{ $document->project_id }}" data-acces="{{ $document->acces }}" data-date_added="{{ $document->date_added }}" data-toggle="modal" data-target="#editDocumentModal">
                                            <i class="fas fa-pencil-alt m-r-5"></i>
                                        </a>
                                    @endcan
                                    @can('delete project')
                                    <a href="#" class="delete-document-btn" data-id="{{ $document->id }}" data-toggle="modal" data-target="#delete_modal">
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
            <form id="addDocumentForm" method="POST" action="{{ route('documents.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="docName">Document Name</label>
                        <input type="text" class="form-control" id="docName" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="docType">Type</label>
                        <select class="form-control" id="docType" name="type" required>
                            <option value="">Select type</option>
                            <option value="pdf">pdf</option>
                            <option value="word">word</option>
                            <option value="ppt">ppt</option>
                            <option value="excel">excel</option>
                            <option value="catia">catia</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="docProjectId">Project ID</label>
                        <input type="number" class="form-control" id="docProjectId" name="project_id" required>
                    </div>
                    <div class="form-group">
                        <label for="docAccess">Access</label>
                        <input type="text" class="form-control" id="docAccess" name="acces" required>
                    </div>
                    <div class="form-group">
                        <label for="docDate">Date Added</label>
                        <input type="date" class="form-control" id="docDate" name="date_added" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Document</button>
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
                <form id="editDocumentForm" method="POST" action="{{ route('documents.update', ['document' => 'DOCUMENT_ID']) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="editName">Document Name</label>
                            <input type="text" class="form-control" id="editName" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="editType">Type</label>
                            <select class="form-control" id="editType" name="type" required>
                                <option value="">Select type</option>
                                <option value="pdf">pdf</option>
                                <option value="word">word</option>
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
                            <input type="text" class="form-control" id="editAccess" name="acces" required>
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
</div>

<script>
$(document).ready(function() {
    // Lorsque vous cliquez sur le bouton de suppression
    $('.delete-document-btn').click(function() {
        const documentId = $(this).data('id');
        const deleteUrl = '{{ route("documents.destroy", ":id") }}'.replace(':id', documentId);
        $('#deleteForm').attr('action', deleteUrl);
    });

    // Lorsque vous cliquez sur le bouton d'édition
    $('.edit-document-btn').click(function() {
        const document = $(this).data();
        $('#editDocumentForm').attr('action', '{{ route("documents.update", ":id") }}'.replace(':id', document.id));
        $('#editName').val(document.name);
        $('#editType').val(document.type);
        $('#editProjectId').val(document.project_id);
        $('#editAccess').val(document.acces);
        $('#editDate').val(document.date_added);
    });
});
</script>

@endsection
