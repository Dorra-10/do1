@extends('layouts.app')

@section('content')    
	<div class="page-wrapper">
		<div class="content container-fluid">
			<div class="page-header">
				<div class="row align-items-center">
					<div class="col">
						<h3 class="page-title mt-5">Edit User</h3>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-8">
					<form action="{{ url('users/'.$user->id) }}" method="POST">
						@csrf
                        @method('PUT')
						<div class="form-group">
							<label>User Name </label>
							<input class="form-control" type="text" name="name" value="{{ $user->name }}" >
						</div>
						<div class="form-group">
							<label>Email </label>
							<input class="form-control" type="text" name="email" readonly value="{{ $user->email }}" >
						</div>
						<div class="form-group">
							<label>Password </label>
							<input class="form-control" type="password" name="password" >
						</div>
						<div class="form-group">
							<label>Roles </label>
							<select name="roles[]" class="form-control" multiple>
								@foreach ($roles as $role)
									<option value="{{ $role }}"
                                        {{ in_array($role, $userRoles) ? 'selected':'' }}>{{ $role }}</option>
								@endforeach
							</select>
						</div>
						<div class="m-t-20">
							<button class="btn btn-primary submit-btn">Update User</button>
						</div>
					</form>
				</div>
			</div>
		</div>
</div>
@endsection