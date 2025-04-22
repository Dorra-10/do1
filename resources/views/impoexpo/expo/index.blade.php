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




@endsection