@extends('admin.layout.master')
@section('content')
    <h1 class="text-4xl text-right">Users</h1>
    <x-admin.table.table>
        <x-admin.table.thead>
            <x-admin.table.th>ID</x-admin.table.th>
            <x-admin.table.th>Name</x-admin.table.th>
            <x-admin.table.th>Email</x-admin.table.th>
            <x-admin.table.th>Created at</x-admin.table.th>
            <x-admin.table.th>Updated at</x-admin.table.th>
        </x-admin.table.thead>
        <tbody>
        @foreach($users as $user)
            <x-admin.table.tr>
                <x-admin.table.td :odd="$loop->odd">{{$user->id}}</x-admin.table.td>
                <x-admin.table.td :odd="$loop->odd">{{$user->name}}</x-admin.table.td>
                <x-admin.table.td :odd="$loop->odd">{{$user->email}}</x-admin.table.td>
                <x-admin.table.td :odd="$loop->odd">{{$user->created_at}}</x-admin.table.td>
                <x-admin.table.td :odd="$loop->odd">{{$user->updated_at}}</x-admin.table.td>
            </x-admin.table.tr>
        @endforeach
        </tbody>
    </x-admin.table.table>
@endsection
