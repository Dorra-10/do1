@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <div class="mt-5">
                        <h4 class="card-title float-left mt-2">Users</h4>
                        @can('create user')
                        <a href="{{ url('users/create') }}" class="btn btn-primary float-right veiwbutton">Add user</a>
                        @endcan
                    </div>
                </div>
            </div>
            @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
            @endif
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table">
                    <div class="card-body booking_card">
                        <div class="table-responsive">
                            <table class="datatable table table-stripped table-hover table-center mb-0">
                                <thead>
                                    <tr>
                                        <th>User ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Roles</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $user)
                                    <tr>
                                        <td>{{ $user->id }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                         <td class="text-left">
                                            @if (!empty($user->getRoleNames()))
                                                @foreach ($user->getRoleNames() as $rolename)
                                                    <span class="badge badge-pill bg-success inv-badge">{{ $rolename }}</span>
                                                @endforeach
                                            @endif
                                        </td>
                                        <td class="text-right" >
                                          @can('update user')  
                                            <a  href="{{ url('users/'.$user->id.'/edit') }}">
                                                <i class="fas fa-pencil-alt m-r-5"></i> 
                                            </a>
                                          @endcan
                                          @can('delete user')  
                                            <a  href="{{ url('users/'.$user->id.'/delete') }}" >
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
    <div id="delete_asset" class="modal fade delete-modal" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <img src="" alt="" width="50" height="46">
                    <h3 class="delete_class">Are you sure want to delete this User?</h3>
                    <div class="m-t-20">
                        <a href="#" class="btn btn-white" data-dismiss="modal">Close</a>
                        <a href="{{ url('users/'.$user->id.'/delete') }}">
                        <button type="submit" class="btn btn-danger">Delete</button>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


