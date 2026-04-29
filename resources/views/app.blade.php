<!DOCTYPE html>
<html>
<head>
   @production
       @if(file_exists(public_path('build/manifest.json')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    @else
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endproduction
</head>
<body>
    <div id="app"></div>
</body>
</html>