@extends('admin.layout.master')
@section('content')
    <h1 class="text-4xl text-right">Messages</h1>
    <x-admin.table.table>
        <x-admin.table.thead>
            <x-admin.table.th>ID</x-admin.table.th>
            <x-admin.table.th>Content</x-admin.table.th>
            <x-admin.table.th>Sender</x-admin.table.th>
            <x-admin.table.th>Receiver</x-admin.table.th>
            <x-admin.table.th>Created at</x-admin.table.th>
            <x-admin.table.th>Updated at</x-admin.table.th>
        </x-admin.table.thead>
        <tbody>
        @foreach($messages as $message)
            <x-admin.table.tr>
                <x-admin.table.td :odd="$loop->odd">{{$message->id}}</x-admin.table.td>
                <x-admin.table.td :odd="$loop->odd">{{$message->content}}</x-admin.table.td>
                <x-admin.table.td :odd="$loop->odd">{{$message->sender->name}}</x-admin.table.td>
                <x-admin.table.td :odd="$loop->odd">{{$message->receiver->name}}</x-admin.table.td>
                <x-admin.table.td :odd="$loop->odd">{{$message->created_at}}</x-admin.table.td>
                <x-admin.table.td :odd="$loop->odd">{{$message->updated_at}}</x-admin.table.td>
            </x-admin.table.tr>
        @endforeach
        </tbody>
    </x-admin.table.table>
@endsection
