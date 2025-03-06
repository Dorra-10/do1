@extends('layouts.app')

@section('content')
	<div class="page-wrapper">
					<div class="content container-fluid">
						<div class="page-header">
							<div class="row align-items-center">
								<div class="col">
									<h3 class="page-title mt-5">Add Permission</h3> </div>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-8">
								<form  action="{{ url('permissions') }}" method="POST">
									@csrf
									<div class="form-group">
										<label>Permission Name <span class="text-danger">*</span></label>
										<input class="form-control" type="text" name="name"> </div>
									
									<div class="m-t-20">
										<button class="btn btn-primary submit-btn">Create Permission</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>



	</div>
@endsection	

