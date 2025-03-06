
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
	<title>WTI Dashboard Template</title>
	<link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.png">
	<link rel="stylesheet" href="{{url('assets/css/bootstrap.min.css')}}">
	<link rel="stylesheet" href="{{url('assets/plugins/fontawesome/css/fontawesome.min.css')}}">
	<link rel="stylesheet" href="{{url('assets/plugins/fontawesome/css/all.min.css')}}">
	<link rel="stylesheet" href="{{url('assets/css/feathericon.min.css')}}">
	<link rel="stylehseet" href="https://cdn.oesmith.co.uk/morris-0.5.1.css">
	<link rel="stylesheet" href="{{url('assets/plugins/morris/morris.css')}}">
	<link rel="stylesheet" href="{{url('assets/css/style.css')}}"> </head>

<body>
<div class="main-wrapper login-body">
	<div class="login-wrapper">
		<div class="container">
			<div class="loginbox">
				<div class="login-left"> <img class="img-fluid" src="assets/img/logo.png" alt="Logo"> </br><img class="img-fluid" src="assets/img/logo.png" alt="Logo"> </div>
				<div class="login-right">
					<div class="login-right-wrap">
						<h1>Login</h1>
						<p class="account-subtitle">Access to our dashboard</p>
						@if ($errors->any())
							<div class="alert alert-danger">
								<ul>
									@foreach ($errors->all() as $error)
										<li>{{ $error }}</li>
									@endforeach
								</ul>
							</div>
						@endif
						<form method="POST" action="{{ route('login') }}">
                            @csrf

							<div class="form-group">
								<input class="form-control" type="email" name="email" :value="__('Email')" required  placeholder="Email">
							</div>
							<div class="form-group">
								<input class="form-control" id="password" type="password"  :value="__('Password')" name="password" required autocomplete="current-password" placeholder="Password">
							</div>
							<div class="block mt-4">
								<label for="remember_me" class="inline-flex items-center">
									<input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
									<span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
								</label>
							</div>
							<div class="form-group">
								<button class="btn btn-primary btn-block" type="submit">Login</button>
							</div>
						
						<div class="text-center forgotpass">
							@if (Route::has('password.request'))
								<a href="{{ route('password.request') }}">Forgot Password?</a>
							@endif
						</div>
                        </form>
						<div class="text-center dont-have">Don’t have an account? <a href="{{ route('register') }}">Register</a></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
	<script src="{{url('assets/js/jquery-3.5.1.min.j')}}s"></script>
	<script src="{{url('assets/js/popper.min.js')}}"></script>
	<script src="{{url('assets/js/bootstrap.min.js')}}"></script>
	<script src="{{url('assets/plugins/slimscroll/jquery.slimscroll.min.js')}}"></script>
	<script src="{{url('assets/plugins/raphael/raphael.min.js')}}"></script>
	<script src="{{url('assets/plugins/morris/morris.min.js')}}"></script>
	<script src="{{url('assets/js/script.js')}}"></script>
</body>

</html>