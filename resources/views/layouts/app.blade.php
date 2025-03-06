<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
	<title>HygecoDashboard </title>
	<link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.png">

	<!-- jQuery -->
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">

	<!-- FontAwesome CSS -->
	<link rel="stylesheet" href="{{asset('assets/plugins/fontawesome/css/fontawesome.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/all.min.css') }}">

	<!-- Feathericon CSS -->
	<link rel="stylesheet" href="{{ asset('assets/css/feathericon.min.css') }}">

	<!-- Morris CSS -->
	<!-- <link rel="stylesheet" href="{{ asset('assets/plugins/morris/morris.css') }}"> -->

	<!-- Custom CSS -->
	<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

	<!-- DataTables CSS -->
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
</head>

<body>
	<div class="main-wrapper">
		@include('layouts.header')
		@include('layouts.navbar')
		
		@yield('content')
	</div>

	<!-- Bootstrap JS -->
	<script src="{{ asset('assets/js/popper.min.js') }}"></script>
	<script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>

	<!-- Slimscroll JS -->
	<script src="{{ asset('assets/plugins/slimscroll/jquery.slimscroll.min.js') }}"></script>

	<!-- Morris JS -->
	<!-- <script src="{{ asset('assets/plugins/raphael/raphael.min.js') }}"></script>
	<script src="{{ asset('assets/plugins/morris/morris.min.js') }}"></script>
	<script src="{{ asset('assets/js/chart.morris.js') }}"></script> -->

	<!-- Custom JS -->

	<!-- DataTables JS -->
	<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>
	<script src="{{ asset('assets/js/script.js') }}"></script>

	<!-- Initialize DataTables -->
	<!-- <script>
		$(document).ready(function() {
			$('.datatable').DataTable({
				"bFilter": false,
			});
		});
	</script> -->
</body>
</html>