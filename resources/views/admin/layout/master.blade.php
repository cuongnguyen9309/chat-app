<!doctype html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    @vite(['resources/js/app.js'])
    <script src="{{asset('js/jquery-3.7.0.min.js')}}"></script>
    <script src="https://kit.fontawesome.com/c2fe055d35.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="{{asset('css/main.css')}}">
    <title>Document</title>
</head>
<body class="flex h-full">
<x-admin.layout.sidebar/>
<div class="right-wrapper flex-1 flex flex-col text-white">
    <x-admin.layout.header/>
    <div class="content flex-1 bg-gray-800 p-5 overflow-y-auto">
        @yield('content')
    </div>
</div>
@yield('js-custom')
</body>
</html>
