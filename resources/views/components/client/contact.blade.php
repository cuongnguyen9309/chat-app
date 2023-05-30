@php use Carbon\Carbon;use Illuminate\Support\Facades\Auth;use Illuminate\Support\Str; @endphp
@props(['contact','contactType','isRequest'=>false,'isSearch'=>false])
<div id="{{($isRequest ? 'request' : 'contact').'-'.$contactType.'-'.$contact->id }}" data-type="{{$contactType}}"
     data-id="{{$contact->id}}" data-name="{{$contact->name}}"
     data-image="{{$contact->image_url}}"
     class="{{$isRequest ? ($contactType === 'user' ? 'friend-request' : 'group-request') : 'contact-info'}} flex flex-row hover:cursor-pointer py-5 px-5  hover:bg-gray-900 duration-150">
    <div class="user-image w-14 h-14 flex items-center relative justify-center ">
        <img class="rounded-full w-full h-full"
             src="{{asset($contact->image_url ?? 'images/avatars/default-avatar.png')}}" alt="">
        @if(!$isSearch)
            <div
                class="unread_num {{$contact->unread_num ? '' : 'hidden'}} flex items-center justify-center text-white bg-red-600 absolute origin-center bottom-0 right-0 rounded-full text-xs w-4 h-4">{{is_null($contact->unread_num) ? 0 : $contact->unread_num}}</div>
        @endif
    </div>
    @if($isRequest)
        <div
            class="content ml-5 flex flex-col justify-center relative flex-1">
            <p class="text-green-500">{{$contact->name}}</p>
            <p class="text-gray-400">{{$contactType === 'user' ?
                                    'Sent you a friend request'
                                    :'Invite you to group'
                                    }}</p>
        </div>
    @else
        <div
            class="content ml-5 flex flex-col justify-center relative flex-1 text-gray-500">
            @if(!$isSearch)
                <div class="misc absolute top-0 right-0 text-xs text-right">
                    <p class="text-gray-400">{{$contact->last_sent ? Carbon::createFromFormat('Y-m-d H:i:s', $contact->last_sent)->diffForHumans() : ''}}</p>
                    <div class="option-wrapper relative inline-block text-left">
                        <i class="fa-solid fa-ellipsis"></i>
                        <div
                            class="options absolute right-0 z-10 mt-2 {{$contactType === 'user' ? 'w-36' : 'w-24'}} origin-top-right rounded-md bg-white duration-150 transition-transform shadow-lg scale-0 invisible">
                        <span data-type="{{$contactType}}" data-id="{{$contact->id}}"
                              class="remove-contact-btn text-gray-700 block px-2 py-1 text-xs">
                            {{$contactType === 'user' ? 'Remove from friendlist' : 'Leave group'}}
                        </span>
                        </div>
                    </div>
                </div>
            @endif
            <div
                class="{{property_exists($contact,'status') ? ($contact->status === 'offline' ? '' : 'text-green-500') : ''}} status-{{$contactType.'-'.$contact->id}}">{!!$contact->name!!}</div>
            @if(!$isSearch)
                <p class="last-content text-gray-400">{{$contactType === 'user'
                                                    ? ($contact->last_message_is_received ? '' : (is_null($contact->last_message_is_received) ? '' :  'You: '))
                                                    : (($contact->last_message_sender_id === Auth::id() ? 'You' : $contact->last_message_sender_name).': ')}}{{Str::limit($contact->last_content,20,'...')}}</p>
            @endif
        </div>
    @endif
</div>
