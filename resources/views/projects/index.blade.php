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
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <div class="mt-5">
                        <h4 class="card-title float-left mt-2">Projects</h4>
                        @can('create project')
                            <a href="#" class="btn btn-primary float-right veiwbutton" data-toggle="modal" data-target="#addProjectModal">Create Project</a>
                        @endcan
                    </div>
                </div>
            </div>
            @if (session('status'))
                <div id="successMessage" class="alert alert-success">{{ session('status') }}</div>
            @endif
        </div>
      
<!-- Search Filter -->
<div class="row mb-3">
    <div class="col-sm-12 col-md-6">
        <form method="GET" action="{{ route('projects.index') }}">
            <div class="input-group w-100">
                <input type="text" name="search" class="form-control" 
                       placeholder="Search by name or type" 
                       value="{{ request('search', '') }}">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-success">Search</button>
                    @if(request()->has('search') && !empty(request('search')))
                        <a href="{{ route('projects.index') }}" class="btn btn-secondary ml-2">Reset</a>
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
                            <table class="datatable table  table-hover table-center mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Project Name</th>
                                        <th>Type</th>
                                        <th>Date</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($projects as $project)
                                        <tr id="project-row-{{ $project->id }}">
                                            <td>{{ $project->id }}</td>
                                            <td><a href="{{ route('projects.documents', $project->id) }}">{{ $project->name }}</a></td>
                                            <td>{{ $project->type }}</td>
                                            <td>{{ $project->date_added }}</td>
                                            <td class="text-right">
                                                @can('update project')
                                                    <a href="#" class="edit-project-btn" 
                                                       data-id="{{ $project->id }}" 
                                                       data-name="{{ $project->name }}" 
                                                       data-type="{{ $project->type }}" 
                                                       data-date_added="{{ $project->date_added }}" 
                                                       data-toggle="modal" 
                                                       data-target="#editProjectModal">
                                                        <i class="fas fa-pencil-alt m-r-5"></i> 
                                                    </a>
                                                @endcan
                                                @can('delete project')
                                                    <a href="#" class="delete-project-btn" 
                                                       data-id="{{ $project->id }}" 
                                                       data-toggle="modal" 
                                                       data-target="#delete_modal_{{ $project->id }}">
                                                        <i class="fas fa-trash-alt m-r-5"></i> 
                                                    </a>
                                                @endcan
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center">
                                                @if(request()->has('search') && !empty(request('search')))
                                                    <div class="alert alert-info">
                                                       No projects found for the search "{{ request('search') }}"
                                                    </div>
                                                    <a href="{{ route('projects.index') }}" class="btn btn-sm btn-outline-primary">
                                                    Show all projects
                                                </a>
                                                @else
                                                    <div class="alert alert-info">
                                                       No projects available at the moment
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <div class="d-flex justify-content-center mt-3">
                                {{ $projects->appends(request()->query())->links() }}
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Page Content -->

        <!-- Add Project Modal -->
        <div class="modal fade" id="addProjectModal" tabindex="-1" role="dialog" aria-labelledby="addProjectModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addProjectModalLabel">Add New Project</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('projects.store') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="projectName">Name</label>
                                <input type="text" class="form-control" id="projectName" name="name" placeholder="Enter project name" required>
                            </div>
                            <div class="form-group">
                                <label for="projectType">Type</label>
                                <select class="form-control" id="projectType" name="type" required>
                                    <option value="">Select type</option>
                                    <option value="In Progress">In Progress</option>
                                    <option value="Not Started">Not Started</option>
                                    <option value="Completed">Completed</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="projectDate">Date Added</label>
                                <input type="date" class="form-control" id="projectDate" name="date_added" required>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                             </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Project Modal -->
        <div class="modal fade" id="editProjectModal" tabindex="-1" role="dialog" aria-labelledby="editProjectModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editProjectModalLabel">Edit Project</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    @if(isset($project))
                        <form id="editProjectForm" method="POST" action="{{ route('projects.update', $project->id) }}">
                    @else
                        <form id="editProjectForm" method="POST" action="#">
                    @endif

                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="editName">Project Name</label>
                                <input type="text" class="form-control" id="editName" name="name"  required>
                            </div>
                            <div class="form-group">
                                <label for="editType">Type</label>
                                <select class="form-control" id="editType" name="type" required>
                                    <option value="">Select type</option>
                                    <option value="In Progress">In Progress</option>
                                    <option value="Not Started">Not Started</option>
                                    <option value="Completed">Completed</option>
                                    <option value="Cancelled">Cancelled</option>
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

        <!-- Delete Project Modal -->
        @foreach($projects as $project)
    <!-- Modal de confirmation de suppression -->
    <div class="modal fade" id="delete_modal_{{ $project->id }}" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the project "{{ $project->name }}"?</p>
                </div>
                <div class="modal-footer">
                    <form method="POST" action="{{ route('projects.destroy', $project->id) }}">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    
@endforeach


<script>

$(document).ready(function() {

// ✅ Gestion des messages de succès
if ($('#successMessage').length) {
    setTimeout(function() {
        $('#successMessage').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 3000); // 3 secondes (tu peux remettre 2000 si tu veux)
}

// ✅ Gestion du bouton de suppression de document
$('.delete-document-btn').click(function() {
    const documentId = $(this).data('id');
    const deleteUrl = '{{ route("documents.destroy", ":id") }}'.replace(':id', documentId);
    $('#deleteForm').attr('action', deleteUrl);
});

// ✅ Gestion du bouton de modification de projet
$('.edit-project-btn').click(function() {
    const projectId = $(this).data('id');
    const name = $(this).data('name');
    const type = $(this).data('type');
    const dateAdded = $(this).data('date_added');

    const editUrl = '{{ route("projects.update", ":id") }}'.replace(':id', projectId);
    $('#editProjectForm').attr('action', editUrl);

    $('#editName').val(name);
    $('#editType').val(type);
    $('#editDate').val(dateAdded);

    $('#editProjectModal').modal('show');
});

// ✅ (optionnel) Confirmation ou logique personnalisée pour suppression de projet
$('.delete-project-btn').click(function() {
    const projectId = $(this).data('id');
    // Traitement ou confirmation si besoin
    console.log("Projet à supprimer ID :", projectId);
});

});

</script>


@endsection