@extends('layouts.app')

@section('content')
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
	 <div class="page-wrapper">
		<div class="content container-fluid">
			<div class="page-header">
				<div class="row align-items-center">
					<div class="col">
						<h3 class="page-title mt-5">Add User</h3>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-8">
					<form action="{{ url('users') }}" method="POST">
						@csrf
						<div class="form-group">
							<label>User Name <span class="text-danger">*</span></label>
							<input class="form-control" type="text" name="name" required>
						</div>
						<div class="form-group">
							<label>Email <span class="text-danger">*</span></label>
							<input class="form-control" type="email" name="email" required>
						</div>
						<div class="form-group">
							<label>Phone Number<span class="text-danger">*</span></label>
							<input class="form-control" type="text" name="phone_number" required>
						</div>
						<div class="form-group">
							<label>Password <span class="text-danger">*</span></label>
							<input class="form-control" type="password" name="password" required>
						</div>
						<div class="form-group">
							<label>Roles <span class="text-danger">*</span></label>
								<select name="roles[]" class="form-control" id="roles-select" required>
									<option value="" disabled selected>Select a role</option>
									@foreach ($roles as $role)
										<option value="{{ $role }}">{{ $role }}</option>
									@endforeach
							    </select>
						</div>

						<div class="m-t-20">
							<button class="btn btn-primary submit-btn">Create User</button>
						</div>
					</form>
				</div>
			</div>
		</div>
</div>
@endsection