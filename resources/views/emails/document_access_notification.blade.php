<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Access</title>
</head>
<body>
    <h1>Good Morning {{ $user->name }},</h1>
    <p> You have received new access in @if($permission == 'read')  <strong>Read</strong> @elseif($permission == 'write') <strong>Write</strong>
    
    @endif to the document : <strong>{{ $document->name }}</strong>.</p>


    <p>Access the document through your dashboard.</p>
    
    <p>Kind regards,</p>
    
</body>
</html>
