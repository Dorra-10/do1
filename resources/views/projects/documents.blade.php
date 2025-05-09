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
                                                    <a href="#"
                                                        class="edit-document-btn {{ $isLocked ? 'locked disabled-link' : '' }}"
                                                        data-id="{{ $document->id }}"
                                                        data-name="{{ $document->name }}"
                                                        data-project_id="{{ $document->project_id }}"
                                                        data-owner="{{ $document->owner }}"
                                                        data-company="{{ $document->company }}"
                                                        data-description="{{ $document->description }}"
                                                        data-date_added="{{ $document->date_added }}"
                                                        {{ $isLocked ? '' : 'data-toggle=modal data-target=#editDocumentModal' }}
                                                        onclick="{{ $isLocked ? 'return false;' : '' }}"
                                                        style="{{ $isLocked ? 'pointer-events: none; opacity: 0.5;' : '' }}"
                                                        title="{{ $isLocked ? 'Document verrouillé' : 'Edit' }}">
                                                        <i class="fas fa-pencil-alt"></i>
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
                                                @if ($user->hasRole('admin') || $user->hasRole('superviseur') || $hasWriteAccess || $hasReadAccess)
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
                                                @if ($user->hasRole('admin') || $user->hasRole('superviseur') || $hasWriteAccess)
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
                                                    class="lock-document-btn {{ $document->is_locked ? 'locked' : '' }}" 
                                                    title="{{ $document->is_locked ? 'Déjà verrouillé' : 'Lock this document' }}"
                                                    data-locked="{{ $document->is_locked ? 'true' : 'false' }}"
                                                    onclick="{{ !$document->is_locked ? 'openLockModal('.$document->id.')' : 'return false;' }}"
                                                    id="lock-icon-{{ $document->id }}">
                                                    <i class="fas {{ $document->is_locked ? 'fa-lock' : 'fa-unlock' }} m-r-5"></i>
                                                    </a>

                                                @endif


                                                <!-- Formulaire caché -->
                                                <form id="lock-form-{{ $document->id }}" 
                                                    action="{{ route('documents.lock', $document->id) }}" 
                                                    method="POST" 
                                                    style="display: none;">
                                                    @csrf
                                                </form>

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

            <form id="editDocumentForm" method="POST" action="{{ route('projects.documents.update', [
          'projectId' => $document->project_id,
          'document' => $document->id
      ]) }}">
      @csrf
                @method('PUT')
                <div class="modal-body">
                    <!-- Document Name -->
                    <div class="form-group">
                        <label for="editName">Document Name</label>
                        <input type="text" class="form-control" id="editName" name="name" required>
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
<div class="modal fade" id="lockModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirmer le verrouillage</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Êtes-vous sûr de vouloir verrouiller ce document ?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Non</button>
        <button type="button" class="btn btn-primary" id="confirmLockBtn">Oui</button>
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
           
            const dateAdded = this.dataset.dateAdded || this.dataset.date_added;
            const owner = this.dataset.owner || '';
            const company = this.dataset.company || '';
            const description = this.dataset.description || '';

            // Remplir les champs
            document.getElementById('editName').value = name;
            document.getElementById('editOwner').value = owner;
            document.getElementById('editCompany').value = company;
            document.getElementById('editDescription').value = description; // Pour textarea, on utilise .value

        

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


//icone
// lock.js - Version finale testée
document.addEventListener('DOMContentLoaded', function() {
    // Stockage global de l'ID
    window.currentLockDocId = null;

    // Ouverture modale
    window.openLockModal = function(docId) {
        window.currentLockDocId = docId;
        $('#lockModal').modal('show');
    };

    // Gestionnaire de clic amélioré
    document.getElementById('confirmLockBtn').addEventListener('click', async function() {
        if (!window.currentLockDocId) {
            alert('Aucun document sélectionné');
            return;
        }

        try {
            // Méthode 1 : Récupération via meta tag
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            // Méthode 2 : Fallback direct pour Laravel
            if (!csrfToken && window.Laravel?.csrfToken) {
                csrfToken = window.Laravel.csrfToken;
            }

            // Méthode 3 : Récupération depuis les cookies
            if (!csrfToken) {
                const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
                csrfToken = match ? decodeURIComponent(match[1]) : null;
            }

            if (!csrfToken) {
                throw new Error('Impossible de récupérer le token CSRF');
            }

            // Envoi avec triple protection
            const response = await fetch(`/documents/${window.currentLockDocId}/lock`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-XSRF-TOKEN': csrfToken, // Pour les cookies encryptés
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    '_token': csrfToken,
                    'document_id': window.currentLockDocId
                })
            });

            if (response.status === 419) {
                const error = await response.json();
                throw new Error(error.message || 'Session expirée - Veuillez recharger');
            }

            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }

            const data = await response.json();
            
            // Mise à jour UI
            if (data.success) {
                const lockIcon = document.getElementById(`lock-icon-${window.currentLockDocId}`);
                if (lockIcon) {
                    lockIcon.innerHTML = '<i class="fas fa-lock m-r-5" style="color:#00796B"></i>';
                    lockIcon.onclick = null;
                }
                $('#lockModal').modal('hide');
                alert('Document verrouillé avec succès');
            }
        } catch (error) {
            console.error('Échec critique:', error);
            alert(`ERREUR: ${error.message}`);
            window.location.reload(); // Recharge en cas d'erreur CSRF
        }
    });
});
</script>
<style>
    .lock-document-btn i {
        color: grey;
        cursor: pointer;
        transition: color 0.3s ease;
    }

    .lock-document-btn.locked i {
        color: #03A9F4;
        cursor: default;
    }

    /* Optionnel : popup */
    .custom-modal {
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
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
    .edit-document-btn.locked i,
/* Dans votre fichier CSS principal */
.disabled-action {
    opacity: 0.6;
    cursor: not-allowed !important;
}

.locked {
    cursor: default !important;
}

[data-locked="true"] {
    pointer-events: none;
}

/* Couleurs personnalisées */
:root {
    --locked-color: #00796B;
    --disabled-color: #6c757d;
}

</style>
@endsection