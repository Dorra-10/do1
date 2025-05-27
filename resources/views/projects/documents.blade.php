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
                                        <th>Project ID</th>
                                        <th>Owner</th>
                                        <th>Company</th>
                                        <th>Date</th>
                                        <th>Description</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($documents as $document)
                                        <tr>
                                            <td>{{ $document->id }}</td>
                                            <td>{{ $document->name }}</td>
                                            <td>{{ $document->project_id }}</td>
                                            <td>{{ $document->owner }}</td>
                                            <td>{{ $document->company }}</td>
                                            <td>{{ $document->updated_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                            <span class="description-preview">
                                                    {{ Str::limit($document->description, 10) }} <!-- Limite à 50 caractères -->
                                                </span>
                                                
                                                @if(strlen($document->description) > 10)
                                                <button class="btn btn-sm btn-link view-full-description" 
                                                        data-description="{{ $document->description }}">
                                                    See More
                                                </button>
                                                @endif
                                            </td>
                                            <td class="text-right">
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

                                                    $isLocked = $document->is_locked;
                                                @endphp


                                                {{-- Bouton de modification --}}
                                                @can('update document')
                                                    <a href="#" class="edit-document-btn" 
                                                    data-id="{{ $document->id }}"
                                                    data-name="{{ $document->name }}"
                                                    data-project_id="{{ $document->project_id }}"
                                                    data-owner="{{ $document->owner }}"
                                                    data-company="{{ $document->company }}"
                                                    data-description="{{ $document->description ?? '' }}"
                                                    data-date_added="{{ $document->date_added ? $document->date_added->format('Y-m-d') : '' }}"
                                                    data-toggle="modal" 
                                                    data-target="#editDocumentModal">
                                                        <i class="fas fa-pencil-alt m-r-5"></i>
                                                    </a>
                                                @endcan
                                        
                                                {{-- Bouton de suppression --}}
                                                @can('delete document')
                                                    <a href="#" 
                                                    class="delete-document-btn {{ $isLocked ? 'locked disabled-link' : '' }}" 
                                                    onclick="{{ $isLocked ? 'return false;' : 'event.preventDefault(); openDeleteModal(' . $document->id . ');' }}" 
                                                    style="{{ $isLocked ? 'pointer-events: none; opacity: 0.5;' : '' }}" 
                                                    title="{{ $isLocked ? 'Document verrouillé' : 'Delete' }}">
                                                        <i class="fas fa-trash m-r-5"></i>
                                                    </a>
                                                @endcan

                                                {{-- Icône de téléchargement --}}
                                                @if ($user->hasRole('admin') || $user->hasRole('supervisor') || $hasWriteAccess || $hasReadAccess)
                                                    <a 
                                                        href="{{ $isLocked ? '#' : route('documents.download', $document->id) }}" 
                                                        class="download-document-btn {{ $isLocked ? 'locked disabled-link' : '' }}"
                                                        style="{{ $isLocked ? 'pointer-events: none; opacity: 0.5;' : '' }}"
                                                        title="{{ $isLocked ? 'Document verrouillé' : 'Download' }}"
                                                    >
                                                        <i class="fas fa-download m-r-5"></i>
                                                    </a>
                                                @endif

                                                {{-- Icône de révision --}}
                                                @if ($user->hasRole('admin') || $user->hasRole('supervisor') || $hasWriteAccess)
                                                    <a href="{{ $isLocked ? '#' : route('documents.revision', $document->id) }}"
                                                    class="revision-document-btn {{ $isLocked ? 'locked disabled-link' : '' }}"
                                                    style="{{ $isLocked ? 'pointer-events: none; opacity: 0.5;' : '' }}" 
                                                    title="{{ $isLocked ? 'Document verrouillé' : 'Upload' }}"
                                                    data-id="{{ $document->id }}" data-toggle="modal" data-target="#revisionModal">
                                                        <i class="fas fa-edit m-r-5"></i> 
                                                    </a>
                                                @endif
                                                  <!-- Icône de verrouillage -->
                                                  @if ($user->hasRole('admin'))
                                            <a href="#"
                                            class="export-document-btn {{ $document->is_exported ? 'exported' : '' }}"
                                            title="{{ $document->is_exported ? 'Document Exported - Click to re-export' : 'Export this document' }}"
                                            onclick="openExportModal({{ $document->id }})"
                                            id="export-icon-{{ $document->id }}">
                                                <i class="fas fa-file-export m-r-5"></i>
                                            </a>
                                        @endif
                                               

                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">
                                                @if (!empty($searchTerm))
                                                    <div class="alert alert-info">
                                                        <i class="fas fa-search"></i> No documents found for "{{ $searchTerm }}"
                                                    </div>
                                                    <a href="{{ route('documents.index') }}" class="btn btn-sm btn-outline-primary">
                                                        Show all documents
                                                    </a>
                                                @else
                                                    <div class="alert alert-info ">
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
                <!-- Form for deletion -->
                <form id="deleteForm" method="POST" action="" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                    <button type="submit" class="btn btn-danger">Yes</button>
                </form>
            </div>
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
            <form id="editDocumentForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <!-- Hidden Project ID -->
                    <input type="hidden" name="project_id" id="editProjectId">
                    <!-- Document Name -->
                    <div class="form-group">
                        <label for="editName">Document Name</label>
                        <input type="text" class="form-control" id="editName" name="name" required>
                        @error('name')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <!-- Owner -->
                    <div class="form-group">
                        <label for="editOwner">Owner</label>
                        <input type="text" class="form-control" id="editOwner" name="owner" required>
                        @error('owner')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <!-- Company -->
                    <div class="form-group">
                        <label for="editCompany">Company</label>
                        <input type="text" class="form-control" id="editCompany" name="company" required>
                        @error('company')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <!-- Description -->
                    <div class="form-group">
                        <label for="editDescription">Description</label>
                        <textarea id="editDescription" name="description" rows="4" class="form-control"></textarea>
                        @error('description')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <!-- Date Added -->
                    <div class="form-group">
                        <label for="editDate">Date Added</label>
                        <input type="date" class="form-control" id="editDate" name="date_added" required>
                        @error('date_added')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
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


<!-- Export confirmation -->
<div class="modal fade" id="exportModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirm Export</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Are you sure you want to export this document?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success" id="confirmExportBtn">Export</button>
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
                <form action="" method="POST" enctype="multipart/form-data" id="revisionForm">
                    @csrf
                    <div class="form-group">
                        <label for="file">Choose file</label>
                        <input type="file" name="file" id="file" class="form-control" required accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.catpart,.catproduct,.cgr,.stl,.igs,.iges,.stp,.step">
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
         function openDeleteModal(documentId) {
    // Ouvre le modal
    $('#delete_modal').modal('show');

    // Met à jour l'action du formulaire avec l'ID du document à supprimer
    var formAction = '{{ route('documents.destroy', ':id') }}';
    formAction = formAction.replace(':id', documentId);
    $('#deleteForm').attr('action', formAction);
}
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
            showAlert(response.success, 'success');
            $('#revisionModal').modal('hide');
            setTimeout(() => location.reload(), 1500);
        },
        error: function(xhr) {
            let message = xhr.responseJSON?.error || "Erreur inconnue.";
            showAlert(message, 'error');
        }
    });
});

// ✅ Fonction pour afficher un message dynamique
function showAlert(message, type = 'success') {
    const color = type === 'success' ? 'rgb(68, 97, 89)' : 'rgb(95, 87, 87)';
    const alertDiv = document.createElement('div');
    alertDiv.id = 'alert-message';
    alertDiv.style.position = 'fixed';
    alertDiv.style.top = '20px';
    alertDiv.style.right = '20px';
    alertDiv.style.backgroundColor = color;
    alertDiv.style.color = 'white';
    alertDiv.style.padding = '15px 25px';
    alertDiv.style.borderRadius = '5px';
    alertDiv.style.boxShadow = '0 2px 10px rgba(0,0,0,0.2)';
    alertDiv.style.zIndex = 9999;
    alertDiv.innerText = message;

    document.body.appendChild(alertDiv);

    setTimeout(() => {
        if (alertDiv) alertDiv.remove();
    }, 3000);
}



//icone
// lock.js - Version finale testée
document.addEventListener('DOMContentLoaded', function () {
    window.currentExportDocId = null;

    // Fonction pour ouvrir la modale d'export
    window.openExportModal = function (docId) {
        window.currentExportDocId = docId;
        $('#exportModal').modal('show');
    };

    // Lors du clic sur "Exporter"
    document.getElementById('confirmExportBtn').addEventListener('click', async function () {
        if (!window.currentExportDocId) {
            alert('Aucun document sélectionné pour l\'exportation.');
            return;
        }

        try {
            // Récupération CSRF token
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!csrfToken && window.Laravel?.csrfToken) csrfToken = window.Laravel.csrfToken;
            if (!csrfToken) {
                const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
                csrfToken = match ? decodeURIComponent(match[1]) : null;
            }
            if (!csrfToken) throw new Error('Impossible de récupérer le token CSRF.');

            // Envoi POST
            const response = await fetch(`/documents/${window.currentExportDocId}/export`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-XSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    '_token': csrfToken,
                    'document_id': window.currentExportDocId
                })
            });

            const data = await response.json();

            if (response.status === 419) {
                throw new Error(data.message || 'Session expirée - Veuillez rafraîchir la page.');
            }

            if (!response.ok) {
                // Afficher le message d'erreur spécifique du backend
                throw new Error(data.message || `Erreur HTTP : ${response.status}`);
            }

            if (data.success) {
                // Mise à jour de l’icône courante
                const exportBtn = document.getElementById(`export-icon-${window.currentExportDocId}`);
                if (exportBtn) {
                    exportBtn.classList.add('exported');
                    exportBtn.setAttribute('title', 'Document exporté - Cliquez pour réexporter');
                }
                $('#exportModal').modal('hide');

                // Message flottant de succès
                const msg = document.createElement('div');
                msg.textContent = data.message || 'Document exporté avec succès.';
                msg.style = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background-color: #56766b;
                    color: white;
                    padding: 12px 20px;
                    border-radius: 5px;
                    z-index: 10000;
                `;
                document.body.appendChild(msg);
                setTimeout(() => msg.remove(), 2500);
            }

        } catch (error) {
            console.error('Échec de l\'exportation :', error);
            // Afficher le message d'erreur spécifique
            const errorMsg = document.createElement('div');
            errorMsg.textContent = error.message;
            errorMsg.style = `
                position: fixed;
                top: 20px;
                right: 20px;
                background-color: rgb(95, 87, 87);
                color: white;
                padding: 12px 20px;
                border-radius: 5px;
                z-index: 10000;
            `;
            document.body.appendChild(errorMsg);
            setTimeout(() => errorMsg.remove(), 2500);

        }
    });
});

</script>
<style>/* Style pour l'icône d'export */
/* Style pour l'icône d'export */
.export-document-btn i {
    color: var(--default-color); /* gris par défaut */
    cursor: pointer;
    transition: color 0.3s ease;
}

/* Une fois exporté, l'icône devient bleue et reste bleue */
.export-document-btn.exported i {
    color: var(--exported-color); /* bleu */
    cursor: pointer; /* reste cliquable */
}

/* Optionnel : styles désactivés généraux */
.disabled-action {
    opacity: 0.6;
    cursor: not-allowed !important;
}

/* Couleurs personnalisées */
:root {
    --exported-color: #03A9F4;
    --default-color: grey;
}

/* Modal perso */
.custom-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.custom-modal-content {
    background: white;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
}

.custom-modal-content button {
    margin: 10px;
}
/* Pagination style matching history */
.pagination {
    justify-content: center;
    margin-top: 20px;
}

.pagination .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.pagination .page-link {
    color: #0d6efd;
    margin: 0 5px;
    border-radius: 4px;
}

.showing-results {
    color: #6c757d;
    font-size: 0.9rem;
}

</style>
@endsection