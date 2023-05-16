<!doctype html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="{{asset('css/main.css')}}">
    <title>Document</title>
</head>
<body class="flex h-full">
<x-admin.layout.sidebar/>
<div class="right-wrapper flex-1 flex flex-col">
    <x-admin.layout.header/>
    <div class="content flex-1 bg-blue-50 p-5 overflow-y-auto">
        @yield('content')
    </div>
</div>
</body>
</html>
