<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	@if($success)
		<meta http-equiv="refresh" content="3;url={{$link}}">
	@endif
	<title>Transactions result Page</title>
	<link href="https://fonts.googleapis.com/css?family=Montserrat:200,400,700" rel="stylesheet">
	<link type="text/css" rel="stylesheet" href="{{asset('css/css/style.css')}}" />
</head>

<body>

	<div id="notfound">
		<div class="notfound">
			<div class="notfound-404">
                @if($success)
                    <h1 style="color:#66B999">Success!</h1>
                    <h2>succès de la transaction</h2>
                @else
                    <h1 style="color:#D6071B">Error!</h1>
                    <h2>erreur survenue</h2>
                @endif
			</div>
			<a href="{{$link}}">Go Back</a>
		</div>
	</div>

</body>

</html>