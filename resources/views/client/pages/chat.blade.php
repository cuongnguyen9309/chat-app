@php
    use Illuminate\Support\Facades\Auth;$messages = array('Test message 1', 'Test message 2');
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
            class="user-image w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center ">
            A
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
</div>


<div class="flex flex-row h-full">
    <x-client.sidebar :friends="$friends" :joined_groups="$joined_groups"/>


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

</div>

<script>
    {{--    chatOwner:
            USER = 0
             OTHER = sender_id--}}
    let currTailChatOwner = 0;
    let currHeadChatOwner = 0;
    let newChat = true;
    let currPartner = {
        type: '',
        partner_id: 0
    };
    let currChatChannel = '';

    const tx = document.getElementsByTagName("textarea");
    for (let i = 0; i < tx.length; i++) {
        tx[i].setAttribute("style", "height:" + (tx[i].scrollHeight) + "px;overflow-y:hidden;");
        tx[i].addEventListener("input", OnInput, false);
    }

    function OnInput() {
        this.style.height = 0;
        this.style.height = (this.scrollHeight) + "px";
    }

    function createMessageBlock(chatOwner) {
        const template = $('#message-template');
        const messageBlock = template.find('.message-block').clone(true);
        if (chatOwner) {
            messageBlock.addClass('flex-row-reverse');
            messageBlock.find('.user-image').addClass('ml-5');
        } else {
            messageBlock.find('.user-image').addClass('mr-5');
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

    function showMessageElementOnReceive(messageInfo) {
        const sender_id = messageInfo.sender_id == {{Auth::id()}} ? 0 : messageInfo.sender_id;
        console.log(messageInfo);
        if (currTailChatOwner == sender_id && !newChat) {
            const message = createMessage(messageInfo.content, currTailChatOwner);
            const messageBlock = $('.message-block').last();
            messageBlock.find('.messages-wrapper').append(message);
        } else {
            if (newChat) {
                newChat = false;
            }
            currTailChatOwner = sender_id;
            const message = createMessage(messageInfo.content, currTailChatOwner, messageInfo.sender_name);
            const messageBlock = createMessageBlock(currTailChatOwner);
            messageBlock.find('.messages-wrapper').append(message);
            $('#chat-window').append(messageBlock);
        }
    }

    $(document).ready(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $('#user-image').on('click', function () {
            $('#user-info-dropdown').toggleClass('invisible opacity-0');
        });
        $('#chat-input-form').on('submit', function () {
            event.preventDefault();
            let values = $(this).find('textarea').val();

            $.ajax({
                url: "{{route('chat.send')}}",
                data: {
                    input: values,
                    receiver_id: currPartner.partner_id,
                    receiver_type: currPartner.type
                },
                type: 'POST',
                success: function (res) {
                    // console.log(res.message);
                }
            })
        });
        $('.contact-filter-type').on('click', function () {
            const target = $($(this).data('target'));
            if (target.hasClass('hidden')) {
                $('.contacts').not('hidden').addClass('hidden');
                target.removeClass('hidden');
            }
        });
        $('.contact-info').on('click', function () {
            const type = $(this).data('type');
            const partner_id = $(this).attr('id');
            if (currPartner.type !== type || currPartner.partner_id !== partner_id) {
                Echo.leaveChannel(currChatChannel);
                currPartner.type = type;
                currPartner.partner_id = partner_id;
                $('#chat-window').empty();
                newChat = true;
                $.ajax({
                    url: "{{route('chat.recent')}}" + `/${type}/${partner_id}`,
                    type: "GET",
                    success: function (res) {
                        const recent_messages = res.recent_messages;
                        $.each(recent_messages, function (key, value) {
                            showMessageElementOnReceive(value);
                        });

                        let channel = '';
                        if (currPartner.type === 'user') {
                            let idArray = [parseInt({{Auth::id()}}), parseInt(currPartner.partner_id)].sort();
                            channel = `chat-user_${idArray[1]}_${idArray[0]}`;
                        } else {
                            channel = `chat-group_${currPartner.partner_id}`;
                        }

                        currChatChannel = channel;
                        Echo.channel(channel)
                            .listen('SendChatEvent', function (event) {
                                console.log(event.channel);
                                showMessageElementOnReceive(event.message);
                            });
                    }
                })
            }
        });
    })
</script>
</body>
</html>
