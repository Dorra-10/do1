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
                                        <th>User With Acces</th>
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
                                        data-user_id="{{ $access->user_id }}"
                                        data-user_name="{{ $access->user_name }}"
                                        data-project_id="{{ $access->project_id }}"
                                        data-project_name="{{ $access->project_name }}"
                                        data-document_id="{{ $access->document_id }}"
                                        data-document_name="{{ $access->documentname }}"
                                        data-access="{{ $access->permission }}"
                                        data-date_added="{{ $access->created_at->format('d-m-Y H:i') }}"
                                        data-toggle="modal"
                                        data-target="#editAccessModal">
                                        <i class="fas fa-pencil-alt m-r-5"></i>
                                        </a>


                                        
                                            <a href="#" class="delete-access-btn" 
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
    <label for="userSelect">Users</label>
                        <div class="dropdown">
                        <button class="btn dropdown-toggle w-100" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="background-color: white; border: 1px solid #ccc; color: #333;">
                        Select Users
                    </button>
                    <ul class="dropdown-menu w-100" aria-labelledby="userDropdown" style="max-height: 140px; overflow-y: auto;">
                        @foreach($users as $user)
                            @if($user->roles->contains('name', 'employee'))
                                <li>
                                    <label class="dropdown-item">
                                        <input type="checkbox" class="user-checkbox" id="user-{{ $user->id }}" name="user_id[]" value="{{ $user->id }}">
                                        {{ $user->name }}
                                    </label>
                                </li>
                            @endif
                        @endforeach
                    </ul>

                        </div>
                    </div>

                    <!-- Project Select -->
                    <div class="form-group">
                        <label for="projectSelect">Project</label>
                        <div class="dropdown">
                            <button class="btn dropdown-toggle w-100" type="button" id="projectDropdown" data-bs-toggle="dropdown" aria-expanded="false"
                                style="background-color: white; border: 1px solid #ccc; color: #333;">
                                Select Project
                            </button>
                            <ul class="dropdown-menu w-100" aria-labelledby="projectDropdown" style="max-height: 140px; overflow-y: auto;">
                                @foreach($projects as $project)
                                    <li>
                                        <label class="dropdown-item">
                                            <input type="radio" name="project_id" value="{{ $project->id }}">
                                            {{ $project->name }}
                                        </label>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>


                    <!-- Document Select -->
                    <div class="form-group">
    <label for="documentSelect">Document</label>
    <select class="form-control" id="documentSelect" name="document_id" size="4" style="max-height: 80px; overflow-y: auto;">
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
                    <button type="submit" class="btn btn-primary">Add</button>
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
                    
                </button>
            </div>
            <form id="editAccessForm" method="POST" action="{{ route('access.update') }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" name="access_id" id="access-id-to-edit">
                   

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
                    <button type="submit" class="btn btn-primary">Save Changes</button>
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
                <p>Are you sure you want to revoke this Access ?</p>
            </div>
            <div class="modal-footer">
                <form  method="POST" action="{{ route('access.delete') }}">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="access_id" id="access-id-to-delete" value="{{ $access->id }}">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                           Revoke
                    </button>

                </form>
            </div>
        </div>
    </div>
</div>


<!-- Script to handle the dynamic document loading -->
<script>
$(document).ready(function() {
    // ==============================================
    // FONCTION COMMUNE DE CHARGEMENT DES DOCUMENTS
    // ==============================================
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

            const unlockedDocuments = documents.filter(doc => doc.is_locked === 0);

            if (unlockedDocuments.length > 0) {
                unlockedDocuments.forEach(function(doc) {
                    const selected = (doc.id == selectedDocumentId) ? 'selected' : '';
                    options += `<option value="${doc.id}" ${selected}>${doc.name}</option>`;
                });
            } else {
                options += '<option value="" disabled>Aucun document disponible</option>';
            }

            $documentSelect.html(options);

            if (selectedDocumentId && !unlockedDocuments.some(d => d.id == selectedDocumentId)) {
                console.warn("Document précédent non disponible ou verrouillé dans ce projet");
            }
        }).fail(function() {
            $documentSelect.html('<option value="" disabled>Erreur de chargement</option>');
        });
    }

    // ==============================================
    // MODAL D'AJOUT - GESTION
    // ==============================================
    $('#givePermissionModal').on('show.bs.modal', function() {
        const projectId = $('input[name="project_id"]:checked').val();
        loadDocuments(projectId, null, '#givePermissionModal #documentSelect');
    });

    $(document).on('change', 'input[name="project_id"]', function() {
        const projectId = $(this).val();
        loadDocuments(projectId, null, '#givePermissionModal #documentSelect');
    });

    // ==============================================
    // GESTION DU BOUTON "Give Permission and Add Another"
    // ==============================================
    $('#addAndStay').on('click', function(e) {
        e.preventDefault();

        const form = $('#givePermissionForm');
        const formData = form.serialize();

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: formData,
            success: function(response) {
                alert('Access granted to users successfully!');

                // Vider les champs sauf le projet sélectionné
                const selectedProjectId = $('input[name="project_id"]:checked').val();
                $('#givePermissionForm')[0].reset();

                // Reselect the project (if needed visually)
                if (selectedProjectId) {
                    $('input[name="project_id"][value="' + selectedProjectId + '"]').prop('checked', true);
                    loadDocuments(selectedProjectId, null, '#documentSelect');
                }
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    alert('Erreur : ' + xhr.responseJSON.message);
                } else {
                    alert('Une erreur s\'est produite.');
                }
            }
        });
    });
});









// =============================================
    // MODAL D'ÉDITION - GESTION COMPLÈTE
    // =============================================
   // Fonction de chargement des documents liée à un projet
function loadDocuments(projectId, selectedDocId, selectElement) {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: `/documents/by-project/${projectId}`, // <-- adapte l'URL si besoin
            method: 'GET',
            success: function(documents) {
                const $select = $(selectElement);
                $select.empty();

                // Ajout d'une option par défaut
                $select.append(`<option value="">-- Sélectionner un document --</option>`);

                documents.forEach(doc => {
                    const selected = doc.id === selectedDocId ? 'selected' : '';
                    $select.append(`<option value="${doc.id}" ${selected}>${doc.name}</option>`);
                });

                resolve();
            },
            error: function(xhr, status, error) {
                console.error('Erreur AJAX:', error);
                reject(error);
            }
        });
    });
}

// Gestion du clic sur le bouton "Modifier l'accès"
// Fonction de chargement des documents liée à un projet
$(document).on('click', '.edit-access-btn', function () {
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

    // Remplissage des champs cachés
    $('#editAccessModal #access-id-to-edit').val(accessData.id);
    $('#editAccessModal #userSelect').val(accessData.user_id);
    $('#editAccessModal #projectSelect').val(accessData.project_id);
    $('#editAccessModal #documentSelect').val(accessData.document_id);
    $('#editAccessModal #accessType').val(accessData.permission);

    // Affichage des informations en lecture seule
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

    // Affichage du modal (pas besoin de charger les documents)
    $('#editAccessModal').modal('hide');
});

// Plus besoin de gérer le changement de projet, donc on peut retirer ce bloc





    
$(document).on('click', '.delete-document-btn', function() {
    var accessId = $(this).data('id');
    $('#access-id-to-delete').val(accessId);
});




</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- JavaScript pour mettre à jour le texte du bouton avec les utilisateurs sélectionnés -->
<script>
    $(document).ready(function() {
        // Lorsque l'option dropdown est ouverte
        $('#userDropdown').on('click', function() {
            var selectedUsers = [];
            $('.user-checkbox:checked').each(function() {
                selectedUsers.push($(this).next('label').text()); // Récupérer le nom de l'utilisateur sélectionné
            });
            $('#userDropdown').text(selectedUsers.join(', ') || 'Select Users');
        });
    });
</script>
<style>
/* Griser les éléments cochés */
.dropdown-item:has(input:checked) {
    background-color: #e0e0e0 !important;
    color: #000 !important;
}
</style>
@endsection
