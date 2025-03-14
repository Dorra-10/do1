@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <div class="mt-5">
                        <h4 class="card-title float-left mt-2">Projects</h4>
                        <a href="#" class="btn btn-primary float-right veiwbutton" data-toggle="modal" data-target="#addProjectModal">Create Project</a>
                    </div>
                </div>
            </div>
            @if (session('status'))
            <div id="successMessage" class="alert alert-success">{{ session('status') }}</div>
            @endif
        </div>
        <!-- /Page Header -->

        <!-- Search Filter -->
        <div class="row filter-row">
            <div class="col-sm-12 col-md-9">
                <div class="form-group form-focus">
                    <input type="text" class="form-control floating">
                    
                </div>
            </div>
            <div class="col-sm-12 col-md-2">
                <a href="#" class="btn btn-success btn-block">Search</a>  
            </div>     
        </div>
        <!-- /Search Filter -->

        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table">
                    <div class="card-body booking_card">
                        <div class="table-responsive">
                            <table class="datatable table table-stripped table-hover table-center mb-0">
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
                                    @foreach ($projects as $project)
                                    <tr id="project-row-{{ $project->id }}">
                                        <td>{{ $project->id }}</td>
                                        <td><a href="#">{{ $project->name }}</a></td>
                                        <td>{{ $project->type }}</td>
                                        <td>{{ $project->date_added }}</td>
                                        <td class="text-right">
                                            <!-- Edit Button -->
                                            <a href="#" class="edit-project-btn" 
                                               data-id="{{ $project->id }}" 
                                               data-name="{{ $project->name }}" 
                                               data-type="{{ $project->type }}" 
                                               data-date_added="{{ $project->date_added }}" 
                                               data-toggle="modal" 
                                               data-target="#editProjectModal">
                                                <i class="fas fa-pencil-alt m-r-5"></i> 
                                            </a>

                                            <!-- Delete Button -->
                                            <a href="#" class="delete-project-btn" 
                                               data-id="{{ $project->id }}" 
                                               data-toggle="modal" 
                                               data-target="#delete_modal">
                                                <i class="fas fa-trash-alt m-r-5"></i> 
                                            </a>
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
                    <form id="editProjectForm" method="POST" action="{{ route('projects.update', ':id') }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="editName">Project Name</label>
                                <input type="text" class="form-control" id="editName" name="name" required>
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
                        <p>Are you sure you want to delete this project?</p>
                    </div>
                    <div class="modal-footer">
                        <form id="deleteForm" method="POST" action="{{ route('projects.destroy', ':id') }}">
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
</div>

<script>
    $(document).ready(function() {
        // Gestion des messages de succès
        if ($('#successMessage').length) {
            setTimeout(function() {
                $('#successMessage').fadeOut('slow', function() {
                    $(this).remove(); // Supprime complètement l'élément après le fadeOut
                });
            }, 2000); // 2000 ms = 2 secondes
        }

        // Edit button click event
        $('.edit-project-btn').click(function() {
            const projectId = $(this).data('id');
            const projectName = $(this).data('name');
            const projectType = $(this).data('type');
            const projectDate = $(this).data('date_added');

            // Set the values in the edit modal
            $('#editProjectForm').attr('action', '/projects/' + projectId);
            $('#editName').val(projectName);
            $('#editType').val(projectType);
            $('#editDate').val(projectDate);
        });

        // Delete button click event
        $('.delete-project-btn').click(function() {
            const projectId = $(this).data('id');
            $('#deleteForm').attr('action', '/projects/' + projectId);
        });
    });
</script>
@endsection