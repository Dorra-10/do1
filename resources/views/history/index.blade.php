@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <div class="mt-5">
                        <h4>Historique</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table">
                    <div class="card-body booking_card">
                        <div class="table-responsive">
                            <table id="history-table" class="datatable table table-stripped table-hover table-center mb-0">
                            <thead>
                                <tr>
                                    <th>Document</th>
                                    <th>Last Viewed</th>
                                    <th>Viewed By</th>
                                    <th>Last Modification</th>
                                    <th>Modified By</th>
                                    <th style="display:none;">Dernière activité</th> <!-- Cette colonne cachée est la base du tri -->
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
                                <td style="display:none;">{{ $history->formatted_latest }}</td> <!-- champ ISO pour tri correct -->
                            </tr>
                            @endforeach
                            </tbody>

                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')

<script>
$(document).ready(function() {
    $('#history-table').DataTable({
        order: [[5, 'desc']], // tri sur la colonne cachée "Dernière activité"
        columnDefs: [{
            targets: 5,
            visible: false,
            type: 'date'
        }],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
        }
    });
});
</script>
@endsection
