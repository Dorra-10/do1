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
                        <h4 class="card-title float-left mt-2">Exports</h4> 
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
                                        <th>Description</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($exports as $export)
                                        <tr>
                                            <td>{{ $export->id }}</td>
                                            <td>{{ $export->name }}.{{ $export->file_type }} {{ $export->updated_at->format('d/m/Y H:i') }}</td>
                                            <td>{{ $export->project_id }}</td>
                                            <td>{{ $export->owner }}</td>
                                            <td>{{ $export->company }}</td>
                                            <td>
                                                <span class="description-preview">
                                                    {{ Str::limit($export->description, 10) }}
                                                </span>

                                                @if(strlen($export->description) > 10)
                                                    <button class="btn btn-sm btn-link view-full-description" data-description="{{ $export->description }}">
                                                        See More
                                                    </button>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                <a href="{{ route('exports.download', $export->id) }}" class="download-document-btn">
                                                    <i class="fas fa-download m-r-5"></i>
                                                </a>
                                           
                                                <a href="#" class="delete-document-btn" data-id="{{ $export->id }}" data-toggle="modal" data-target="#delete_modal">
                                            <i class="fas fa-trash-alt m-r-5"></i>
                                        </a>
                                    </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No export documents found.</td>
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

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="delete_modal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm deletion</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        
            <div class="modal-body">
                <p>Are you sure you want to delete this Export ?</p>
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

<!-- Modal pour afficher la description complète -->

<div class="modal fade" id="descriptionModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document"> <!-- Plus grand pour les longues descriptions -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Document Description</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="fullDescriptionContent">
                <!-- Le contenu de la description sera injecté ici en JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script>
    $('.delete-document-btn').click(function () {
        var docId = $(this).data('id');
        var actionUrl = "{{ url('/exports') }}/" + docId;
        $('#deleteForm').attr('action', actionUrl);
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
</script>


@endsection