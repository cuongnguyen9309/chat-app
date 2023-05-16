@props(['message','sender_name','reverse'])
<div class="message text-black max-w-7xl my-4 bg-gray-200 py-3 px-2 {{isset($reverse) ? 'rounded-mr' : 'rounded-ml'}}">
    @isset($sender_name)
        <p class="user-name leading-8 text-xs text-gray-500">{{$sender_name}}</p>
    @endif
    {{$message}}
</div>
