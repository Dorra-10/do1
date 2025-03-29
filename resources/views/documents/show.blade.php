<!DOCTYPE html>
<html>
<head>
   
    <style>
        iframe {
            width: 100%;
            height: 100vh;
            border: none;
        }
        .error-message {
            text-align: center;
            color: red;
            font-size: 18px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
@if(str_starts_with($document->file_type, 'image/'))
    <!-- Affichage de l'image -->
    <img src="{{ asset('storage/' . $document->path) }}" alt="{{ $document->name }}" style="width: 100%;"/>

@elseif($document->file_type == 'application/pdf')
    <!-- Affichage du fichier PDF -->
    <iframe src="{{ asset('storage/' . $document->path) }}" frameborder="0" style="width: 100%; min-height: 640px;"></iframe>

@elseif(in_array($document->file_type, [
    'application/msword', 
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.ms-powerpoint',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation'
]))
    <!-- Affichage du document (Word, Excel, PowerPoint) avec Office Online -->
    <iframe src="https://view.officeapps.live.com/op/view.aspx?src={{ urlencode(asset('storage/' . $document->path)) }}" frameborder="0" style="width: 100%; min-height: 640px;"></iframe>

@else
    <!-- Si le type de fichier n'est pas pris en charge -->
    <p>Le fichier ne peut pas être affiché dans le navigateur.</p>
@endif@elseif(in_array($document->file_type, [
    'application/msword', 
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.ms-powerpoint',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation'
]))
    <!-- Affichage du document (Word, Excel, PowerPoint) avec Office Online -->
    <iframe src="https://view.officeapps.live.com/op/view.aspx?src={{ urlencode(asset('storage/' . $document->path)) }}" frameborder="0" style="width: 100%; min-height: 640px;"></iframe>
@endif




</body>
</html>