@props(['title'=>'','popupId'=>''])
<div {{$popupId ? "id=$popupId" : ''}}
     class="popup w-0 h-0 fixed z-90 invisible top-0 left-0">
    <div
        class="popup-content scale-0 absolute top-1/3 left-1/2 transform -translate-x-1/2 -translate-y-1/2 origin-center flex flex-col justify-center bg-gray-100 text-black transition-transform duration-500 rounded">
        <header class="flex items-center border-b-[1px] pb-1 border-gray-300 mb-2 p-5">
            <span class="popup-title mr-auto text-xl px-3">{{$title}}</span>
            <i data-target="{{'#'.$popupId}}"
               class="ml-5 close-popup fa-solid fa-xmark text-xl text-gray-500 hover:cursor-pointer"></i>
        </header>
        {{ $slot }}
    </div>
</div>
