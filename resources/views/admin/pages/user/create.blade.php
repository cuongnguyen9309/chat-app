@extends('admin.layout.master')
@section('content')
    <form id="create-user-form" class="w-full max-w-lg text-black" method="POST" action="{{route('admin.user.store')}}"
          enctype="multipart/form-data">
        @csrf
        <div id="user-image-wrapper flex flex-wrap -mx-3 mb-6"
             class="img-wrapper w-24 h-24">
            <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0 hover:cursor-pointer">
                <img id="user-image" class="w-full h-full object-cover rounded-full"
                     src="/images/avatars/default-avatar.png"
                     alt="">
                <input
                    name="image"
                    data-target="#user-image"
                    id="user-avatar"
                    type="file" class="hidden">
                <i class="text-gray-600 fa-solid fa-floppy-disk absolute bottom-0 right-2 origin-center"></i>
            </div>
        </div>
        <div class="flex flex-wrap -mx-3 mb-6">
            <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
                <label class="block uppercase tracking-wide text-xs font-bold mb-2 text-white" for="grid-first-name">
                    Name
                </label>
                <input
                    name="name"
                    class="appearance-none block w-full bg-gray-200 border border-red-500 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white"
                    id="grid-first-name" type="text" placeholder="User Name">
                @error('name')
                <span class="text-red-600">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="flex flex-wrap -mx-3 mb-6">
            <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
                <label class="block uppercase tracking-wide text-xs font-bold mb- text-white" for="email">
                    Email
                </label>
                <input
                    name="email"
                    class="appearance-none block w-full bg-gray-200 border border-red-500 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white"
                    id="email" type="text" placeholder="Email">
                @error('email')
                <span class="text-red-600">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="flex flex-wrap -mx-3 mb-6">
            <div class="w-full px-3">
                <label class="block uppercase tracking-wide text-xs font-bold mb-2 text-white" for="grid-password">
                    Password
                </label>
                <input
                    name="password"
                    class="appearance-none block w-full bg-gray-200 border border-gray-200
                    rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                    id="password" type="password" placeholder="******************">
                @error('password')
                <span class="text-red-600">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="flex flex-wrap -mx-3 mb-6">
            <div class="w-full px-3">
                <label class="block uppercase tracking-wide text-xs font-bold mb-2 text-white" for="password-confirm">
                    Password Confirmation
                </label>
                <input
                    name="password_confirmation"
                    class="appearance-none block w-full bg-gray-200 border border-gray-200 rounded
                     py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                    id="password-confirm" type="password" placeholder="******************">
            </div>
        </div>
        <div class="flex flex-wrap -mx-3 mb-2">
            <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                <div class="flex items-center mb-4">
                    <input name="is_accept_stranger_request" id="friend-request-checkbox" type="checkbox" value="1"
                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 ">
                    <label for="friend-request-checkbox" class="ml-2 text-sm font-medium  text-white">Accept Friend
                        request
                        from strangers?</label>
                </div>
            </div>
        </div>
        <div class="flex flex-wrap -mx-3">
            <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                <div class="flex items-center mb-4">
                    <input name="is_admin" id="is-admin-checkbox" type="checkbox" value="1"
                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 ">
                    <label for="is-admin-checkbox" class="ml-2 text-sm font-medium  text-white">Is Admin?</label>
                </div>
            </div>
        </div>
        <div class="button-wrapper flex">
            <button id="create-user-btn" type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-full ml-auto mr-11 text-white">
                Create New User
            </button>
        </div>
    </form>
@endsection
@section('js-custom')
    <script>
        $(document).ready(function () {
            $('#user-image').on('click', function () {
                $('#user-avatar').trigger('click');
            })
            $('#user-avatar').on('change', function (event) {
                const filePath = URL.createObjectURL(event.target.files[0]);
                const target = $(this).data('target');
                $(`${target}`).attr('src', filePath);
            })
        })
    </script>
@endsection
