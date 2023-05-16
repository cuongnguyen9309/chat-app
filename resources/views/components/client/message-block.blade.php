@props(['messages','reverse' => false])
@php
    $sender_name = 'Nguyen Test'
@endphp
<div class="flex {{$reverse ? 'flex-row-reverse' : ''}} mb-5">
    <div
        class="user-image w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center {{$reverse ? 'ml-5' : 'mr-5'}}">
        A
    </div>
    <div class="messages-wrapper">
        <x-client.chat-message :message="$messages[0]" :sender_name="$sender_name" :reverse="$reverse"/>
        <x-client.chat-message :message="$messages[1]" :reverse="$reverse"/>
    </div>
</div>

