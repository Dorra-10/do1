@extends('layouts.app')

@section('content')
	
	<div class="page-wrapper">
			<div class="content container-fluid">
				<div class="page-header">
					<div class="row align-items-center">
						<div class="col">
							<h3 class="page-title mt-5">Edit role</h3> </div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-8">
						<form  action="{{ url('roles/'.$role->id) }}"
                         method="POST">
                            @csrf
                            @method('PUT')

							<div class="form-group">
								<label>role Name <span class="text-danger">*</span></label>
								<input class="form-control" type="text" name="name" value="{{ $role->name }}"> </div>
							
							<div class="m-t-20">
								<button class="btn btn-primary submit-btn">update role</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>



</div>
@endsection