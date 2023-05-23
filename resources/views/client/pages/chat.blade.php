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
    <script src="https://kit.fontawesome.com/c2fe055d35.js" crossorigin="anonymous"></script>
    <script src="{{asset('js/jquery-3.7.0.min.js')}}"></script>
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
    <div class="message text-black max-w-7xl my-4 bg-gray-200 py-3 px-2">
    </div>
    {{--    UserInfo Tag. Append to new message when chat owner change--}}
    <p class="user-name leading-8 text-xs text-gray-500"></p>
    <p class="send-message-error text-red-600 text-xs">Message sending failed</p>
</div>
<div class="hidden" id="chat-window-content-template">
    <div class="hidden chat-window-content w-full h-full">
    </div>
</div>

<div class="flex flex-row h-full">
    <x-client.sidebar :friends="$friends"
                      :joined_groups="$joined_groups"
                      :friendRequests="$friendRequests"
                      :groupRequests="$groupRequests"
    />


    <div class="w-full flex-1 flex flex-col max-h-full overflow-hidden">
        <x-client.header/>


        <div id="chat-window" class="chat-window overflow-y-scroll p-5 flex-1">

        </div>


        <form class="border-gray-200 px-4 pt-4 mb-2 sm:mb-3" id="chat-input-form">
            @csrf
            <div
                class="relative flex w-full bg-gray-200 rounded-md">
                <textarea placeholder="Enter your message"
                          class="w-full resize-none bg-gray-200 focus:outline-none focus:placeholder-gray-400 text-gray-600 rounded-md placeholder-gray-600 py-3 px-5"
                          rows="1"></textarea>
                <div class=" items-center inset-y-0 flex">
                    <button
                        class="flex items-center justify-center rounded-full h-10 w-10 transition duration-500 ease-in-out text-gray-500 hover:bg-gray-300 focus:outline-none">
                        <i class="fa-solid fa-paperclip"></i>
                    </button>
                    <button
                        class="flex items-center justify-center rounded-full h-10 w-10 transition duration-500 ease-in-out text-gray-500 hover:bg-gray-300 focus:outline-none">
                        <i class="fa-solid fa-camera"></i>
                    </button>
                    <button
                        class="flex items-center justify-center rounded-full h-10 w-10 transition duration-500 ease-in-out text-gray-500 hover:bg-gray-300 focus:outline-none">
                        <i class="fa-regular fa-face-smile"></i>
                    </button>
                    <button type="submit"
                            class="flex items-center justify-center rounded-2xl py-3 px-3 mr-2   transition duration-500 ease-in-out text-white bg-green-400 hover:bg-blue-400 focus:outline-none">
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
        <form id="search-friend-form" action="" method="POST">
            <input id="search-friend-input" type="text" placeholder="Friend ID"
                   class="bg-none w-full focus:outline-none text-black p-1 rounded-md mt-2" autocomplete="off">
        </form>
        <x-client.friend-info/>
        <button id="add-friend-button"
                class="hidden text-center bold bg-green-400 mt-5 p-2 rounded-xl">Add friend
        </button>
        <div class="error hidden text-center">Invalid User ID</div>
    </x-client.popup>
    <x-client.popup popupId="accept-friend-popup" title="Friend Request">
        <x-client.friend-info/>
        <p class="mt-5">Has sent you a friend request</p>
        <button id="accept-friend-button" data-id="0"
                class="text-center bold bg-green-400 mt-5 p-2 rounded-xl">Accept
        </button>
    </x-client.popup>
    <x-client.popup popupId="accept-group-popup" title="Group Invite">
        <x-client.friend-info/>
        <p class="mt-5">Has invite you to join</p>
        <button id="accept-group-button" data-id="0"
                class="text-center bold bg-green-400 mt-5 p-2 rounded-xl">Accept
        </button>
    </x-client.popup>
    <x-client.popup popupId="create-group-popup" title="Create new group">
        <form id="create-group-form" action="" class="group-name ml-5" enctype="multipart/form-data">
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
    </x-client.popup>
    <x-client.message-modal/>
</div>

<script>
    {{--    chatOwner:
            USER = 0
             OTHER = sender_id--}}
    let currPartnerKey = '';
    let addFriendId = 0;
    let chatWindowStatus = [];
    // HTML element reuse
    const sidebarContactWrapper = $('#sidebar-contact-wrapper');
    const chatInputForm = $('#chat-input-form');
    const tx = document.getElementsByTagName("textarea");
    for (let i = 0; i < tx.length; i++) {
        tx[i].setAttribute("style", "height:" + (tx[i].scrollHeight) + "px;overflow-y:hidden;");
        tx[i].addEventListener("input", OnInput, false);
    }

    function OnInput() {
        this.style.height = 0;
        this.style.height = (this.scrollHeight) + "px";
    }

    function createChatWindow(partner_key) {
        const template = $('#chat-window-content-template');
        const chatWindowContent = template.find('.chat-window-content').clone(true);
        chatWindowContent.attr('id', partner_key);
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

    function createMessage(content, chatOwner, newChatOwnerName = '') {
        const template = $('#message-template');
        const message = template.find('.message').clone(true);
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
        if (messageInfo['receiver_type'] === 'user') {
            partner_key = messageInfo.sender_id == {{Auth::id()}} ? `user-${messageInfo['receiver_id']}` : `user-${messageInfo['sender_id']}`;
        } else {
            partner_key = `${messageInfo['receiver_type']}-${messageInfo['receiver_id']}`;
        }
        let message;
        if (chatWindowStatus[partner_key]) {
            const sender_id = messageInfo.sender_id == {{Auth::id()}} ? 0 : messageInfo.sender_id;
            let messageBlock = '';
            if (chatWindowStatus[partner_key].tailChatOwner == sender_id) {
                message = createMessage(messageInfo.content, chatWindowStatus[partner_key].tailChatOwner);
                if ($('.message-block', `#${partner_key}`).length) {
                    messageBlock = $('.message-block', `#${partner_key}`).last();
                } else {
                    messageBlock = createMessageBlock(messageInfo);
                    $(`#${partner_key}`).append(messageBlock);
                }
            } else {
                chatWindowStatus[partner_key].tailChatOwner = sender_id;
                message = createMessage(messageInfo.content, chatWindowStatus[partner_key].tailChatOwner, messageInfo.sender_name);
                messageBlock = createMessageBlock(messageInfo);
                $(`#${partner_key}`).append(messageBlock);
            }
            messageBlock.find('.messages-wrapper').append(message);
        }
        return message;
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
        let values = form.find('textarea').val();
        form.find('textarea').val('');
        const [partner_type, partner_id] = currPartnerKey.split('-');
        let message = {
            sender_id: {{Auth::id()}},
            sender_name: "{{Auth::user()->name}}",
            content: values,
            receiver_id: partner_id,
            receiver_type: partner_type
        }
        // Show message on client side first, show error if request fail later.
        let messageElement = showMessage(message);
        $.ajax({
            url: "{{route('chat.send')}}",
            data: {
                input: values,
                receiver_id: partner_id,
                receiver_type: partner_type
            },
            type: 'POST',
            success: function (res) {
                if (res.error) {
                    const messageError = $('#message-template').find('.send-message-error').clone(true);
                    messageElement.append(messageError);
                }
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
        Echo.channel("{{Auth::id()}}")
            .listen('FriendListUpdated', function (event) {
                reloadContent('#contacts-user');
            })
            .listen('ReceivedFriendRequest', function (event) {
                reloadContent('#friend-requests');
            })
            .listen('ReceivedGroupRequest', function (event) {
                reloadContent('#group-requests');
            })
            .listen('ReceiveChat', function (event) {
                const message = event.message;
                message.receiver_type = event.receiver_type;
                message.sender_name = event.sender_name;
                showMessage(message);
            });
        $('#close-message-modal, .confirm-button').on('click', function () {
            $('#message-modal').addClass('hidden');
        });
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
        // Send message to server on input
        chatInputForm.on('keydown', function (event) {
            if (event.keyCode === 13 && event.ctrlKey) {
                sendMessage($(this));
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
                        "headTime": 0,
                        "headChatOwner": 0,
                        "tailChatOwner": 0
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
                                chatWindowStatus[partner_key].headTime = recent_messages[0].created_at;
                                chatWindowStatus[partner_key].headChatOwner = recent_messages[0].sender_id;
                                $.each(recent_messages, function (key, value) {
                                    showMessage(value);
                                });
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
                    console.log(res.data);
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
    })
</script>
</body>
</html>
