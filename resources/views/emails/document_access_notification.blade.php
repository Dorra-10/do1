<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès Document</title>
</head>
<body>
    <h1>Bonjour {{ $user->name }},</h1>
    <p>Vous avez reçu un nouvel accès en @if($permission == 'read')  <strong>lecture</strong> @elseif($permission == 'write') <strong>écriture</strong>
    
    @endif au document : <strong>{{ $document->name }}</strong>.</p>


    <p>Accédez au document via votre tableau de bord.</p>
    
    <p>Bien cordialement,</p>
    
</body>
</html>
