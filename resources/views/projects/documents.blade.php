@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <div class="mt-5">
                        <h4 class="card-title float-left mt-2">Documents pour le projet</h4> 
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table">
                    <div class="card-body booking_card">
                        <div class="table-responsive">
                            <table class="datatable table table-stripped table table-hover table-center mb-0">
                                <thead>
                                    <tr>
                                        <th> ID</th>
                                        <th>Nom</th>
                                        <th>Type</th>
                                        <th>Accès</th>
                                        <th>Date</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($documents as $document)
                                <tr>
                                    <td>{{ $document->id }}</td>
                                    <td>{{ $document->name }}</td>
                                    <td>{{ $document->type }}</td>
                                    <td>{{ $document->acces }}</td>
                                    <td>{{ $document->date_added }}</td>
                                    <td class="text-right">
                                        @can('update project')
                                            <a href="#" class="edit-document-btn" data-id="{{ $document->id }}" data-name="{{ $document->name }}" data-type="{{ $document->type }}" data-project_id="{{ $document->project_id }}" data-acces="{{ $document->acces }}" data-date_added="{{ $document->date_added }}" data-toggle="modal" data-target="#editDocumentModal">
                                                <i class="fas fa-pencil-alt m-r-5"></i>
                                            </a>
                                        @endcan
                                        @can('delete project')
                                            <a href="#" class="delete-document-btn" data-id="{{ $document->id }}" data-toggle="modal" data-target="#delete_modal">
                                                <i class="fas fa-trash-alt m-r-5"></i>
                                            </a>
                                        @endcan
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
</div>
@endsection
