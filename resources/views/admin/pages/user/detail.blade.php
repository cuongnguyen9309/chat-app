@php use Illuminate\Support\Facades\Auth;use Illuminate\Support\Str;use const http\Client\Curl\AUTH_ANY; @endphp
@extends('admin.layout.master')
@section('content')
    <div class="hidden" id="table-template">
        <table>
            <x-admin.table.thead/>
            <tr class="tr">
            </tr>
            <x-admin.table.td/>
        </table>
    </div>
    <div class="top-row flex text-gray-300">
        <div id="user-info" class="min-w-[30rem]">
            <div id="user-info-reload">
                <form id="user-info-form" action=""
                      class="user-info-wrapper flex bg-gray-700 shadow p-5 rounded-xl"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="left w-1/2 mr-11 mt-5">
                        <div class="image-wrapper w-24 h-24 mb-4 relative">
                            <img id="user-avatar-upload" class="w-full h-full  object-cover rounded-full"
                                 src="{{asset($user->image_url)}}"
                                 alt="">
                            <i
                                id="avatar-editable"
                                class="invisible text-gray-300 fa-solid fa-floppy-disk absolute bottom-0 right-0 origin-center "></i>
                        </div>
                        <input id="user-avatar" type="file" name="image" disabled class="hidden">
                        <h1 class="font-bold text-xl text-green-500">{{Str::limit($user->name,20,'...')}}</h1>
                    </div>
                    <div class="right flex flex-col flex-1">
                        <div class="button-wrapper">
                            <button
                                type="button"
                                id="edit-toggle-btn"
                                class="ml-auto font-semibold bg-yellow-400 hover:bg-yellow-500 text-black py-1 px-4 duration-200 rounded shadow-xl">
                                Edit
                            </button>
                            <button
                                type="submit"
                                id="save-edit-btn"
                                class="ml-auto font-semibold bg-blue-500 hover:bg-blue-600 text-black py-1 px-4 duration-200 rounded shadow-xl">
                                Save
                            </button>
                        </div>
                        <div class="user-info-block mt-2">
                            <p class="font-bold">ID:</p>
                            <p>{{$user->id}}</p>
                        </div>
                        <div class="user-info-block mt-2">
                            <label for="user-name" class="font-bold">Name:</label>
                            <input class="disabled:bg-transparent bg-gray-600 focus:outline-none block w-full"
                                   name="name"
                                   id="user-name" disabled
                                   autocomplete="off"
                                   value="{{$user->name}}"/>
                        </div>
                        <div class="user-info-block mt-2">
                            <label for="email" class="font-bold">Email:</label>
                            <input class="disabled:bg-transparent bg-gray-600 focus:outline-none block w-full"
                                   name="email"
                                   id="email" disabled
                                   value="{{$user->email}}"/>
                        </div>
                        <div class="user-info-block mt-2">
                            <p class="font-bold">Created at:</p>
                            <p>{{$user->created_at}}</p>
                        </div>
                        <div class="user-info-block mt-2">
                            <p class="font-bold">Updated at:</p>
                            <p>{{$user->updated_at}}</p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="friend-list ml-5 bg-gray-700 flex-1 p-5 rounded-xl">
            <header class="flex">
                <h2 class="font-bold text-xl">Friend List:</h2>
                <button
                    id="add-friend-btn"
                    class="ml-auto font-semibold bg-blue-500 hover:bg-blue-600 text-gray-800 py-1 px-4 duration-200 rounded shadow-xl">
                    Add Friend
                </button>
            </header>
            <div id="friend-list">
                <div id="friend-list-reload">
                    <x-admin.table.table>
                        <x-admin.table.thead>
                            <x-admin.table.th>ID</x-admin.table.th>
                            <x-admin.table.th>Name</x-admin.table.th>
                            <x-admin.table.th>Email</x-admin.table.th>
                            <x-admin.table.th>Remove Friend</x-admin.table.th>
                        </x-admin.table.thead>
                        <tbody>
                        @foreach($friends as $friend)
                            <x-admin.table.tr>
                                <x-admin.table.td :odd="$loop->odd">{{$friend->id}}</x-admin.table.td>
                                <x-admin.table.td :odd="$loop->odd">
                                    <a href="{{route('admin.user.show',$friend->id)}}" class="flex items-center">
                                        <div class="w-10 h-10 image-wrapper mr-3">
                                            <img class="w-full h-full  object-cover rounded-full"
                                                 src="{{asset($friend->image_url)}}"
                                                 alt="">
                                        </div>
                                        {{Str::limit($friend->name,25,'...')}}
                                    </a>
                                </x-admin.table.td>
                                <x-admin.table.td :odd="$loop->odd">{{$friend->email}}</x-admin.table.td>
                                <x-admin.table.td :odd="$loop->odd">
                                    <button
                                        data-id="{{$friend->id}}"
                                        class="remove-friend font-semibold bg-red-400 hover:bg-red-500 text-gray-800 py-1 px-4 duration-200 rounded shadow-xl">
                                        Remove
                                    </button>
                                </x-admin.table.td>
                            </x-admin.table.tr>
                        @endforeach
                        </tbody>
                    </x-admin.table.table>
                    <div
                        class="paginate flex justify-center mt-3">{{ $friends->appends([
                                                                    "messages"=>$messages->currentPage(),
                                                                    "groups"=>$groups->currentPage()])->links() }}</div>
                </div>
            </div>

        </div>

    </div>
    <div class="bottom-row">
        <div class="bg-gray-700 p-5 rounded-xl mt-2">
            <header class="flex">
                <h2 class="font-bold text-xl">Joined Groups:</h2>
                <button
                    id="add-group-btn"
                    class="ml-auto font-semibold bg-blue-500 hover:bg-blue-600 text-gray-800 py-1 px-4 duration-200 rounded shadow-xl">
                    Add Group
                </button>
            </header>
            <div id="group-list">
                <div id="group-list-reload">
                    <x-admin.table.table>
                        <x-admin.table.thead>
                            <x-admin.table.th>ID</x-admin.table.th>
                            <x-admin.table.th>Name</x-admin.table.th>
                            <x-admin.table.th>Admin</x-admin.table.th>
                            <x-admin.table.th>Remove Group</x-admin.table.th>
                        </x-admin.table.thead>
                        <tbody>
                        @foreach($groups as $group)
                            <x-admin.table.tr>
                                <x-admin.table.td :odd="$loop->odd">{{$group->id}}</x-admin.table.td>
                                <x-admin.table.td :odd="$loop->odd">
                                    <a href="{{route('admin.group.show',$group->id)}}" class="flex items-center">
                                        <div class="w-10 h-10 image-wrapper mr-3">
                                            <img class="w-full h-full  object-cover rounded-full"
                                                 src="{{asset($group->image_url)}}"
                                                 alt="">
                                        </div>
                                        {{Str::limit($group->name,25,'...')}}
                                    </a>
                                </x-admin.table.td>
                                <x-admin.table.td :odd="$loop->odd">
                                    <a href="{{route('admin.user.show',$group->admin->id)}}" class="flex items-center">
                                        <div class="w-10 h-10 image-wrapper mr-3">
                                            <img class="w-full h-full  object-cover rounded-full"
                                                 src="{{asset($group->admin->image_url)}}"
                                                 alt="">
                                        </div>
                                        {{Str::limit($group->admin->name,25,'...')}}
                                    </a>
                                </x-admin.table.td>
                                <x-admin.table.td :odd="$loop->odd">
                                    <button
                                        data-id="{{$group->id}}"
                                        class="remove-group font-semibold bg-red-400 hover:bg-red-500 text-gray-800 py-1 px-4 duration-200 rounded shadow-xl">
                                        Remove
                                    </button>
                                </x-admin.table.td>
                            </x-admin.table.tr>
                        @endforeach
                        </tbody>
                    </x-admin.table.table>
                    <div
                        class="paginate flex justify-center mt-3">{{ $groups->appends([
                                                                        "messages"=>$messages->currentPage(),
                                                                        "$friends"=>$friends->currentPage()])->links() }}</div>
                </div>
            </div>
        </div>
        <div id="message-list" class="bg-gray-700 p-5 rounded-xl mt-2">
            <div id="message-list-reload">
                <h2 class="font-bold text-xl">Message List:</h2>
                <x-admin.table.table>
                    <x-admin.table.thead>
                        <x-admin.table.th>ID</x-admin.table.th>
                        <x-admin.table.th>Content</x-admin.table.th>
                        <x-admin.table.th>Sender</x-admin.table.th>
                        <x-admin.table.th>Receiver</x-admin.table.th>
                        <x-admin.table.th>Type</x-admin.table.th>
                        <x-admin.table.th>Created at</x-admin.table.th>
                        <x-admin.table.th>Updated at</x-admin.table.th>
                        <x-admin.table.th>Status</x-admin.table.th>

                    </x-admin.table.thead>
                    <tbody>
                    @foreach($messages as $message)
                        <x-admin.table.tr>
                            <x-admin.table.td :odd="$loop->odd">{{$message->id}}</x-admin.table.td>
                            <x-admin.table.td
                                :odd="$loop->odd">{{Str::limit($message->content,100,'...') }}</x-admin.table.td>
                            <x-admin.table.td :odd="$loop->odd"><a
                                    href="{{route('admin.user.show',$message->sender_id)}}"
                                    class="{{$message->sender_id == $user->id ? 'text-green-500' : ''}}">{{$message->sender_name}}</a>
                            </x-admin.table.td>
                            <x-admin.table.td :odd="$loop->odd"><a
                                    href="{{route('admin.user.show',$message->receiver_id)}}"
                                    class="{{($message->receiver_id == $user->id && $message->type === 'personal') ? 'text-green-500' : ''}}">{{$message->receiver_name}}</a>
                            </x-admin.table.td>
                            <x-admin.table.td :odd="$loop->odd">{{$message->type}}</x-admin.table.td>
                            <x-admin.table.td :odd="$loop->odd">{{$message->created_at}}</x-admin.table.td>
                            <x-admin.table.td :odd="$loop->odd">{{$message->updated_at}}</x-admin.table.td>
                            <x-admin.table.td
                                :odd="$loop->odd">{{$message->deleted_at ? 'deleted' : 'available'}}</x-admin.table.td>
                            <x-admin.table.td :odd="$loop->odd">
                                <button
                                    data-id="{{$message->id}}"
                                    data-type="{{$message->type}}"
                                    data-side="{{($message->receiver_id == $user->id && $message->type === 'personal') ? 'receive' : 'sent'}}"
                                    class="{{$message->deleted_at ? 'hidden' : ''}} remove-message font-semibold bg-red-400 hover:bg-red-500 text-gray-800 py-1 px-4 duration-200 rounded shadow-xl">
                                    Remove
                                </button>
                                <button
                                    data-id="{{$message->id}}"
                                    data-type="{{$message->type}}"
                                    data-side="{{($message->receiver_id == $user->id && $message->type === 'personal') ? 'receive' : 'sent'}}"
                                    class="{{$message->deleted_at ? '' : 'hidden'}} restore-message font-semibold bg-blue-400 hover:bg-blue-500 text-gray-800 py-1 px-4 duration-200 rounded shadow-xl">
                                    Restore
                                </button>
                            </x-admin.table.td>
                        </x-admin.table.tr>
                    @endforeach
                    </tbody>
                </x-admin.table.table>
                <div
                    class="paginate flex justify-center mt-3">{{$messages->appends([
                                                                                "friends"=>$friends->currentPage(),
                                                                                "groups"=>$groups->currentPage()])->links() }}</div>
            </div>
        </div>
    </div>
    <div id="overlay"
         class="overlay fixed top-0 left-0 z-60 w-0 h-0 bg-gray-900 opacity-60">
    </div>
    <x-client.popup popupId="add-friend-popup" title="Add Friend">
        <form id="search-friend-form" action="" method="POST">
            <input id="search-friend-input" type="text" placeholder="Friend ID"
                   class="bg-none w-full focus:outline-none text-black p-1 rounded-md mt-2" autocomplete="off">
        </form>
        <x-admin.table.table id="add-friend-table">
            <tbody>
            </tbody>
        </x-admin.table.table>
        <div class="button-wrapper flex">
            <button
                id="submit-add-friend-list"
                class="ml-auto mt-2 font-semibold bg-blue-500 hover:bg-blue-600 text-gray-800 py-1 px-4 transition-colors duration-200 rounded shadow-xl">
                Add Friend
            </button>
        </div>
    </x-client.popup>
    <x-client.popup popupId="add-group-popup" title="Add Group">
        <form id="search-group-form" action="" method="POST">
            <input id="search-group-input" type="text" placeholder="Group ID"
                   class="bg-none w-full focus:outline-none text-black p-1 rounded-md mt-2" autocomplete="off">
        </form>
        <x-admin.table.table id="add-group-table">
            <tbody>
            </tbody>
        </x-admin.table.table>
        <div class="button-wrapper flex">
            <button
                id="submit-add-group-list"
                class="ml-auto mt-2 font-semibold bg-blue-500 hover:bg-blue-600 text-gray-800 py-1 px-4 transition-colors duration-200 rounded shadow-xl">
                Join Group
            </button>
        </div>
    </x-client.popup>
@endsection
@section('js-custom')
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        let addFriendList = [];
        let addGroupList = [];
        let friends_id = {{$friends->pluck('id')}};
        let groups_id = {{$groups->pluck('id')}};
        const userInfo = $('#user-info');
        const messageList = $('#message-list');

        function reloadContent(id) {
            $(id).load("{{route('admin.user.show',$user->id)}}" + ` ${id}-reload`);
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

        function createTd(content) {
            const td = $('#table-template').find('.td').clone('true');
            td.text(content);
            return td;
        }

        userInfo.on('click', '#user-avatar-upload', function () {
            $('#user-avatar').trigger('click');
        });
        userInfo.on('change', '#user-avatar', function (event) {
            const filePath = URL.createObjectURL(event.target.files[0]);
            $('#user-avatar-upload').attr('src', filePath);
        });
        userInfo.on('click', '#edit-toggle-btn', function () {
            $('#user-info-form input').attr('disabled', (_, attr) => !attr);
            $('#avatar-editable').toggleClass('invisible');
            $('#user-avatar-upload').toggleClass('hover:cursor-pointer');
        });
        userInfo.on('submit', '#user-info-form', function (event) {
            event.preventDefault();
            const formData = new FormData($(this)[0]);
            formData.append('_method', 'PUT');
            console.log(Array.from(formData));
            $.ajax({
                url: "{{route('admin.user.update',$user->id)}}",
                data: formData,
                type: "POST",
                processData: false,
                contentType: false,
                success: function (res) {
                    console.log(res);
                    reloadContent('#user-info');
                },
                error: function (xhr) {
                    xhr = JSON.parse(xhr.responseText);
                    alert(xhr.message);
                }
            })
        });
        $('#add-friend-btn').on('click', function () {
            showPopup('#add-friend-popup');
        });
        $('.close-popup').on('click', function () {
            closePopup($(this).data('target'));
        });
        $('#search-friend-form').on('submit', function (event) {
            event.preventDefault();
            const input = $('#search-friend-input');
            let id = input.val();
            input.empty();
            if (friends_id.includes(parseInt(id))) {
                alert(`USER_ID ${id} is already a friend`);
            }
            if (!addFriendList.includes(parseInt(id)) && !friends_id.includes(parseInt(id))) {
                $.ajax({
                    url: "{{route('user.info')}}" + `/${id}`,
                    type: "GET",
                    success: function (res) {
                        const user = res.user;
                        addFriendList.push(user.id);
                        const template = $('#table-template');
                        const tr = template.find('.tr').clone(true);
                        tr.attr('id', `friend-${user.id}`);
                        const name = createTd(user.name);
                        const email = createTd(user.email);
                        const id = createTd(user.id);
                        const btn = createTd("")
                        tr.append(id, name, email, btn);
                        tr.append(`<button data-id=${user.id} class='remove-add-friend ml-auto font-semibold bg-red-500 ` +
                            "hover:bg-red-600 text-gray-800 py-1 px-4 " +
                            "duration-200 rounded shadow-xl'>X</button>")
                        $('#add-friend-table').find('tbody').append(tr);
                    },
                    error: function (xhr) {
                        xhr = JSON.parse(xhr.responseText);
                        const error = xhr.message;
                        alert(error);
                    }
                })
            }
        });
        $('#add-friend-table').on('click', '.remove-add-friend', function () {
            const id = $(this).data('id');
            $(`#friend-${id}`).remove();
            addFriendList = addFriendList.filter(item => item !== id);
            console.log(addFriendList);
        });
        $('#submit-add-friend-list').on('click', function () {
            if (addFriendList.length) {
                $.ajax({
                    url: "{{route('admin.friend.add')}}",
                    type: "POST",
                    data: {
                        addFriendList: addFriendList,
                        user_id: "{{$user->id}}"
                    },
                    success: function (res) {
                        addFriendList = [];
                        reloadContent('#friend-list');
                        $('#add-friend-table').find('tbody').empty();
                    },
                    error: function (xhr) {
                        xhr = JSON.parse(xhr.responseText);
                        alert(xhr.message);
                    }
                })
            }
        });
        $('#friend-list').on('click', '.remove-friend', function () {
            const id = $(this).data('id');
            const url = "{{route('admin.friend.remove',['userId'=>$user->id])}}" + `/${id}`;
            console.log(url);
            $.ajax({
                url: url,
                type: "GET",
                success: function (res) {
                    friends_id = res.friends_id;
                    reloadContent('#friend-list');
                },
                error: function (xhr) {
                    xhr = JSON.parse(xhr.responseText);
                    alert(xhr.message);
                }
            })
        });
        $('#group-list').on('click', '.remove-group', function () {
            const id = $(this).data('id');
            $.ajax({
                url: "{{route('admin.user.leave.group',["userId"=>$user->id])}}" + `/${id}`,
                type: "GET",
                success: function (res) {
                    groups_id = res.groups_id;
                    reloadContent('#group-list');
                },
                error: function (xhr) {
                    xhr = JSON.parse(xhr.responseText);
                    alert(xhr.message);
                }
            })
        })
        $('#search-group-form').on('submit', function (event) {
            event.preventDefault();
            const input = $('#search-group-input');
            let id = input.val();
            input.empty();
            if (groups_id.includes(parseInt(id))) {
                alert(`User already a member of GROUP ${id}`);
            } else if (!addGroupList.includes(parseInt(id))) {
                $.ajax({
                    url: "{{route('group.info')}}" + `/${id}`,
                    type: "GET",
                    success: function (res) {
                        const group = res.group;
                        addGroupList.push(group.id);
                        const template = $('#table-template');
                        const tr = template.find('.tr').clone(true);
                        tr.attr('id', `group-${group.id}`);
                        const name = createTd(group.name);
                        const admin = createTd(group.admin.name);
                        const id = createTd(group.id);
                        const btn = createTd("")
                        tr.append(id, name, admin, btn);
                        tr.append(`<button data-id=${group.id} class='remove-add-group ml-auto font-semibold bg-red-500 ` +
                            "hover:bg-red-600 text-gray-800 py-1 px-4 " +
                            "duration-200 rounded shadow-xl'>X</button>")
                        $('#add-group-table').find('tbody').append(tr);
                    },
                    error: function (xhr) {
                        xhr = JSON.parse(xhr.responseText);
                        const error = xhr.message;
                        alert(error);
                    }
                })
            }
        });
        $('#add-group-btn').on('click', function () {
            showPopup('#add-group-popup');
        });
        $('#submit-add-group-list').on('click', function () {
            if (addGroupList.length) {
                $.ajax({
                    url: "{{route('admin.user.join.group')}}",
                    type: "POST",
                    data: {
                        addGroupList: addGroupList,
                        user_id: "{{$user->id}}"
                    },
                    success: function (res) {
                        console.log(res);
                        addGroupList = [];
                        reloadContent('#group-list');
                        $('#add-group-table').find('tbody').empty();
                    },
                    error: function (xhr) {
                        xhr = JSON.parse(xhr.responseText);
                        alert(xhr.message);
                    }
                })
            }
        });

        messageList.on('click', '.remove-message', function () {
            const id = $(this).data('id');
            const type = $(this).data('type');
            $.ajax({
                url: "{{route('admin.message.remove')}}" + `/${type}/${id}`,
                type: "GET",
                success: function (res) {
                    reloadContent('#message-list');
                },
                error: function (xhr) {
                    xhr = JSON.parse(xhr.responseText);
                    alert(xhr.message);
                }
            })
        });
        messageList.on('click', '.restore-message', function () {
            const id = $(this).data('id');
            const type = $(this).data('type');
            $.ajax({
                url: "{{route('admin.message.restore')}}" + `/${type}/${id}`,
                type: "GET",
                success: function (res) {
                    reloadContent('#message-list');
                },
                error: function (xhr) {
                    xhr = JSON.parse(xhr.responseText);
                    alert(xhr.message);
                }
            })
        })
    </script>
@endsection
