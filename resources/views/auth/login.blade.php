<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>EDMS</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ url('assets/img/logo3.png') }}">
    <link rel="stylesheet" href="{{ url('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ url('assets/plugins/fontawesome/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ url('assets/plugins/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ url('assets/css/feathericon.min.css') }}">
    <link rel="stylesheet" href="https://cdn.oesmith.co.uk/morris-0.5.1.css">
    <link rel="stylesheet" href="{{ url('assets/plugins/morris/morris.css') }}">
    <link rel="stylesheet" href="{{ url('assets/css/style.css') }}">
</head>
<body>
<div class="main-wrapper login-body">
    <div class="login-wrapper">
        <div class="container">
            <div class="loginbox">
                <div class="login-left">
                    <img class="img-fluid" src="{{ url('assets/img/logo1.png') }}" alt="Logo">
                </div>
                <div class="login-right">
                    <div class="login-right-wrap">
                        <h1>Login</h1>
                        <p class="account-subtitle">Access your EDMS dashboard</p>

                        {{-- Affichage du message de succès --}}
                        @if (session('success'))
                            <div id="success-message" style="position: fixed; top: 20px; right: 20px; background-color:rgb(86, 109, 103); color: white; padding: 15px 25px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.2); z-index: 9999; display: flex; align-items: center;">
                                <i class="fa fa-check-circle mr-2"></i>
                                {{ session('success') }}
                            </div>
                            <script>
                                setTimeout(() => {
                                    const msg = document.getElementById('success-message');
                                    if (msg) msg.style.display = 'none';
                                }, 5000);
                            </script>
                        @endif

                        {{-- Affichage du message d'erreur --}}
                        @if (session('error'))
                            <div id="error-message" style="position: fixed; top: 20px; right: 20px; background-color: rgb(95, 87, 87); color: white; padding: 15px 25px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.2); z-index: 9999; display: flex; align-items: center;">
                                <i class="fa fa-exclamation-circle mr-2"></i>
                                {{ session('error') }}
                            </div>
                            <script>
                                setTimeout(() => {
                                    const msg = document.getElementById('error-message');
                                    if (msg) msg.style.display = 'none';
                                }, 5000);
                            </script>
                        @endif

                        {{-- Affichage des erreurs de validation --}}
                        @if ($errors->any())
                            <div id="error-messages" style="position: fixed; top: 20px; right: 20px; background-color: rgb(95, 87, 87); color: white; padding: 15px 25px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.2); z-index: 9999; display: flex; align-items: center;">
                                <i class="fa fa-exclamation-circle mr-2"></i>
                                <div>
                                    <ul style="margin: 0; padding: 0 0 0 20px;">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <script>
                                setTimeout(() => {
                                    const msg = document.getElementById('error-messages');
                                    if (msg) msg.style.display = 'none';
                                }, 5000);
                            </script>
                        @endif

                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="form-group">
                                <input class="form-control" type="email" name="email" placeholder="Email" required>
                            </div>
                            <div class="form-group">
                                <input class="form-control" id="password" type="password" name="password" placeholder="Password" required autocomplete="current-password">
                            </div>
                            <div class="form-group">
                                <label for="remember_me" class="inline-flex items-center">
                                    <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                                    <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                                </label>
                            </div>
                            <div class="form-group">
                                <button class="btn btn-primary btn-block" type="submit">Login</button>
                            </div>
                        </form>

                        <div class="text-center forgotpass">
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}">Forgot Password?</a>
                            @endif
                        </div>
                        <div class="text-center dont-have">Don’t have an account? <a href="{{ route('register') }}">Register</a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ url('assets/js/jquery-3.5.1.min.js') }}"></script>
<script src="{{ url('assets/js/popper.min.js') }}"></script>
<script src="{{ url('assets/js/bootstrap.min.js') }}"></script>
<script src="{{ url('assets/plugins/slimscroll/jquery.slimscroll.min.js') }}"></script>
<script src="{{ url('assets/plugins/raphael/raphael.min.js') }}"></script>
<script src="{{ url('assets/plugins/morris/morris.min.js') }}"></script>
<script src="{{ url('assets/js/script.js') }}"></script>
</body>
</html>