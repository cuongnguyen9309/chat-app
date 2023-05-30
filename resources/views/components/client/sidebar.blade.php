@php use Carbon\Carbon;use Illuminate\Support\Facades\Auth; @endphp

@props(['user',
'friends','joined_groups',
'friendRequests','groupRequests',
'search_contacts','search_messages','search',
'search_contacts_page','search_messages_page',
'search_contacts_page_num','search_messages_page_num'
])
<div id="user-info-sub-menu" class="user-info relative z-50 py-5">
    <div class="user-image-wrapper relative px-2">
        <div id="user-image"
             class=" w-10 h-10 flex items-center justify-center text-white cursor-pointer">
            <img class="w-full h-full object-cover rounded-full" src="{{$user->image_url}}" alt="">
        </div>
        <div id="user-info-dropdown"
             class="min-w-max px-4 dropdown absolute top-[90%] left-[110%] invisible opacity-0 duration-200 bg-gray-300 rounded-md p-2 text-sm shadow-md shadow-gray-800 ">
            <p class="user-name text-xl border-b-[1px] border-gray-400 mb-3">{{Auth::user()->name}}</p>
            <button id="your-info-btn" class="block mb-1 text-base  ">Your info</button>
            <a class="text-base" href="{{route('logout')}}">Sign out</a>
        </div>
    </div>
    <div class="sidebar-tabs">
        <button id="message-tab" class="h-12 w-full mt-4 flex">
            <div class="w-[2px] bg-green-500 h-full"></div>
            <div class="icon-wrapper flex w-full h-full items-center justify-center text-white">
                <i class="fa-solid fa-message text-2xl"></i>
            </div>
        </button>
    </div>
</div>


<div id="sidebar-contact-wrapper"
     class="lg:w-1/5
      lg:min-w-[20rem] w-0
      transform
      transition-all
      ease-out
      duration-300
      content-stretch bg-gray-800 text-gray-50 shadow-xl shadow-gray-800 overflow-hidden relative">
    <x-client.sidebar.header/>
    <div id="search-window" class="h-full w-full absolute z-10 bg-gray-800 origin-top scale-y-0 opacity-0 duration-300">
        <div class="search-results-filter-tab flex flex-row ml-5">
            <div data-target="#search-contact"
                 class="search-filter-type p-1 border-b-2 border-gray-200 hover:border-gray-200 hover:cursor-pointer">
                Contacts
            </div>
            <div data-target="#search-message"
                 class="search-filter-type p-1 border-b-2 border-gray-600 hover:border-gray-200 hover:cursor-pointer">
                Messages
            </div>
        </div>
        <div id="search-contact" class="search-results h-full overflow-auto">
            <div id="search-contact-reload" data-search="{{$search}}" data-page="{{$search_contacts_page}}">
                @if($search_contacts)
                    @forelse($search_contacts as $search_contact)
                        <x-client.contact isSearch="true" :contact="$search_contact"
                                          :contactType="$search_contact->type"/>
                    @empty
                    @endforelse
                    <nav class="flex justify-center">
                        <ul class="inline-flex -space-x-px">
                            @if($search_contacts_page > 1)
                                <li data-inc="-1" data-pageTarget="search-contact"
                                    class="page-nav px-3 py-2 ml-0 hover:cursor-pointer leading-tight text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                                    Previous
                                </li>
                            @endif
                            @if($search_contacts_page < $search_contacts_page_num)
                                <li data-inc="1" data-pageTarget="search-contact"
                                    class="page-nav px-3 py-2 leading-tight hover:cursor-pointer text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                                    Next
                                </li>
                            @endif
                        </ul>
                    </nav>
                @endif
            </div>
        </div>
        <div id="search-message" class="search-results h-full overflow-auto hidden">
            <div id="search-message-reload" data-search="{{$search}}" data-page="{{$search_messages_page}}">
                @if($search_messages)
                    @forelse($search_messages as $search_message)
                        <div class="flex flex-row hover:cursor-pointer py-5 px-5  hover:bg-gray-900 duration-150">
                            <div class="user-image w-14 h-14 flex items-center relative justify-center ">
                                <img class="rounded-full w-full h-full"
                                     src="{{asset($search_message->image_url ?? 'images/avatars/default-avatar.png')}}"
                                     alt="">
                            </div>
                            <div
                                class="content ml-5 flex flex-col justify-center relative flex-1">
                                <div class="misc absolute top-0 right-0 text-xs text-right">
                                    <p class="text-gray-400">{{$search_message->created_at ? Carbon::createFromFormat('Y-m-d H:i:s', $search_message->created_at)->diffForHumans() : ''}}</p>
                                </div>
                                <p class="text-green-500">{!!$search_message->name!!}</p>
                                <p class="last-content text-gray-400">{!!truncateAndHighlight($search_message->content,$search,20,'...')!!}</p>
                            </div>
                        </div>
                    @empty
                    @endforelse
                    <nav class="flex justify-center">
                        <ul class="inline-flex -space-x-px">
                            @if($search_messages_page > 1)
                                <li data-inc="-1" data-pageTarget="search-message"
                                    class="page-nav px-3 py-2 ml-0 hover:cursor-pointer leading-tight text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                                    Previous
                                </li>
                            @endif
                            @if($search_messages_page < $search_messages_page_num)
                                <li data-inc="1" data-pageTarget="search-message"
                                    class="page-nav px-3 py-2 leading-tight hover:cursor-pointer text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                                    Next
                                </li>
                            @endif
                        </ul>
                    </nav>

                @endif
            </div>
        </div>

    </div>
    <div class="filter-tab flex flex-row ml-5">
        <div data-target="#contacts-user"
             class="contact-filter-type p-1 border-b-2 border-gray-200 hover:border-gray-200 hover:cursor-pointer">
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
