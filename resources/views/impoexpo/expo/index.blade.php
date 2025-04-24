@extends('layouts.app')

@section('content')

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
                                                    {{ Str::limit($export->description, 20) }}
                                                </span>

                                                @if(strlen($export->description) > 50)
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
                <h5 class="modal-title" id="deleteModalLabel">Confirmer la suppression</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        
            <div class="modal-body">
                <p>Voulez-vous vraiment supprimer ce document ?</p>
            </div>
        
            <div class="modal-footer">
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>

        </div>
    </div>
</div>

<!-- Script jQuery pour remplir dynamiquement l'action -->
<script>
    $('.delete-document-btn').click(function () {
        var docId = $(this).data('id');
        var actionUrl = "{{ url('/exports') }}/" + docId;
        $('#deleteForm').attr('action', actionUrl);
    });
</script>


@endsection