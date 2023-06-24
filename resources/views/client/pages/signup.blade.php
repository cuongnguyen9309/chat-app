<!doctype html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://kit.fontawesome.com/c2fe055d35.js" crossorigin="anonymous"></script>
    <script src="{{asset('js/jquery-3.7.0.min.js')}}"></script>
    <style>
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-transition-delay: 9999s;
            -webkit-transition: color 9999s ease-out, background-color 9999s ease-out;
        }
    </style>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="{{asset('css/main.css')}}">
    <title>Signup</title>
</head>
<body class="h-full flex bg-gray-800 text-white">
<div class="login-form-wrapper content-stretch w-[40%] min-w-[30rem] px-[10rem] py-[2rem]">
    <div class="logo font-logo text-green-400 tracking-wider text-6xl font-bold">GREEN</div>
    <form method="POST" action="{{route('signup.store')}}" class="mt-[6rem]  font-valera">
        @csrf
        <h2 class="text-3xl font-bold mb-[3rem]">Sign Up</h2>
        <div class="form-control mb-5">
            <div class="input-wrapper flex items-center py-3 border-b-[1.5px] border-gray-400">
                <input class="w-full bg-transparent focus:outline-none" type="email"
                       placeholder="Email address"
                       name="email">
                <i class="fa-solid fa-envelope"></i>
            </div>
            @error('email')
            <span class="text-red-600">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-control mb-5">
            <div class="input-wrapper flex items-center py-3  border-b-[1.5px] border-gray-400">
                <input id="passwordInput" class="w-full bg-transparent focus:outline-none" type="password"
                       placeholder="Password" name="password">
                <i id="passwordVisibility" class="fa-solid fa-eye text-gray-600"></i>
            </div>
            @error('password')
            <span class="text-red-600">{{$message}}</span>
            @enderror
        </div>
        <div class="form-control mb-5">
            <div class="input-wrapper flex items-center py-3  border-b-[1.5px] border-gray-400">
                <input id="confirmPwInput" class="w-full bg-transparent focus:outline-none" type="password"
                       placeholder="Confirm Password" name="password_confirmation">
                <i id="confirmPwVisibility" class="fa-solid fa-eye text-gray-600"></i>
            </div>
            @error('password_confirmation')
            <span class="text-red-600">{{$message}}</span>
            @enderror
        </div>
        <button
            class="w-full bg-green-600 text-center border-[2px] border-green-600 rounded-md py-3 px-5 mt-[5rem] mb-5"
            type="submit">
            Sign up
        </button>
        <a class="block text-sm mt-5 underline decoration-gray-400" href="{{route('login')}}">Already have an
            account?</a>
    </form>
</div>
<div
    class="page-image bg-authPage bg-cover flex-1 shadow-[-0.25rem_0px_0.3rem_0px_rgba(0,0,0,0.8),-0.25rem_0px_2rem_0px_rgba(0,0,0,1)]">
</div>
<script>
    function togglePasswordVisibility(event) {
        const pwInput = document.querySelector('#passwordInput');
        if (pwInput.type === "password") {
            pwInput.type = "text";
        } else {
            pwInput.type = "password";
        }
        event.target.classList.toggle('text-gray-600');
    }

    function toggleConfirmPwVisibility(event) {
        const confirmPwInput = document.querySelector('#confirmPwInput');
        if (confirmPwInput.type === "password") {
            confirmPwInput.type = "text";
        } else {
            confirmPwInput.type = "password";
        }
        event.target.classList.toggle('text-gray-600');
    }

    document.querySelector('#passwordVisibility').addEventListener('click', togglePasswordVisibility);
    document.querySelector('#confirmPwVisibility').addEventListener('click', toggleConfirmPwVisibility);
</script>
</body>
</html>
