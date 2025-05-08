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
    @if (session('error'))
    <div id="error-message" style="
        position: fixed;
        top: 20px;
        right: 20px;
        background-color:rgb(95, 87, 87); 
        color: white;
        padding: 15px 25px;
        border-radius: 5px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
        z-index: 9999;
    ">
        {{ session('error') }}
    </div>

    <script>
        setTimeout(function() {
            var message = document.getElementById('error-message');
            if (message) {
                message.style.display = 'none';
            }
        }, 2000);
    </script>
@endif
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header mt-5">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Profile</h3>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="profile-menu">
                    <ul class="nav nav-tabs nav-tabs-solid">
                        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#per_details_tab">About</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#password_tab">Password</a></li>
                    </ul>
                </div>
                <div class="tab-content profile-tab-cont">
                    <div class="tab-pane fade show active" id="per_details_tab">
                        <div class="row">
                            <div class="container d-flex justify-content-center align-items-center">
                                <div class="card" style="width: 60%;">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-center mb-4">
                                            <img src="{{ asset('assets/img/profil.png') }}" alt="Photo de profil"
                                                class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                                        </div>
                                        <h5 class="card-title d-flex justify-content-between">
                                            <span>Personal Details</span>
                                            <a class="edit-link" data-toggle="modal" href="#edit_personal_details">
                                                <i class="fa fa-edit mr-1"></i>Edit
                                            </a>
                                        </h5>
                                        <div class="container mt-5 ms-md-5">
                                            <div class="row mb-1 align-items-center">
                                                <div class="col-md-2 offset-md-1 text-md-end font-weight-bold">Name</div>
                                                <div class="col-md-8">{{ Auth::user()->name }}</div>
                                            </div>
                                            <div class="row mb-1 align-items-center">
                                                <div class="col-md-2 offset-md-1 text-md-end font-weight-bold">Email</div>
                                                <div class="col-md-8">{{ Auth::user()->email }}</div>
                                            </div>
                                            <div class="row mb-1 align-items-center">
                                                <div class="col-md-2 offset-md-1 text-md-end font-weight-bold">Phone Number</div>
                                                <div class="col-md-6">{{ Auth::user()->phone_number }}</div>
                                            </div>
                                            <div class="row mb-1 align-items-center">
                                                <div class="col-md-2 offset-md-1 text-md-end font-weight-bold">Role</div>
                                                <div class="col-md-8">
                                                    @if (Auth::user()->hasRole('admin'))
                                                        Admin
                                                    @elseif (Auth::user()->hasRole('superviseur'))
                                                        Superviseur
                                                    @elseif (Auth::user()->hasRole('employee'))
                                                        Employé
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="edit_personal_details" aria-hidden="true" role="dialog">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Personal Details</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('profile.update') }}" method="POST">
                                                @csrf
                                                <div class="row form-row">
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label>Name</label>
                                                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label>Email</label>
                                                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label>Phone Number</label>
                                                            <input type="text" name="phone_number" class="form-control" value="{{ old('phone_number', $user->phone_number) }}">
                                                        </div>
                                                    </div>
                                                </div>
                                                <button type=" he submit" class="btn btn-primary btn-block">Save Changes</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="password_tab" class="tab-pane fade">
    <div class="container d-flex justify-content-center align-items-center">
        <div class="card" style="width: 60%;">
            <div class="card-body">
                <h5 class="card-title text-center mb-4">Change Password</h5>

                <!-- Affichage des messages de succès -->
                @if (session('password_success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('password_success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Affichage des erreurs -->
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('password.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label>Old Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <div class="text-center">
                        <button class="btn btn-primary" type="submit">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .row {
            min-height: 2rem;
        }
        .fw-bold {
            padding-right: 1rem;
        }
    </style>
@endsection