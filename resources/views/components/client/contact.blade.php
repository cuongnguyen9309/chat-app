@props(['contact','type'])
<div class="contact flex flex-row hover:cursor-pointer py-5 px-5  hover:bg-gray-900 duration-150">
    <div class="user-image w-14 h-14 flex items-center justify-center ">
        <img class="rounded-full" src="{{asset('images')."/{$contact->image_url}"}}" alt="">
    </div>
    <div data-type="{{$type}}" id="{{$contact->id}}"
         class="contact-info ml-5 flex flex-col justify-center relative flex-1">
        <span class="absolute top-0 right-0 text-xs text-gray-400">1 hour ago</span>
        <p class="text-green-500">{{$contact->name}}</p>
        <p class="text-gray-400">Last message</p>
    </div>
</div>
