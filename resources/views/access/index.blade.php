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
                                            <a class="edit-access-btn" 
                                            data-id="{{ $access->id }}"
                                            data-user_name="{{ $access->user_name}}"
                                            data-project_name="{{ $access->project_name }}"
                                            data-document_name="{{ $access->documentname}}"
                                            data-access="{{ $access->permission }}"
                                            data-date_added="{{ $access->created_at->format('d-m-Y H:i') }}"
                                            data-toggle="modal" 
                                            data-target="#editAccessModal">
                                                <i class="fas fa-pencil-alt m-r-5"></i>
                                            </a>
                                            <a href="#" class="delete-document-btn" 
                                            data-id="{{ $access->id }}" 
                                            data-name="{{ $access->document->name ?? 'Unknown Document' }}" 
                                            data-toggle="modal" 
                                            data-target="#delete_modal">
                                                <i class="fas fa-trash-alt m-r-5"></i>
                                            </a>
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
                <h5 class="modal-title" id="exampleModalLabel">Give Access</h5>
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
<!-- Edit Permission Modal -->
<div class="modal fade" id="editAccessModal" tabindex="-1" role="dialog" aria-labelledby="editAccessModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAccessModalLabel">Change access</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editAccessForm" method="POST" action="{{ route('access.update') }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" name="access_id" id="access-id-to-edit">
                    <!-- Sélectionner l'utilisateur -->
                    <div class="form-group">
                        <label for="userSelect">User</label>
                        <select class="form-control" id="userSelect" name="user_id" required>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sélectionner le projet -->
                    <div class="form-group">
                        <label for="projectSelect">Project</label>
                        <select class="form-control" id="projectSelect" name="project_id" required>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sélectionner le document -->
                    <div class="form-group">
                        <label for="documentSelect">Document</label>
                        <select class="form-control" id="documentSelect" name="document_id" required>
                            <option value="" disabled selected>select project</option>
                        </select>
                    </div>

                    <!-- Type d'accès -->
                    <div class="form-group">
                        <label for="accessType">Access Type</label>
                        <select class="form-control" id="accessType" name="permission" required>
                            <option value="read">Read</option>
                            <option value="write">Write</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Access Edit</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="delete_modal" tabindex="-1" role="dialog" aria-labelledby="delete_modal_label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="delete_modal_label">Confirm deletion</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this permission ?</p>
            </div>
            <div class="modal-footer">
                <form id="delete-access-form" method="POST" action="{{ route('access.delete') }}">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="access_id" id="access-id-to-delete">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Script to handle the dynamic document loading -->
 <script>
$(document).ready(function() {
    // =============================================
    // FONCTION COMMUNE DE CHARGEMENT DES DOCUMENTS
    // =============================================
    function loadDocuments(projectId, selectedDocumentId = null, targetSelector = '#documentSelect') {
        const $documentSelect = $(targetSelector);
        
        if (!projectId) {
            $documentSelect.html('<option value="" disabled selected>Sélectionnez un projet d\'abord</option>');
            return Promise.resolve();
        }

        $documentSelect.html('<option value="">Chargement en cours...</option>');

        return $.ajax({
            url: '/get-documents/' + projectId,
            method: 'GET',
            dataType: 'json'
        }).then(function(documents) {
            let options = '<option value="">Sélectionnez un document</option>';
            
            if (documents && documents.length > 0) {
                documents.forEach(function(doc) {
                    const selected = (doc.id == selectedDocumentId) ? 'selected' : '';
                    options += `<option value="${doc.id}" ${selected}>${doc.name}</option>`;
                });
            } else {
                options += '<option value="" disabled>Aucun document disponible</option>';
            }
            
            $documentSelect.html(options);
            
            if (selectedDocumentId && !documents.some(d => d.id == selectedDocumentId)) {
                console.warn("Document précédent non disponible dans ce projet");
            }
        }).fail(function() {
            $documentSelect.html('<option value="" disabled>Erreur de chargement</option>');
        });
    }

    // =============================================
    // MODAL D'ÉDITION - GESTION COMPLÈTE
    // =============================================
    $(document).on('click', '.edit-access-btn', function() {
        // Récupération de toutes les données
        const accessData = {
            id: $(this).data('id'),
            user_id: $(this).data('user_id'),
            user_name: $(this).data('user_name'),
            project_id: $(this).data('project_id'),
            project_name: $(this).data('project_name'),
            document_id: $(this).data('document_id'),
            document_name: $(this).data('document_name'),
            permission: $(this).data('access'),
            date_added: $(this).data('date_added')
        };

        // 1. Remplissage des champs cachés et visibles
        $('#editAccessModal #access-id-to-edit').val(accessData.id);
        $('#editAccessModal #userSelect').val(accessData.user_id);
        $('#editAccessModal #projectSelect').val(accessData.project_id);
        $('#editAccessModal #accessType').val(accessData.permission);

        // 2. Affichage des informations actuelles (lecture seule)
        $('#currentAccessDetails').html(`
            <div class="alert alert-info">
                <h6>Accès actuel</h6>
                <p><strong>Utilisateur:</strong> ${accessData.user_name || 'Non spécifié'}</p>
                <p><strong>Projet:</strong> ${accessData.project_name || 'Non spécifié'}</p>
                <p><strong>Document:</strong> ${accessData.document_name || 'Non spécifié'}</p>
                <p><strong>Permission:</strong> ${accessData.permission === 'read' ? 'Lecture' : 'Écriture'}</p>
                <p><strong>Créé le:</strong> ${accessData.date_added || 'Date inconnue'}</p>
            </div>
        `);

        // 3. Chargement des documents avec préservation de la sélection actuelle
        loadDocuments(accessData.project_id, accessData.document_id, '#editAccessModal #documentSelect')
            .then(() => {
                // 4. Ouverture du modal une fois tout chargé
                $('#editAccessModal').modal('show');
            })
            .catch(error => {
                console.error("Erreur lors du chargement:", error);
                $('#editAccessModal').modal('show');
            });
    });

    // Gestion du changement de projet dans le modal d'édition
    $('#editAccessModal #projectSelect').on('change', function() {
        const newProjectId = $(this).val();
        loadDocuments(newProjectId, null, '#editAccessModal #documentSelect');
    });

    // =============================================
    // MODAL D'AJOUT - GESTION
    // =============================================
    $('#givePermissionModal').on('show.bs.modal', function() {
        const projectId = $('#givePermissionModal #projectSelect').val();
        loadDocuments(projectId, null, '#givePermissionModal #documentSelect');
    });

    $('#givePermissionModal #projectSelect').on('change', function() {
        const projectId = $(this).val();
        loadDocuments(projectId, null, '#givePermissionModal #documentSelect');
    });
});


$(document).on('click', '.delete-document-btn', function() {
    var accessId = $(this).data('id');
    var documentName = $(this).data('name');
    
    // Mettre à jour le modal avec les informations du document à supprimer
    $('#document-name-to-delete').text(documentName);
    $('#access-id-to-delete').val(accessId);
});

</script>
@endsection
