@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <div class="mt-5">
                        <h4>History</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table">
                    <div class="card-body booking_card">
                        <div class="table-responsive">
                            <table id="history-table" class="table table-stripped table-hover table-center mb-0">
                                <thead>
                                    <tr>
                                        <th>Document</th>
                                        <th>Last Viewed</th>
                                        <th>Viewed By</th>
                                        <th>Last Modification</th>
                                        <th>Modified By</th>
                                        <th style="display:none;">Dernière activité</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($histories as $history)
                                    <tr>
                                        <td>{{ $history->document->name ?? 'Document supprimé' }}</td>
                                        <td>{{ $history->formatted_viewed }}</td>
                                        <td>{{ $history->viewer->name ?? '-' }}</td>
                                        <td>{{ $history->formatted_modified }}</td>
                                        <td>{{ $history->modifier->name ?? '-' }}</td>
                                        <td style="display:none;">{{ $history->formatted_latest }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- Pagination Links -->
                <div class="d-flex justify-content-center mt-3">
                    {{ $histories->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.3em 0.8em;
        margin-left: 2px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #0d6efd;
        color: white !important;
        border: 1px solid #0d6efd;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #e9ecef;
        border: 1px solid #dee2e6;
    }
    .pagination .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
</style>
@endsection

@section('scripts')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    $('#history-table').DataTable({
        paging: false, // Désactive la pagination de DataTables (nous utilisons la pagination Laravel)
        searching: true,
        info: false,
        ordering: false,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json',
            search: "Rechercher:",
            zeroRecords: "Aucun historique trouvé"
        },
        dom: '<"top"f>rt<"bottom"lip><"clear">',
        initComplete: function() {
            // Style personnalisé pour la recherche
            $('.dataTables_filter input').addClass('form-control');
            $('.dataTables_filter label').contents().filter(function() {
                return this.nodeType === 3;
            }).remove();
        }
    });
});
</script>
@endsection