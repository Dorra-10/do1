@extends('layouts.app')

@section('content')
<table class="table table-hover text-nowrap">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Description</th>
            <th>Date création</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($projects as $project)
        <tr>
            <td>{{ $project->id }}</td>
            <td>{{ $project->name }}</td>
            <td>{{ Str::limit($project->description, 50) }}</td>
            <td>{{ $project->created_at->format('d/m/Y') }}</td>
            <td>
                <!-- Vos boutons d'action -->
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="text-center">Aucun projet trouvé</td>
        </tr>
        @endforelse
    </tbody>
</table>
@endsection