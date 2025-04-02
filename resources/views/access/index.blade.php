@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <div class="mt-5">
                        <h4 class="card-title float-left mt-2">Access</h4> 
                        <a href="#" class="btn btn-primary float-right" data-toggle="modal" data-target="#givePermissionModal">Give Access</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table">
                    <div class="card-body booking_card">
                        <div class="table-responsive">
                            <table class="datatable table table-striped table-hover table-center mb-0">
                                <thead>
                                    <tr>
                                        <th> ID</th>
                                        <th>User With Permission</th>
                                        <th>Project Name</th>
                                        <th>Document Name</th>
                                        <th>Access Type</th>
                                        <th>Date</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($accesses as $access)
                                    <tr>
                                        <td>{{ $access->id }}</td>
                                        <td>{{ $access->user->name ?? 'Unknown User' }}</td>
                                        <td>{{ $access->project->name ?? 'Unknown Project' }}</td>
                                        <td>{{ $access->document->name ?? 'Unknown Document' }}</td>
                                        <td>{{ $access->permission }}</td>
                                        <td>{{ $access->created_at->format('d-m-Y H:i') }}</td>
                                        <td class="text-right">
                                            <!-- Actions (like Edit/Delete) can go here -->
                                        </td>
                                    </tr>
                                @endforeach
                                @if($accesses->isEmpty())
                                    <tr>
                                        <td colspan="7" class="text-center">No access records found.</td>
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

<!-- Add Permission Modal -->
<div class="modal fade" id="givePermissionModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Give Permission</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="givePermissionForm" method="POST" action="{{ url('/give-access') }}">
                @csrf
                <div class="modal-body">
                    <!-- User Select -->
                    <div class="form-group">
                        <label for="userSelect">User</label>
                        <select class="form-control" id="userSelect" name="user_id">
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

              <!-- Project Select -->
<div class="form-group">
    <label for="projectSelect">Project</label>
    <select class="form-control" id="projectSelect" name="project_id">
        @foreach($projects as $project)
            <option value="{{ $project->id }}">{{ $project->name }}</option>
        @endforeach
    </select>
</div>

<!-- Document Select -->
<div class="form-group">
    <label for="documentSelect">Document</label>
    <select class="form-control" id="documentSelect" name="document_id">
        <option value="" disabled selected>Sélectionnez d'abord un projet</option>
    </select>
</div>
                    <!-- Access Type Select -->
                    <div class="form-group">
                        <label for="accessType">Access Type</label>
                        <select class="form-control" id="accessType" name="permission">
                            <option value="read">Read</option>
                            <option value="write">Write</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Give Permission</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Script to handle the dynamic document loading -->
<script>
$(document).ready(function() {
    $('#projectSelect').change(function() {
        const projectId = $(this).val();
        const $documentSelect = $('#documentSelect');
        
        if (!projectId) {
            $documentSelect.html('<option value="" disabled selected>Sélectionnez un projet</option>');
            return;
        }

        $documentSelect.html('<option value="" disabled>Chargement...</option>');
        
        $.get(`/get-documents/${projectId}`)
            .done(function(documents) {
                $documentSelect.empty();
                
                if (documents.length === 0) {
                    $documentSelect.append($('<option>', {
                        value: '',
                        text: 'Aucun document disponible',
                        disabled: true
                    }));
                } else {
                    $.each(documents, function(index, doc) {
                        $documentSelect.append($('<option>', {
                            value: doc.id,
                            text: doc.name
                        }));
                    });
                }
            })
            .fail(function(error) {
                $documentSelect.html('<option value="" disabled>Erreur de chargement</option>');
                console.error(error);
            });
    });
});
</script>

@endsection
