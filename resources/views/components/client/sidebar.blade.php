@php use Illuminate\Support\Facades\Auth; @endphp
@props(['friends','joined_groups','friendRequests','groupRequests'])
<div class="user-info relative z-50 px-2 py-5">
    <div class="user-image-wrapper relative">
        <div id="user-image"
             class=" w-10 h-10 flex items-center justify-center bg-[#4ABA72] rounded-full text-white cursor-pointer">
            A
        </div>
        <div id="user-info-dropdown"
             class="min-w-max dropdown absolute top-[90%] left-[110%] invisible opacity-0 duration-200 bg-gray-300 rounded-md p-2 text-sm shadow-md shadow-gray-800 ">
            <p class="text-xl border-b-[1px] border-gray-400 mb-3">{{Auth::user()->name}}</p>
            <a href="{{route('logout')}}">Sign out</a>
        </div>
    </div>
</div>


<div id="sidebar-contact-wrapper"
     class="w-1/5 min-w-[20rem] content-stretch bg-gray-800 text-gray-50 shadow-xl shadow-gray-800 overflow-hidden">
    <x-client.sidebar.header/>
    <div class="filter-tab flex flex-row ml-5">
        <div data-target="#contacts-user"
             class="contact-filter-type p-1 border-b-2 border-gray-600 hover:border-gray-200 hover:cursor-pointer">
            Friends
        </div>
        <div data-target="#contacts-group"
             class="contact-filter-type p-1 border-b-2 border-gray-600 hover:border-gray-200 hover:cursor-pointer">
            Groups
        </div>
        <div data-target="#friend-requests"
             class="contact-filter-type p-1 border-b-2 border-gray-600 hover:border-gray-200 hover:cursor-pointer">
            Friend Request
        </div>
        <div data-target="#group-requests"
             class="contact-filter-type p-1 border-b-2 border-gray-600 hover:border-gray-200 hover:cursor-pointer">
            Group Request
        </div>
    </div>
    <div id="contacts-user" class="contacts h-full overflow-auto">
        <div id="contacts-user-reload">
            @forelse($friends as $friend)
                <x-client.contact :contact="$friend" contactType="user"/>
            @empty
            @endforelse
        </div>
    </div>
    <div id="contacts-group" class="contacts h-full overflow-auto hidden">
        <div id="contacts-group-reload">
            @forelse($joined_groups as $group)
                <x-client.contact :contact="$group" contactType="group"/>
            @empty
            @endforelse
        </div>
    </div>
    <div id="friend-requests" class="contacts h-full overflow-auto hidden">
        <div id="friend-requests-reload">
            @forelse($friendRequests as $friendRequest)
                <x-client.contact :contact="$friendRequest" contactType="user" isRequest/>
            @empty
            @endforelse
        </div>
    </div>
    <div id="group-requests" class="contacts h-full overflow-auto hidden">
        <div id="group-requests-reload">
            @forelse($groupRequests as $groupRequest)
                <x-client.contact :contact="$groupRequest" contactType="group" isRequest/>
            @empty
            @endforelse
        </div>
    </div>
</div>
