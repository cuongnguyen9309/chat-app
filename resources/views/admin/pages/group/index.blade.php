@php use Illuminate\Support\Str; @endphp
@extends('admin.layout.master')
@section('content')
    <div class="button-wrapper flex">
        <h1 class="text-4xl ml-5">Groups</h1>
        <button
            class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-16 duration-200 border border-gray-400 rounded shadow ml-auto mr-5 mt-3">
            Create New Group
        </button>
    </div>


    <x-admin.table.table>
        <x-admin.table.thead>
            <x-admin.table.th>ID</x-admin.table.th>
            <x-admin.table.th>Name</x-admin.table.th>
            <x-admin.table.th>Admin</x-admin.table.th>
            <x-admin.table.th>Created at</x-admin.table.th>
            <x-admin.table.th>Updated at</x-admin.table.th>
            <x-admin.table.th>Action</x-admin.table.th>
        </x-admin.table.thead>
        <tbody>
        @foreach($groups as $group)
            <x-admin.table.tr>
                <x-admin.table.td :odd="$loop->odd">{{$group->id}}</x-admin.table.td>
                <x-admin.table.td :odd="$loop->odd">
                    <span class="flex items-center"><div class="w-10 h-10 image-wrapper mr-3">
                            <img class="w-full h-full object-cover rounded-full" src="{{asset($group->image_url)}}"
                                 alt="">
                        </div>{{Str::limit($group->name,25,'...')}}
                    </span>
                </x-admin.table.td>
                <x-admin.table.td :odd="$loop->odd">{{$group->admin->name}}</x-admin.table.td>
                <x-admin.table.td :odd="$loop->odd">{{$group->created_at}}</x-admin.table.td>
                <x-admin.table.td :odd="$loop->odd">{{$group->updated_at}}</x-admin.table.td>
                <x-admin.table.td :odd="$loop->odd">
                    <a
                        class="mr-2 font-semibold bg-yellow-400 hover:bg-yellow-600 text-gray-800 py-1 px-4 duration-200 rounded shadow-xl"
                        href="{{route('admin.group.show',$group->id)}}">
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
    <div class="paginate flex justify-center mt-3">{{ $groups->links() }}</div>

@endsection
