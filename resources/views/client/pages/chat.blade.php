@php
    use Illuminate\Support\Facades\Auth;use Illuminate\Support\Str;$messages = array('Test message 1', 'Test message 2');
@endphp
    <!doctype html>
<html class="h-full" lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        em {
            background-color: lightgreen;
            font-style: normal;
        }

        .ui-autocomplete {
            visibility: hidden;
            position: absolute;
            z-index: 9999;
            background-color: #fff;
            border: 1px solid #ccc;
            max-height: 200px;
            overflow-y: auto;
        }

        /* Autocomplete dropdown list item */
        .ui-autocomplete li {
            padding: 5px;
            cursor: pointer;
        }

        /* Autocomplete dropdown list item hover state */
        .ui-autocomplete li.ui-state-hover {
            background-color: #f2f2f2;
        }

        /* Autocomplete dropdown list item active state */
        .ui-autocomplete li .ui-menu-item-wrapper.ui-state-active {
            background-color: cornflowerblue;
            color: white;
        }


    </style>
    <script src="https://kit.fontawesome.com/c2fe055d35.js" crossorigin="anonymous"></script>
    <script src="{{asset('js/jquery-3.7.0.min.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"
            integrity="sha512-57oZ/vW8ANMjR/KQ6Be9v/+/h6bq9/l3f0Oc7vn6qMqyhvPd1cvKBRWWpzu0QoneImqr2SkmO4MSqU+RpHom3Q=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-autocomplete/1.0.7/jquery.auto-complete.min.js"
            integrity="sha512-TToQDr91fBeG4RE5RjMl/tqNAo35hSRR4cbIFasiV2AAMQ6yKXXYhdSdEpUcRE6bqsTiB+FPLPls4ZAFMoK5WA=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    @vite('resources/css/app.css')
    @vite('resources/js/bootstrap.js')
    <link rel="stylesheet" href="{{asset('css/main.css')}}">
    <title>Chat</title>
</head>
{{--First load 10 messages with blade template. Listen for websocket event. Add new message to chat-window on event.
    If user scroll upwards, get chat-window partial, create another new 10 chat messages and return the html to client. On client use insertBefore to append.--}}
<body class="bg-gray-900 w-full h-full ">
<div class="hidden" id="message-template">
    {{--    {{$reverse ? 'flex-row-reverse' : ''}}--}}
    <div class="message-block flex  mb-5">
        <div
            {{--    {{$reverse ? 'ml-5' : 'mr-5'}}--}}
            class="user-image-wrapper w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center ">
            <img class="user-image w-full h-full rounded-full" src="" alt="">
        </div>
        <div class="messages-wrapper w-3/4">
            {{--Add chat message here--}}
        </div>
    </div>

    {{--    Add rounded-mr if reverse, rounded-ml if not--}}
    <div class="message text-black max-w-7xl my-4 bg-gray-200 py-3 px-2 duration-200 transition-colors">
    </div>
    {{--    UserInfo Tag. Append to new message when chat owner change--}}
    <p class="user-name leading-8 text-xs text-gray-500"></p>
    <p class="send-message-error text-red-600 text-xs">Message sending failed</p>

    {{-- Attachment view --}}
    <div class="message-attachment">
        <div class="attachment-wrapper">
            <div class="attachment flex mb-2">
                <div class="attachment-thumbnail-wrapper">
                    <img class="attachment-thumbnail" src="{{asset('/images/file_thumbnails/audio.png')}}" alt="">
                </div>
                <div class="attachment-info">
                    <p class="attachment-name">Attachment name</p>
                    <p class="attachment-size">10 MB</p>
                </div>
                <a class="download-link inline-block"><i class="fa-solid fa-download"></i></a>
            </div>
        </div>
    </div>
</div>
<div class="hidden" id="chat-window-content-template">
    <div class="hidden chat-window-content w-full h-full">
    </div>
</div>
<div class="hidden" id="toast-notification-template">
    <div class="transform toast-notif hover:cursor-pointer bg-blue-400 p-2
     text-white absolute z-[100] rounded-xl
    right-0 top-20 translate-x-full transition-all duration-500">
        <span class="notif-content"></span>
    </div>
</div>
<div class="flex flex-row h-full">
    <x-client.sidebar :user="$user"
                      :friends="$friends"
                      :joined_groups="$joined_groups"
                      :friendRequests="$friendRequests"
                      :groupRequests="$groupRequests"
                      :search_contacts="$search_contacts"
                      :search_messages="$search_messages"
                      :search="$input"
                      :search_contacts_page="$search_contacts_page"
                      :search_contacts_page_num="$search_contacts_page_num"
                      :search_messages_page="$search_messages_page"
                      :search_messages_page_num="$search_messages_page_num"
    />
    <div class="w-full flex-1 flex flex-col max-h-full overflow-hidden">
        <x-client.header/>


        <div id="chat-window" class="chat-window overflow-y-scroll p-5 flex-1">

        </div>
        <form class="border-gray-200 px-4 pt-4 mb-2 sm:mb-3" id="chat-input-form"
              enctype="multipart/form-data"
              action=""
              method="POST"
        >
            @csrf
            <input type="file" id="attachment-input" class="hidden" name="attachment">
            <div
                class="relative flex w-full bg-gray-200 rounded-md">
                <textarea
                    id="chat-textarea"
                    name="content"
                    placeholder="Enter your message"
                    class="w-full resize-none bg-gray-200 focus:outline-none focus:placeholder-gray-400 text-gray-600 rounded-md placeholder-gray-600 py-3 px-5"
                    rows="1"></textarea>
                <div class=" items-center inset-y-0 flex">
                    <button
                        type="button"
                        id="upload-attachment-btn"
                        class="flex items-center justify-center rounded-full h-10 w-10 transition duration-500 ease-in-out text-gray-500 hover:bg-gray-300 focus:outline-none">
                        <i class="fa-solid fa-paperclip"></i>
                    </button>
                    <button
                        type="button"
                        class="flex items-center justify-center rounded-full h-10 w-10 transition duration-500 ease-in-out text-gray-500 hover:bg-gray-300 focus:outline-none">
                        <i class="fa-solid fa-camera"></i>
                    </button>
                    <button
                        type="button"
                        class="flex items-center justify-center rounded-full h-10 w-10 transition duration-500 ease-in-out text-gray-500 hover:bg-gray-300 focus:outline-none">
                        <i class="fa-regular fa-face-smile"></i>
                    </button>
                    <button type="submit"
                            class="flex items-center justify-center rounded-2xl py-3 px-3 mr-2 transition duration-500 ease-in-out text-white bg-green-400 hover:bg-blue-400 focus:outline-none">
                        <i class="fa-regular fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </form>

    </div>
    <div id="overlay"
         class="overlay fixed z-60 w-0 h-0 bg-gray-900 opacity-60">
    </div>
    <x-client.popup popupId="add-friend-popup" title="Add Friend">
        <div class="px-5 pb-5">
            <form id="search-friend-form" action="" method="POST">
                <input id="search-friend-input" type="text" placeholder="Friend ID"
                       class="bg-none w-full focus:outline-none text-black p-1 rounded-md mt-2" autocomplete="off">
            </form>
            <x-client.friend-info/>
            <div class="btn-wrapper flex items-center justify-center">
                <button id="add-friend-button"
                        class="hidden text-center bold bg-green-400 mt-5 p-2 rounded-xl">Add friend
                </button>
            </div>
            <div class="error hidden text-center">Invalid User ID</div>
        </div>
    </x-client.popup>
    <x-client.popup popupId="accept-friend-popup" title="Friend Request">
        <div class="px-5 pb-5">
            <x-client.friend-info/>
            <p class="mt-5">Has sent you a friend request</p>
            <button id="accept-friend-button" data-id="0"
                    class="text-center bold bg-green-400 mt-5 p-2 rounded-xl">Accept
            </button>
        </div>
    </x-client.popup>
    <x-client.popup popupId="accept-group-popup" title="Group Invite">
        <div class="px-5 pb-5">
            <x-client.friend-info/>
            <p class="mt-5">Has invite you to join</p>
            <button id="accept-group-button" data-id="0"
                    class="text-center bold bg-green-400 mt-5 p-2 rounded-xl">Accept
            </button>
        </div>
    </x-client.popup>
    <x-client.popup popupId="create-group-popup" title="Create new group">
        <div class="px-5 pb-5">
            <form id="create-group-form" action="" class="group-name" enctype="multipart/form-data">
                <div class="top-wrapper flex mt-5 items-center pb-3 border-b-[1px] border-gray-200">
                    <div id="group-avatar-wrapper"
                         class="user-image w-14 h-14 flex items-center justify-center relative hover:cursor-pointer mr-5">
                        <img id="group-avatar" class="rounded-[50%] w-full h-full object-cover"
                             src="{{asset('images/avatars')."/default-avatar.png"}}"
                             alt="">
                        <i class="text-gray-600 fa-solid fa-floppy-disk absolute bottom-0 right-0 origin-center"></i>
                    </div>
                    <input class="bg-none focus:outline-none p-2 text-black rounded-md" type="text"
                           placeholder="Group name" name="name">
                    <input id="group-avatar-upload" type="file" class="hidden" name="group-avatar"
                    >
                </div>
                <div class="friend-select bg-white p-3 rounded-xl mt-5 mb-6">
                    <h2 class="text-lg">Invite friends</h2>
                    @forelse($friends as $friend)
                        <label class="flex items-center" for="group-select-friend-{{$friend->id}}"><input
                                id="group-select-friend-{{$friend->id}}"
                                class="w-4 h-4 text-green-600 border-1 rounded-md focus:ring-0 mr-2"
                                type="checkbox" name="selectFriends[]"
                                value="{{$friend->id}}"/>{{Str::limit($friend->name,20,'...')}}
                        </label>
                    @empty
                    @endforelse
                </div>
                <div class="button-wrapper flex w-full">
                    <button
                        class="ml-auto rounded-lg hover:bg-green-600 text-white duration-200 transition-colors transition-transform bg-green-500 py-2 px-4">
                        Create Group
                    </button>
                </div>
            </form>
        </div>
    </x-client.popup>
    <x-client.popup popupId="user-detail" title="User Info">
        <div id="edit-user-info">
            <div id="edit-user-info-reload">
                <form id="edit-user-info-form" action="" enctype="multipart/form-data">
                    <header class="relative w-full min-w-[20rem] h-32 bg-authPage bg-cover">
                        <div id="edit-user-image-wrapper"
                             class="img-wrapper w-24 h-24 absolute left-1/2 top-[100%] -translate-x-1/2 -translate-y-1/2">
                            <img id="edit-user-image" class="w-full h-full object-cover rounded-full"
                                 src="{{$user->image_url}}"
                                 alt="">
                            <input
                                disabled
                                data-oldValue="{{$user->image_url}}"
                                name="image"
                                data-target="#edit-user-image"
                                id="edit-user-avatar"
                                type="file" class="hidden">
                            <i class="hidden text-gray-600 fa-solid fa-floppy-disk absolute bottom-0 right-2 origin-center"></i>
                        </div>
                    </header>
                    <input
                        data-oldvalue="{{$user->name}}"
                        name="name"
                        disabled
                        class="block text-center text-2xl mt-12 font-bold
                    disabled:text-black disabled:bg-transparent bg-transparent
            disabled:border-none border-none
            focus:outline-none border-transparent focus:border-transparent focus:ring-0"
                        type="text" value="{{$user->name}}">
                    <div class="px-5 pb-5 mt-4">
                        <table class="user-info w-3/4 min-w-[50px]">
                            <tr>
                                <td class="pr-5 text-gray-600">ID:</td>
                                <td>{{$user->id}}</td>
                            </tr>
                            <tr>
                                <td class="pr-5 text-gray-600"><label for="edit-user-name">Email</label></td>
                                <td><input
                                        data-oldvalue="{{$user->email}}"
                                        name="email"
                                        disabled id="edit-user-name"
                                        class="disabled:text-black disabled:bg-transparent bg-transparent
                                   disabled:border-none border-none focus:outline-none
                                   border-transparent focus:border-transparent focus:ring-0 p-0"
                                        type="text"
                                        value="{{$user->email}}">
                                </td>
                            </tr>
                        </table>
                        <div class="btn-wrapper flex justify-center items-center">
                            <button
                                type="button"
                                id="toggle-edit-user-info"
                                class="text-center bold bg-[#f2cc8f] hover:bg-[#E9A377] transition-colors duration-200 mt-5 p-2 rounded-xl">
                                Edit Info
                            </button>
                            <button
                                type="submit"
                                id="confirm-user-info"
                                class="hidden text-center bold bg-[#48cae4] mr-11
                        hover:bg-[#ade8f4] transition-colors duration-200 mt-5 p-2 rounded-xl">
                                Confirm
                            </button>
                            <button
                                type="button"
                                id="cancel-user-info"
                                class="hidden text-center bold bg-[#ef233c] hover:bg-[#ffddd2] transition-colors duration-200 mt-5 p-2 rounded-xl">
                                Cancel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </x-client.popup>

    <x-client.message-modal/>
</div>

<script>
    /*   chatOwner: For message side decision
            USER = 0
             PARTNER = sender_id*/
    let friends_id = {{$friends->pluck('id')}};
    let currPartnerKey = '';
    let addFriendId = 0;
    let chatWindowStatus = [];
    let timerId = undefined;
    let readMessages = [];
    let chatInput = '';
    // HTML element reuse
    const sidebarContactWrapper = $('#sidebar-contact-wrapper');
    const chatInputForm = $('#chat-input-form');
    const chatTextArea = document.querySelector('#chat-textarea');
    const chatTextAreaJquery = $('#chat-textarea');
    const editUserInfo = $('#edit-user-info');

    /* Throttle Function */
    function throttle(func, delay) {
        if (timerId) {
            return
        }
        timerId = setTimeout(function () {
            func();
            timerId = undefined;
        }, delay)
    }

    /* Create intersection observer for infinite loading */
    function loadMoreOnScroll(entries, observer) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                observer.unobserve(entry.target);
                const [partner_type, partner_id] = currPartnerKey.split('-');
                const partner_key = currPartnerKey;
                const oldScrollHeight = $(`#chat-window`)[0].scrollHeight;
                $.ajax({
                    url: "{{route('user.message.retrieve')}}",
                    type: "POST",
                    data: {
                        headId: chatWindowStatus[currPartnerKey].headId,
                        partner_type,
                        partner_id
                    },
                    success: function (res) {
                        const messages = res.messages;
                        buildMessageUpward(messages, partner_key);
                        const newScrollHeight = $(`#chat-window`)[0].scrollHeight;
                        $(`#chat-window`)[0].scrollTo(0, newScrollHeight - oldScrollHeight);/* Fix window jump to top on prepend */
                        if (chatWindowStatus[partner_key].olderMessages) {/*Only add intersection event again if there's still messages to be loaded */
                            observer.observe($(`#${partner_key}`).find('.message').first()[0]);
                        }
                    },
                    error: function (xhr) {
                        xhr = JSON.parse(xhr.responseText);
                        alert(xhr.message);
                    }
                })
            }
        })
    }

    function sendReadData() {
        const read = readMessages;
        readMessages = [];
        const jsonStr = JSON.stringify(read);
        $.ajax({
            url: "{{route('user.message.read')}}",
            data: {
                read: jsonStr,
            },
            type: "POST",
            success: function (res) {
                const partner_keys = res.partner_keys;
                for (const partner_key in partner_keys) {
                    const qty = partner_keys[partner_key];
                    const contact = $(`#contact-${partner_key}`);
                    const newVal = parseInt(contact.find('.unread_num').text()) - qty;
                    if (newVal <= 0) {
                        contact.find('.unread_num').addClass('hidden');
                    }
                    contact.find('.unread_num').text(newVal);
                }
            },
            error: function (xhr) {
                xhr = JSON.parse(xhr.responseText);
                alert(xhr.message);
            }
        })
    }

    function readOnIntersect(entries, observer) {/* Intersection observer for updating message read status */
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const message_key = `${entry.target.dataset.type}-${entry.target.dataset.id}`;
                readMessages.push(message_key);
                observer.unobserve(entry.target);
                throttle(sendReadData, 1000);
            }
        })
    }

    const chatWindowLoadMessage = {
        root: document.querySelector('#chat-window'),
        rootMargin: '0px',
        threshold: 1,
    };
    const chatWindowReadMessage = {
        root: document.querySelector('#chat-window'),
        rootMargin: '0px',
        threshold: 0.5,
    };
    const observer = new IntersectionObserver(loadMoreOnScroll, chatWindowLoadMessage);
    const readObserver = new IntersectionObserver(readOnIntersect, chatWindowReadMessage);
    /********/

    /* Expand textarea on newline */
    function OnInput() {
        this.style.height = 0;
        this.style.height = (this.scrollHeight) + "px";
    }

    chatTextArea.setAttribute("style", "height:" + (chatTextArea.scrollHeight) + "px;overflow-y:hidden;");
    chatTextArea.addEventListener("input", OnInput, false);

    /******/
    function translateSenderId($id) {
        return ($id == {{Auth::id()}} ? 0 : $id);
    }

    function createChatWindow(partner_key) {
        const template = $('#chat-window-content-template');
        const chatWindowContent = template.find('.chat-window-content').clone(true);
        const spinner = '<div class="flex w-full justify-center loading hidden" role="status"> <svg aria-hidden="true" class="w-8 h-8 mr-2 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/><path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/></svg><span class="sr-only">Loading...</span></div>';
        chatWindowContent.attr('id', partner_key);
        chatWindowContent.append(spinner);
        $('#chat-window').append(chatWindowContent);
        return chatWindowContent;
    }

    function createMessageBlock(messageInfo) {
        const template = $('#message-template');
        const messageBlock = template.find('.message-block').clone(true);
        const userImageWrapper = messageBlock.find('.user-image-wrapper');
        const userImage = messageBlock.find('.user-image');
        userImage.attr('src', messageInfo.sender_image_url);
        if (messageInfo.sender_id != "{{Auth::id()}}") {
            messageBlock.addClass('flex-row-reverse');
            userImageWrapper.addClass('ml-5');
        } else {
            userImageWrapper.addClass('mr-5');
        }
        return messageBlock;
    }

    function createMessage(content, type, id, chatOwner, newChatOwnerName = '') {
        const template = $('#message-template');
        const message = template.find('.message').clone(true);
        message.attr('data-id', id);
        message.attr('data-type', type);
        message.attr('id', `${type}-message-${id}`);
        if (chatOwner) {
            message.addClass('rounded-mr');
        } else {
            message.addClass('rounded-ml');
        }
        if (newChatOwnerName) {
            const infoTag = template.find('.user-name').clone(true);
            infoTag.text(newChatOwnerName);
            message.append(infoTag);
        }
        message.append(`<span>${content}</span>`);
        return message;
    }

    function showMessage(messageInfo) {
        let partner_key = '';
        if (messageInfo['receiver_type'] === 'user') {/* create partner key */
            partner_key = messageInfo.sender_id == {{Auth::id()}} ? `user-${messageInfo['receiver_id']}` : `user-${messageInfo['sender_id']}`;
        } else {
            partner_key = `${messageInfo['receiver_type']}-${messageInfo['receiver_id']}`;
        }
        let message;
        if (chatWindowStatus[partner_key]) {/* Check if the chat window for this convo has been registered  */
            const sender_id = translateSenderId(messageInfo.sender_id);
            let messageBlock = '';
            if (chatWindowStatus[partner_key].tailChatOwner == sender_id) {
                if ($('.message-block', `#${partner_key}`).length) {
                    message = createMessage(messageInfo.content, messageInfo.receiver_type, messageInfo.id, chatWindowStatus[partner_key].tailChatOwner);
                    messageBlock = $('.message-block', `#${partner_key}`).last();
                } else {
                    message = createMessage(messageInfo.content, messageInfo.receiver_type, messageInfo.id, chatWindowStatus[partner_key].tailChatOwner, messageInfo.sender_name);
                    messageBlock = createMessageBlock(messageInfo);
                    $(`#${partner_key}`).append(messageBlock);
                }
            } else {
                chatWindowStatus[partner_key].tailChatOwner = sender_id;
                message = createMessage(messageInfo.content, messageInfo.receiver_type, messageInfo.id, chatWindowStatus[partner_key].tailChatOwner, messageInfo.sender_name);
                messageBlock = createMessageBlock(messageInfo);
                $(`#${partner_key}`).append(messageBlock);
            }
            messageBlock.find('.messages-wrapper').append(message);
        }
        if (currPartnerKey === partner_key) {
            message[0].scrollIntoView(false);
        }
        if (messageInfo.sender_id != {{Auth::id()}}) {
            if ((messageInfo.receiver_type === 'user' && !(messageInfo.seen_at)) || (messageInfo.receiver_type === 'group' && messageInfo.unseen)) {
                readObserver.observe(message[0]);
            }
        }
        return message;
    }

    function buildMessageUpward(messages, partner_key) {
        const senders_id = messages.map(message => message.sender_id);
        if (messages.length < 10) {
            chatWindowStatus[partner_key].olderMessages = false;
        }
        messages.forEach((messageInfo, index) => {
            let message;
            let messageBlock;
            if (chatWindowStatus[partner_key]) {/* Check if the chat window for this convo has been registered  */
                const sender_id = translateSenderId(messageInfo.sender_id);/* Translate sender_id into 0,1
                                with O is user and 1 is for partner */

                if (index === 0) {/* Remove previous message name tag if it's from the same sender */
                    if (chatWindowStatus[partner_key].headChatOwner == sender_id) {
                        $(`#${partner_key}`).find('.message').first().find('.user-name').remove();
                    }
                }

                if (messageInfo.sender_id != senders_id[index + 1]) {/* Create message, add name tag or not depend on the next message sender */
                    message = createMessage(messageInfo.content, messageInfo.receiver_type, messageInfo.id, chatWindowStatus[partner_key].headChatOwner, messageInfo.sender_name);
                } else {
                    message = createMessage(messageInfo.content, messageInfo.receiver_type, messageInfo.id, chatWindowStatus[partner_key].headChatOwner);
                }

                if (chatWindowStatus[partner_key].headChatOwner == sender_id) {/* Create or get the previous message block */
                    if ($('.message-block', `#${partner_key}`).length) {
                        messageBlock = $('.message-block', `#${partner_key}`).first();
                    } else {
                        messageBlock = createMessageBlock(messageInfo);
                        $(`#${partner_key}`).prepend(messageBlock);
                    }
                } else {
                    chatWindowStatus[partner_key].headChatOwner = sender_id;
                    messageBlock = createMessageBlock(messageInfo);
                    $(`#${partner_key}`).prepend(messageBlock);
                }
                messageBlock.find('.messages-wrapper').prepend(message);
                if ((messageInfo.receiver_type === 'user' && !(messageInfo.seen_at)) || (messageInfo.receiver_type === 'group' && messageInfo.unseen)) {
                    readObserver.observe(message[0]);
                }
                chatWindowStatus[partner_key].headId = messageInfo.id;
            }
        })
    }

    function openMenu(menu) {
        menu.removeClass("w-0 h-0 opacity-0");
        menu.addClass("w-screen h-screen opacity-60");
    }

    function closeMenu(menu) {
        menu.removeClass("w-screen h-screen opacity-60");
        menu.addClass("w-0 h-0 opacity-0");
    }

    function showPopup(id) {
        openMenu($('#overlay'));
        const popup = $(id);
        popup.removeClass("invisible w-0 h-0");
        popup.addClass("w-screen h-screen");
        popup.find('.popup-content').removeClass('scale-0');
        return popup;
    }

    function closePopup(id, resetPopup = null) {
        closeMenu($('#overlay'));
        const popup = $(id);
        popup.addClass("invisible w-0 h-0");
        popup.removeClass("w-screen h-screen");
        popup.find('.popup-content').addClass('scale-0');
        if (resetPopup) {
            resetPopup();
        }
    }

    function showToastNotif(content) {
        const template = $('#toast-notification-template');
        const toast = template.find('.toast-notif').clone('true');
        toast.find('.notif-content').text(content);
        $('body').append(toast);
        toast.focus();
        toast.removeClass('translate-x-full');
        setTimeout(() => {
            toast.addClass('opacity-0 translate-x-full');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 2000);
    }

    function resetAddFriendPopup() {
        const popup = $('#add-friend-popup');
        $('#add-friend-button').addClass('hidden');
        popup.find('.friend-info').addClass('hidden');
        popup.find('.error').addClass('hidden');
        $('#search-friend-form')[0].reset();
    }

    function closeAddFriendPopup() {
        closePopup('#add-friend-popup', resetAddFriendPopup)
    }

    function displayContactInfo(contact, popupID) {
        const popup = $(popupID);
        popup.find('.friend-image').attr('src', `/${contact.image_url}`);
        popup.find('.friend-name').text(contact.name ? contact.name : 'Unknown');
        popup.find('.friend-id').text("ID: " + contact.id);
        popup.find('.friend-info').removeClass('hidden');
    }

    function showAcceptFriendPopup(user) {
        displayContactInfo(user, '#accept-friend-popup');
        $('#accept-friend-button').data("id", user.id);
        showPopup('#accept-friend-popup');
    }

    function showAcceptGroupPopup(group) {
        displayContactInfo(group, '#accept-group-popup');
        $('#accept-group-button').data("id", group.id);
        showPopup('#accept-group-popup');
    }

    function hideAcceptFriendPopup() {
        closePopup('#accept-friend-popup');
    }

    function showMessageModal(content, callback = null) {
        const modal = $('#message-modal');
        modal.find('.message-content').text(content);
        modal.removeClass('hidden');
        if (callback) {
            callback();
        }
    }

    function reloadContent(id) {
        $(id).load("{{route('chat.index')}}" + ` ${id}-reload`);
    }

    function sendMessage(form) {
        const data = new FormData(form[0]);
        const partner_key = currPartnerKey;
        const [partner_type, partner_id] = currPartnerKey.split('-');
        data.append('receiver_id', partner_id);
        data.append('receiver_type', partner_type);
        let message = {
            sender_id: {{Auth::id()}},
            sender_name: "{{Auth::user()->name}}",
            content: form.find('textarea').val(''),
            receiver_id: partner_id,
            receiver_type: partner_type
        }
        // Show message on client side first, show error if request fail later.
        let messageElement = showMessage(message);
        $.ajax({
            url: "{{route('chat.send')}}",
            type: 'POST',
            data: data,
            processData: false,
            contentType: false,
            success: function (res) {
                console.log(res);
                // let content = values.length > 20 ? values.substring(0, 20) + '...' : values;
                // const contact = $(`#contact-${partner_key}`);
                // contact.find('.last-content').text(content);
                // if (res.error) {
                //     const messageError = $('#message-template').find('.send-message-error').clone(true);
                //     messageElement.append(messageError);
                // }
            },
            error: function (xhr) {
                xhr = JSON.parse(xhr.responseText);
                alert(xhr.message);
            }
        })
    }


    $(document).ready(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        Echo.join('chat')
            .joining(function (user) {
                if (friends_id.includes(user.id)) {
                    showToastNotif(`${user.name} is online`);
                }
                $(`.status-user-${user.id}`).addClass('text-green-500');
            })
            .leaving(function (user) {
                $(`.status-user-${user.id}`).removeClass('text-green-500');
            });
        Echo.private("chat.{{Auth::id()}}")
            .listen('FriendListUpdated', function (event) {
                friends_id = event.friends_id;
                reloadContent('#contacts-user');
            })
            .listen('ReceivedFriendRequest', function (event) {
                reloadContent('#friend-requests');
            })
            .listen('ReceivedGroupRequest', function (event) {
                reloadContent('#group-requests');
            })
            .listen('ReceiveChat', function (event) {
                console.log('receive');
                const message = event.message;
                message.receiver_type = event.receiver_type;
                message.sender_name = event.sender_name;
                let partner_key = '';
                if (message['receiver_type'] === 'user') {/* create partner key */
                    partner_key = message.sender_id == {{Auth::id()}} ? `user-${message['receiver_id']}` : `user-${message['sender_id']}`;
                } else {
                    partner_key = `${message['receiver_type']}-${message['receiver_id']}`;
                }
                /*increment unread num*/
                const contact = $(`#contact-${partner_key}`);
                const oldVal = contact.find('.unread_num').text();
                if (parseInt(oldVal) === 0) {
                    contact.find('.unread_num').removeClass('hidden');
                }
                contact.find('.unread_num').text(parseInt(oldVal) + 1);
                /*update last message content*/
                let content = message.content.length > 20 ? message.content.substring(0, 20) + '...' : message.content;
                contact.find('.last-content').text(content);
                showMessage(message);
            });
        /*Autocomplete for chat*/
        chatTextAreaJquery.on("input", function () {
            chatInput = $(this).val();
            let lastWord;
            let words = chatInput.trim().split(" ");
            lastWord = words.pop();
            $(this).autocomplete({
                source: function (request, response) {
                    if (chatInput[chatInput.length - 1] === ' ' || lastWord.length <= 2) {
                        response([]);
                    } else {
                        $.ajax({
                            url: "{{route('chat.autocomplete')}}",
                            method: "GET",
                            data: {
                                query: lastWord
                            },
                            success: function (res) {
                                response(res);
                            },
                            error: function () {
                                response([]);
                            }
                        });
                    }
                },
                minLength: 3,
                select: function (event, ui) {
                    words.push(ui.item.value);
                    $(this).val(words.join(" "));
                    return false;
                }, focus: function (event, ui) {
                    let temp = [...words];
                    temp.push(ui.item.value);
                    let joinValue = temp.join(" ")
                    let lastIndex = joinValue.lastIndexOf(' ') + 1;
                    $(this).val(joinValue);
                    chatTextArea.setSelectionRange(lastIndex + lastWord.length, lastIndex + ui.item.value.length);
                    return false;
                }, autoFocus: true
            })
        });
        chatTextAreaJquery.on("keydown", function (event) {
            if (event.keyCode === 9 && $(this).autocomplete("instance").menu.active) {
                event.preventDefault();
                $(this).autocomplete("instance").menu.active.children("li:first").trigger("click");
                chatTextArea.selectionStart = chatTextArea.selectionEnd;
            }
        });
        chatTextAreaJquery.on("keydown", function (event) {
            if (event.keyCode === $.ui.keyCode.ESCAPE) {
                $(this).val(chatInput); // Restore the original input value
            }
        }).on("focus", function () {
            chatInput = $(this).val(); // Store the original input value on focus
        });
        /*--------*/
        $('#message-tab').on('click', function () {
            if (window.innerWidth >= 1024) {
                $('#sidebar-contact-wrapper').toggleClass('lg:w-1/5 lg:min-w-[20rem]');
            } else {
                $('#sidebar-contact-wrapper').removeClass('lg:w-1/5 lg:min-w-[20rem]');
                $('#sidebar-contact-wrapper').toggleClass('w-0 w-[20rem]');
            }

        })
        $('.toast-notif').on('click', function () {
            $(this).addClass('hidden');
        });
        $('#close-message-modal, .confirm-button').on('click', function () {
            $('#message-modal').addClass('hidden');
        });
        $('#filter-input').on('click', function () {
            $('#search-window').removeClass('scale-y-0 opacity-0');
            $('#close-search-window').removeClass('hidden');
            $('#add-socials').addClass('hidden');
        });
        $('#close-search-window').on('click', function () {
            $('#search-window').addClass('scale-y-0 opacity-0');
            $('#close-search-window').addClass('hidden');
            $('#add-socials').removeClass('hidden');
            $(".search-filter-type[data-target='#search-contact']").trigger('click');
        });
        $('#search-form').on('submit', function (event) {
            event.preventDefault();
            let val = $(this).find('input').val();
            val = val.replace(' ', '%20');
            const contactUrl = "{{route('chat.index')}}" + `?search=${val} #search-contact-reload`;
            const messageUrl = "{{route('chat.index')}}" + `?search=${val} #search-message-reload`;
            $('#search-contact').load(contactUrl, function (response, status, xhr) {
                if (status == "error") {
                    console.log(xhr.statusText);
                }
            });
            $('#search-message').load(messageUrl, function (response, status, xhr) {
                if (status == "error") {
                    console.log(xhr.statusText);
                }
            });
        });
        $('#search-window').on('click', '.page-nav', function () {
            const inc = parseInt($(this).data('inc'));
            const pageTarget = $(this).data('pagetarget');
            const reloadElement = $(`#${pageTarget}-reload`);
            let page = reloadElement.data('page');
            let newPage = parseInt(page) + inc;
            const search = reloadElement.data('search');
            const contactUrl = "{{route('chat.index')}}" + `?search=${search}&${pageTarget}s=${newPage} #${pageTarget}-reload`;
            $(`#${pageTarget}`).load(contactUrl, function (response, status, xhr) {
                if (status == "error") {
                    console.log(xhr.statusText);
                }
            });

        })

        $('.search-filter-type').on('click', function () {
            const target = $($(this).data('target'));
            const active = $('.search-filter-type.border-gray-200');
            active.removeClass('border-gray-200');
            active.addClass('border-gray-600');
            if ($(this).hasClass('border-gray-600')) {
                $(this).addClass('border-gray-200');
                $(this).removeClass('border-gray-600');
            }
            if (target.hasClass('hidden')) {
                $('.search-results').not('hidden').addClass('hidden');
                target.removeClass('hidden');
            }
        })
        $('#user-image').on('click', function () {
            $('#user-info-dropdown').toggleClass('invisible opacity-0');
        });
        // Filter contact or requests
        $('.contact-filter-type').on('click', function () {
            const target = $($(this).data('target'));
            const active = $('.contact-filter-type.border-gray-200');
            active.removeClass('border-gray-200');
            active.addClass('border-gray-600');
            if ($(this).hasClass('border-gray-600')) {
                $(this).addClass('border-gray-200');
                $(this).removeClass('border-gray-600');
            }
            if (target.hasClass('hidden')) {
                $('.contacts').not('hidden').addClass('hidden');
                target.removeClass('hidden');
            }
        });

        $('#your-info-btn').on('click', function () {
            showPopup('#user-detail');
            $('#user-image').trigger('click');
        })
        editUserInfo.on('click', '#edit-user-image', function () {
            $('#edit-user-avatar').trigger('click');
        })
        editUserInfo.on('change', '#edit-user-avatar', function (event) {
            const filePath = URL.createObjectURL(event.target.files[0]);
            const target = $(this).data('target');
            $(`${target}`).attr('src', filePath);
        })
        editUserInfo.on('click', '#toggle-edit-user-info', function () {
            $(this).addClass('hidden');
            $('#edit-user-info input').removeAttr('disabled');
            $('#edit-user-image-wrapper').addClass('hover:cursor-pointer');
            $('#edit-user-info .fa-floppy-disk').removeClass('hidden');
            $('#cancel-user-info').removeClass('hidden');
            $('#confirm-user-info').removeClass('hidden');
        })
        editUserInfo.on('click', '#cancel-user-info', function () {
            $('#edit-user-info input:not(#edit-user-avatar)').each(function () {
                $(this).val($(this).data('oldvalue'));
            })
            $('#edit-user-image').attr('src', $('#edit-user-avatar').data('oldvalue'));
            $('#toggle-edit-user-info').removeClass('hidden');
            $('#edit-user-info .fa-floppy-disk').addClass('hidden');
            $('#your-info-btn input').attr('disabled', 'true');
            $('#edit-user-image-wrapper').removeClass('hover:cursor-pointer');
            $('#cancel-user-info').addClass('hidden');
            $('#confirm-user-info').addClass('hidden');
        })
        editUserInfo.on('submit', '#edit-user-info-form', function (event) {
            event.preventDefault();
            const data = new FormData($(this)[0]);
            $.ajax({
                url: "{{route('user.update')}}",
                data: data,
                type: "POST",
                processData: false,
                contentType: false,
                success: function (res) {
                    console.log(res.user);
                    reloadContent('#edit-user-info');
                    closePopup('#user-detail');
                    $('#user-info-sub-menu img').attr('src', res.user.image_url);
                    $('#user-info-sub-menu .user-name').text(res.user.name);
                    showToastNotif('User info updated');
                },
                error: function (xhr) {
                    const error = JSON.parse(xhr.responseText);
                    console.log(error.message);
                }
            })
        })
        // Send message to server on input
        $('#upload-attachment-btn').on('click', function () {
            $('#attachment-input').trigger('click');
        });
        chatInputForm.on('keydown', function (event) {/* Map Ctrl+Enter to submit action */
            if (event.keyCode === 13 && event.ctrlKey) {
                sendMessage($(this));
                chatTextArea.style.height = 0;
                chatTextArea.style.height = (chatTextArea.scrollHeight) + "px";
            }
        });
        chatInputForm.on('submit', function () {
            event.preventDefault();
            sendMessage($(this));
        });
        // Reset chat window, get new recent messages
        sidebarContactWrapper.on('click', '.contact-info', function () {
            $('.contact-info.bg-gray-900').toggleClass('hover:bg-gray-900 bg-gray-900');
            $(this).toggleClass('hover:bg-gray-900 bg-gray-900');
            const type = $(this).data('type');
            const partner_id = $(this).data('id');
            const partner_key = `${type}-${partner_id}`;
            if (currPartnerKey !== partner_key) {
                $('#chat-header .partner-name').text($(this).data('name'));
                $('#chat-header .partner-id').text(`ID: ${$(this).data('id')}`);
                currPartnerKey = partner_key;
                if (!($(`#${partner_key}`).length)) createChatWindow(partner_key);
                $('.chat-window-content:not(.hidden)').addClass('hidden');
                $(`#${partner_key}`).removeClass('hidden');
                if (!chatWindowStatus[partner_key]) {
                    chatWindowStatus[partner_key] = {
                        "updated": false,
                        "headId": 0,
                        "headChatOwner": 0,
                        "tailChatOwner": 0,
                        "olderMessages": true
                    };
                }
                if (!(chatWindowStatus[partner_key].updated)) {
                    $(`#${partner_key}`).empty();
                    $.ajax({
                        url: "{{route('chat.recent')}}" + `/${type}/${partner_id}`,
                        type: "GET",
                        success: function (res) {
                            const recent_messages = res.recent_messages;
                            if (recent_messages.length) {
                                chatWindowStatus[partner_key].updated = true;
                                chatWindowStatus[partner_key].headId = recent_messages[0].id;
                                chatWindowStatus[partner_key].headChatOwner = (recent_messages[0].sender_id == {{Auth::id()}} ? 0 : recent_messages[0].sender_id);
                                $.each(recent_messages, function (key, value) {
                                    showMessage(value);
                                });
                                if (recent_messages.length < 10) {
                                    chatWindowStatus[partner_key].olderMessages = false;
                                }
                                $('.message', `#${partner_key}`).last()[0].scrollIntoView(false);
                                observer.observe($(`#${partner_key}`).find('.message')[0]);
                            }
                        }
                    })
                }
            }
        });
        // Show remove friends option
        sidebarContactWrapper.on('click', '.contact-info .fa-ellipsis', function (event) {
            event.stopPropagation();
            const options = $(this).siblings('.options');
            const showingOptions = $('.contact-info .options:not(.invisible)');
            if (showingOptions && options.hasClass('invisible')) {
                showingOptions.addClass('scale-0 invisible');
            }
            options.toggleClass('scale-0 invisible');
        });
        // Remove friends or leave group
        sidebarContactWrapper.on('click', '.contact-info .options .remove-contact-btn', function (event) {
            event.stopPropagation();
            const type = $(this).data('type');
            const url = $(this).data('type') === 'user'
                ? "{{route('friend.remove')}}" + `/${$(this).data('id')}`
                : "{{route('group.leave')}}" + `/${$(this).data('id')}`;
            $.ajax({
                url: url,
                type: "GET",
                success: function (res) {
                    type === 'user'
                        ? reloadContent('#contacts-user')
                        : reloadContent('#contacts-group');
                },
                error: function (xhr) {
                    const error = JSON.parse(xhr.responseText);
                    console.log(error.message);
                }
            });
        });
        // Toggle show friend request confirm modal
        sidebarContactWrapper.on('click', '.friend-request', function () {
            const user = {
                id: $(this).data('id'),
                name: $(this).data('name'),
                image_url: $(this).data('image')
            };
            showAcceptFriendPopup(user);
        });
        sidebarContactWrapper.on('click', '.group-request', function () {
            const group = {
                id: $(this).data('id'),
                type: $(this).data('type'),
                name: $(this).data('name'),
                image_url: $(this).data('image')
            };
            showAcceptGroupPopup(group);
        });
        $('#accept-friend-button').on('click', function () {
            const id = $(this).data('id');
            $.ajax({
                url: "{{route('friend.accept')}}" + `/${id}`,
                type: "GET",
                success: function (res) {
                    const messageContent = `You are now friend with ${res.friend.name}`;
                    showMessageModal(messageContent, hideAcceptFriendPopup);
                    reloadContent('#friend-requests');
                    reloadContent('#contacts-user');
                }
            })
        });
        // Toggle add friend popup visibility
        $('#add-friend').on('click', function () {
            showPopup('#add-friend-popup');
        })
        $('.close-popup').on('click', function () {
            closePopup($(this).data('target'));
        });
        // ----
        // Show search friend info
        $('#search-friend-form').on('submit', function (event) {
            event.preventDefault();
            const input = $('#search-friend-input');
            let id = input.val();
            input.empty();
            $.ajax({
                url: "{{route('user.info')}}" + `/${id}`,
                type: "GET",
                success: function (res) {
                    resetAddFriendPopup();
                    displayContactInfo(res.user, '#add-friend-popup');
                    if (res.user.id != {{Auth::id()}}) {
                        $('#add-friend-button').removeClass('hidden');
                    }
                    addFriendId = id;
                },
                error: function (err) {
                    resetAddFriendPopup();
                    $('#add-friend-popup').find('.error').removeClass('hidden');
                }
            })
        })
        $('#add-friend-button').on('click', function () {
            $.ajax({
                url: "{{route('friend.add')}}" + `/${addFriendId}`,
                type: "GET",
                success: function (res) {
                    const messageContent = `You has sent ${res.friend.name} a friend request`
                    showMessageModal(messageContent, closeAddFriendPopup);
                }
            })
        });
        // -----
        //Create and join group
        $('#create-group').on('click', function () {
            showPopup('#create-group-popup');
        });
        $('#group-avatar-wrapper').on('click', function () {
            $('#group-avatar-upload').trigger('click');
        });
        $('#group-avatar-upload').on('change', function (event) {
            const filePath = URL.createObjectURL(event.target.files[0]);
            $('#group-avatar').attr('src', filePath);
        });
        $('#create-group-form').on('submit', function (event) {
            event.preventDefault();
            const data = new FormData($(this)[0]);
            $.ajax({
                url: "{{route('group.store')}}",
                data: data,
                type: "POST",
                processData: false,
                contentType: false,
                success: function (res) {
                    reloadContent('#contacts-group');
                    closePopup('#create-group-popup', function () {
                        $('#create-group-form')[0].reset();
                    })
                },
                error: function (xhr) {
                    const error = JSON.parse(xhr.responseText);
                    console.log(error.message);
                }
            })
        });
        $('#accept-group-button').on('click', function () {
            const id = $(this).data('id');
            $.ajax({
                url: "{{route('group.accept')}}" + `/${id}`,
                type: "GET",
                success: function (res) {
                    const messageContent = `You has joined ${res.group.name} group`;
                    showMessageModal(messageContent, function () {
                        closePopup('#accept-group-popup');
                    });
                    reloadContent('#group-requests');
                    reloadContent('#contacts-group');
                },
                error: function (xhr) {
                    const error = JSON.parse(xhr.responseText);
                    console.log(error.message);
                }
            })
        });
        //     ------

        $('#search-message').on('click', '.search-message-result', function () {
            const contactType = $(this).data('type');
            const contactId = $(this).data('contactid');
            const messageId = $(this).data('messageid');
            const partner_key = `${contactType}-${contactId}`;
            let from;
            let to;
            currPartnerKey = partner_key;
            const contactChatWindow = $(`#${partner_key}`).length ? $(`#${partner_key}`) : createChatWindow(partner_key);
            $('.chat-window-content:not(.hidden)').addClass('hidden');
            contactChatWindow.removeClass('hidden');
            contactChatWindow.find('.loading').removeClass('hidden');
            if (!chatWindowStatus[partner_key]) {
                chatWindowStatus[partner_key] = {
                    "updated": false,
                    "headId": 0,
                    "headChatOwner": 0,
                    "tailChatOwner": 0,
                    "olderMessages": true
                };
            }
            if (chatWindowStatus[partner_key].headId !== 0 && parseInt(chatWindowStatus[partner_key].headId) <= parseInt(messageId)) {
                contactChatWindow.find('.loading').addClass('hidden');
                const message = $(`#${contactType}-message-${messageId}`);
                message[0].scrollIntoView(false);
                message.addClass('bg-yellow-500');
                setTimeout(function () {
                    message.removeClass('bg-yellow-500');
                }, 300);
                return;
            }
            if (chatWindowStatus[partner_key].headId === 0) {
                to = 0;
            }
            if (parseInt(chatWindowStatus[partner_key].headId) > parseInt(messageId)) {
                to = parseInt(chatWindowStatus[partner_key].headId);
            }
            from = parseInt(messageId) - 10;
            $.ajax({
                url: "{{route('user.message.search')}}" + `/${contactType}/${contactId}/${from}/${to}`,
                type: "GET",
                success: function (res) {
                    if (res.messages.length) {
                        const messages = res.messages;
                        buildMessageUpward(messages, partner_key);
                        if (chatWindowStatus[partner_key].olderMessages) {/*Only add intersection event again if there's still messages to be loaded */
                            observer.observe($(`#${partner_key}`).find('.message').first()[0]);
                        }
                    }
                    contactChatWindow.find('.loading').addClass('hidden');
                    const message = $(`#${contactType}-message-${messageId}`);
                    message[0].scrollIntoView(false);
                    message.addClass('bg-yellow-500');
                    setTimeout(function () {
                        message.focus();
                        message.removeClass('bg-yellow-500');
                    }, 500);
                },
                error: function (xhr) {
                    xhr = JSON.parse(xhr.responseText);
                    alert(xhr.message);
                }
            })

        })
    })
</script>
</body>
</html>
