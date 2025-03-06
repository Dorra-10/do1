@extends('layouts.app')

@section('content')    
	<div class="page-wrapper">
			<div class="content container-fluid">
				    
				<div class="page-header">
					 f
					<div class="row align-items-center">
						<div class="col">
							<h3 class="page-title mt-5">Role : {{ $role->name}}</h3> </div>
					</div>
					     @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                		  @endif
				</div>
				<div class="row">
					<div class="col-lg-8">
					 <form action="{{ url('roles/'.$role->id.'/give-permissions') }}" method="POST">
                            @csrf
                            @method('PUT')

                       <div class="table-responsive">
							@error('permission')
							<span class="text-danger">{{ $message }}</span>
							@enderror
							<table class="table table-striped custom-table">
								<thead>
									<tr>
										<th>Module Permission</th>
										@foreach ($permissions as $permission)
										<th class="text-center">{{ $permission->name }}</th>
										@endforeach
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>Role : {{ $role->name}}</td>
										@foreach ($permissions as $permission)
										<td class="text-center">
											<input type="checkbox" name="permission[]" value="{{ $permission->name }}" {{
												in_array($permission->id, $rolePermissions) ? 'checked':'' }}
											>
										</td>
										@endforeach

									</tr>


								</tbody>
							</table>
						</div>
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </form>
					</div>
				</div>
			</div>
		</div>
@endsection


</div>