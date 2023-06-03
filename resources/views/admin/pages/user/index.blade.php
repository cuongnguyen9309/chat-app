@php use Illuminate\Support\Str; @endphp
@extends('admin.layout.master')
@section('content')
    <div class="button-wrapper flex">
        <h1 class="text-4xl ml-5">Users</h1>
        <a
            href="{{route('admin.user.create')}}"
            class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-16 duration-200 border border-gray-400 rounded shadow ml-auto mr-5 mt-3">
            Create New User
        </a>
    </div>


    <x-admin.table.table>
        <x-admin.table.thead>
            <x-admin.table.th>ID</x-admin.table.th>
            <x-admin.table.th>Name</x-admin.table.th>
            <x-admin.table.th>Email</x-admin.table.th>
            <x-admin.table.th>Created at</x-admin.table.th>
            <x-admin.table.th>Updated at</x-admin.table.th>
            <x-admin.table.th>Action</x-admin.table.th>
        </x-admin.table.thead>
        <tbody>
        @foreach($users as $user)
            <x-admin.table.tr>
                <x-admin.table.td :odd="$loop->odd">{{$user->id}}</x-admin.table.td>
                <x-admin.table.td :odd="$loop->odd">
                    <span id="user-{{$user->id}}"
                          class="flex items-center {{$user->status === 'online' ? 'text-green-500' : ''}}"><div
                            class="w-[3rem] h-[3rem] min-w-[3rem] image-wrapper mr-3">
                            <img class="w-full h-full object-cover rounded-full" src="{{asset($user->image_url)}}"
                                 alt="">
                        </div>{{Str::limit($user->name,20,'...')}}
                    </span>
                </x-admin.table.td>
                <x-admin.table.td :odd="$loop->odd">{{$user->email}}</x-admin.table.td>
                <x-admin.table.td :odd="$loop->odd">{{$user->created_at}}</x-admin.table.td>
                <x-admin.table.td :odd="$loop->odd">{{$user->updated_at}}</x-admin.table.td>
                <x-admin.table.td :odd="$loop->odd">
                    <a
                        class="mr-2 font-semibold bg-yellow-400 hover:bg-yellow-600 text-gray-800 py-1 px-4 duration-200 rounded shadow-xl"
                        href="{{route('admin.user.show',$user->id)}}">
                        Edit
                    </a>
                    <button
                        class="font-semibold bg-red-500 hover:bg-red-700 text-gray-800 py-1 px-4 duration-200 rounded shadow-xl">
                        Delete
                    </button>
                </x-admin.table.td>
            </x-admin.table.tr>
        @endforeach
        </tbody>
    </x-admin.table.table>
    <div class="paginate flex justify-center mt-3">{{ $users->links() }}</div>

@endsection
@section('js-custom')
    <script>
        $(document).ready(function () {
            Echo.join('chat')
                .joining(function (user) {
                    $(`#user-${user.id}`).addClass('text-green-500');
                })
                .leaving(function (user) {
                    $(`#user-${user.id}`).removeClass('text-green-500');
                })
        })
    </script>
@endsection
