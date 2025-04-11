@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <div class="mt-5">
                        <h4 class="card-title float-left mt-2">History</h4>
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
                                        <th>Document Name</th>
                                        <th>Last View Date</th>
                                        <th>Last Modification Date</th>
                                        <th>Last Modification By</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <tbody>
                                    @foreach ($histories as $history)
                                        <tr>
                                            <td>{{ $history->document_name }}</td>
                                            <td>{{ $history->last_viewed_at }}</td>
                                            <td>{{ $history->last_modified_at }}</td>
                                            <td>{{ optional($history->lastModifier)->name ?? '—' }}</td>
                                            <td class="text-right">
                                            <a href="#" class="delete-history-btn" 
                                               data-id="{{ $history->id }}" 
                                               data-toggle="modal" 
                                               data-target="#delete_modal_{{ $history->id }}">
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
    </div>
</div>


@endsection
