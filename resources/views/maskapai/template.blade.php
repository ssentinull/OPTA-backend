<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $_ENV["APP_NAME"] }}</title>

    <!-- MDL -->
    <link rel="stylesheet" href="{{ asset('mdl/material.css') }}">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('custom-admin.css') }}">
    
    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:regular,bold,italic,thin,light,bolditalic,black,medium&amp;lang=en">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.cyan-light_blue.min.css">

</head>
<body>

    <div class="demo-layout mdl-layout mdl-js-layout mdl-layout--fixed-drawer mdl-layout--fixed-header">
        @include("maskapai.header")
        @include("maskapai.sidebar")
        @yield("main")
    </div>

    <!-- MDL -->
    <script src="{{ asset('mdl/material.js') }}"></script>
</body>
</html>