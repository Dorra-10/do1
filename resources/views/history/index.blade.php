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
                            <table id="history-table" class="datatable table table-stripped table-hover table-center mb-0">
                                <thead>
                                    <tr>
                                        <th>Document</th>
                                        <th>Dernière consultation</th>
                                        <th>Consulté par</th>
                                        <th>Dernière modification</th>
                                        <th>Modifié par</th>
                                        <th style="display:none;">Date Tri</th>
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
                                        <td style="display:none;">{{ $history->latest_date ?? '' }}</td>
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
        order: [[5, 'desc']], // Colonne cachée pour le tri
        columnDefs: [{
            targets: 5,
            visible: false,
            render: function(data) {
                return new Date(data).getTime();
            }
        }],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
        }
    });
});
</script>
@endsection