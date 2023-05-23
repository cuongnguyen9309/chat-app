@php use Illuminate\Support\Str; @endphp
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
        <div id="group-info" class="min-w-[30rem]">
            <div id="group-info-reload">
                <form id="group-info-form" action=""
                      class="group-info-wrapper flex bg-gray-700 shadow p-5 rounded-xl"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="left w-1/2 mr-11 mt-5">
                        <div class="image-wrapper w-24 h-24 mb-4 relative">
                            <img id="group-avatar-upload" class="w-full h-full  object-cover rounded-full"
                                 src="{{asset($group->image_url)}}"
                                 alt="">
                            <i
                                id="avatar-editable"
                                class="invisible text-gray-300 fa-solid fa-floppy-disk absolute bottom-0 right-0 origin-center "></i>
                        </div>
                        <input id="group-avatar" type="file" name="image" disabled class="hidden">
                        <h1 class="font-bold text-xl text-green-500">{{Str::limit($group->name,20,'...')}}</h1>
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
                        <div class="group-info-block mt-2">
                            <p class="font-bold">ID:</p>
                            <p>{{$group->id}}</p>
                        </div>
                        <div class="group-info-block mt-2">
                            <label for="group-name" class="font-bold">Name:</label>
                            <input class="disabled:bg-transparent bg-gray-600 focus:outline-none block w-full"
                                   name="name"
                                   id="group-name" disabled
                                   autocomplete="off"
                                   value="{{$group->name}}"/>
                        </div>
                        <div class="group-info-block mt-2">
                            <label for="admin-select" class="font-bold flex">Admin:
                            </label>
                            <span id="non-edit-admin-name">{{$group->admin->name}}</span>
                            <select
                                disabled
                                id="admin-select"
                                name="admin_id"
                                class="py-1 px-4 pr-9 mt-2 w-full
                                hidden
                                border-gray-200 rounded-md text-sm focus:border-blue-500 focus:ring-blue-500
                                text-black
                                 ">
                                @forelse($users as $user)
                                    <option
                                        data-name="{{$user->name}}"
                                        {{$group->admin_id === $user->id ? 'selected' : ''}} value="{{$user->id}}">{{$user->name}}</option>
                                @empty
                                @endforelse
                            </select>

                        </div>
                        <div class="group-info-block mt-2">
                            <p class="font-bold">Created at:</p>
                            <p>{{$group->created_at}}</p>
                        </div>
                        <div class="group-info-block mt-2">
                            <p class="font-bold">Updated at:</p>
                            <p>{{$group->updated_at}}</p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="user-list ml-5 bg-gray-700 flex-1 p-5 rounded-xl">
            <header class="flex">
                <h2 class="font-bold text-xl">User List:</h2>
                <button
                    id="add-user-btn"
                    class="ml-auto font-semibold bg-blue-500 hover:bg-blue-600 text-gray-800 py-1 px-4 duration-200 rounded shadow-xl">
                    Add User
                </button>
            </header>
            <div id="user-list">
                <div id="user-list-reload">
                    <x-admin.table.table>
                        <x-admin.table.thead>
                            <x-admin.table.th>ID</x-admin.table.th>
                            <x-admin.table.th>Name</x-admin.table.th>
                            <x-admin.table.th>Email</x-admin.table.th>
                            <x-admin.table.th>Remove User</x-admin.table.th>
                        </x-admin.table.thead>
                        <tbody>
                        @foreach($users as $user)
                            <x-admin.table.tr>
                                <x-admin.table.td :odd="$loop->odd">{{$user->id}}</x-admin.table.td>
                                <x-admin.table.td :odd="$loop->odd">
                                    <a href="{{route('admin.user.show',$user->id)}}" class="flex items-center">
                                        <div class="w-10 h-10 image-wrapper mr-3">
                                            <img class="w-full h-full object-cover rounded-full"
                                                 src="{{asset($user->image_url)}}"
                                                 alt="">
                                        </div>
                                        {{Str::limit($user->name,25,'...')}}
                                    </a>
                                </x-admin.table.td>
                                <x-admin.table.td :odd="$loop->odd">{{$user->email}}</x-admin.table.td>
                                <x-admin.table.td :odd="$loop->odd">
                                    <button
                                        data-id="{{$user->id}}"
                                        class="remove-user font-semibold bg-red-400 hover:bg-red-500 text-gray-800 py-1 px-4 duration-200 rounded shadow-xl">
                                        Remove
                                    </button>
                                </x-admin.table.td>
                            </x-admin.table.tr>
                        @endforeach
                        </tbody>
                    </x-admin.table.table>
                    <div
                        class="paginate flex justify-center mt-3">{{ $users->appends(["messages"=>$messages->currentPage()])->links() }}</div>
                </div>
            </div>

        </div>
    </div>
    <div class="bottom-row">
        <div id="message-list" class="bg-gray-700 p-5 rounded-xl mt-2">
            <div id="message-list-reload">
                <h2 class="font-bold text-xl">Message List:</h2>
                <x-admin.table.table>
                    <x-admin.table.thead>
                        <x-admin.table.th>ID</x-admin.table.th>
                        <x-admin.table.th>Content</x-admin.table.th>
                        <x-admin.table.th>Sender</x-admin.table.th>
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
                                    href="{{route('admin.user.show',$message->sender_id)}}">{{$message->sender_name}}</a>
                            </x-admin.table.td>
                            <x-admin.table.td :odd="$loop->odd">{{$message->created_at}}</x-admin.table.td>
                            <x-admin.table.td :odd="$loop->odd">{{$message->updated_at}}</x-admin.table.td>
                            <x-admin.table.td
                                :odd="$loop->odd">{{$message->deleted_at ? 'deleted' : 'available'}}</x-admin.table.td>
                            <x-admin.table.td :odd="$loop->odd">
                                <button
                                    data-id="{{$message->id}}"
                                    class="{{$message->deleted_at ? 'hidden' : ''}} remove-message font-semibold bg-red-400 hover:bg-red-500 text-gray-800 py-1 px-4 duration-200 rounded shadow-xl">
                                    Remove
                                </button>
                                <button
                                    data-id="{{$message->id}}"
                                    class="{{$message->deleted_at ? '' : 'hidden'}} restore-message font-semibold bg-blue-400 hover:bg-blue-500 text-gray-800 py-1 px-4 duration-200 rounded shadow-xl">
                                    Restore
                                </button>
                            </x-admin.table.td>
                        </x-admin.table.tr>
                    @endforeach
                    </tbody>
                </x-admin.table.table>
                <div
                    class="paginate flex justify-center mt-3">{{$messages->appends(["users"=>$users->currentPage()])->links() }}</div>
            </div>
        </div>
    </div>
    <div id="overlay"
         class="overlay fixed top-0 left-0 z-60 w-0 h-0 bg-gray-900 opacity-60">
    </div>
    <x-client.popup popupId="add-user-popup" title="Add User">
        <form id="search-user-form" action="" method="POST">
            <input id="search-user-input" type="text" placeholder="User ID"
                   class="bg-none w-full focus:outline-none text-black p-1 rounded-md mt-2" autocomplete="off">
        </form>
        <x-admin.table.table id="add-user-table">
            <tbody>
            </tbody>
        </x-admin.table.table>
        <div class="button-wrapper flex">
            <button
                id="submit-add-user-list"
                class="ml-auto mt-2 font-semibold bg-blue-500 hover:bg-blue-600 text-gray-800 py-1 px-4 transition-colors duration-200 rounded shadow-xl">
                Add User
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
        let addUserList = [];
        let users_id = {{$users->pluck('id')}};
        const groupInfo = $('#group-info');
        const messageList = $('#message-list');

        function reloadContent(id) {
            $(id).load("{{route('admin.group.show',$group->id)}}" + ` ${id}-reload`);
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

        groupInfo.on('click', '#group-avatar-upload', function () {
            $('#group-avatar').trigger('click');
        });
        groupInfo.on('change', '#group-avatar', function (event) {
            const filePath = URL.createObjectURL(event.target.files[0]);
            $('#group-avatar-upload').attr('src', filePath);
        });
        groupInfo.on('click', '#edit-toggle-btn', function () {
            $('#group-info-form input').attr('disabled', (_, attr) => !attr);
            $('#group-info-form select').attr('disabled', (_, attr) => !attr);
            $('#avatar-editable').toggleClass('invisible');
            $('#group-avatar-upload').toggleClass('hover:cursor-pointer');
            $('#non-edit-admin-name').toggleClass('hidden');
            $('#admin-select').toggleClass('hidden');
        });
        groupInfo.on('change', '#admin-select', function () {
            const name = $('option:selected', this).data('name');
            $('#non-edit-admin-name').text(name);
        });
        groupInfo.on('submit', '#group-info-form', function (event) {
            event.preventDefault();
            const formData = new FormData($(this)[0]);
            formData.append('_method', 'PUT');
            console.log(Array.from(formData));
            $.ajax({
                url: "{{route('admin.group.update',$group->id)}}",
                data: formData,
                type: "POST",
                processData: false,
                contentType: false,
                success: function (res) {
                    console.log(res);
                    reloadContent('#group-info');
                },
                error: function (xhr) {
                    xhr = JSON.parse(xhr.responseText);
                    alert(xhr.message);
                }
            })
        });
        $('#add-user-btn').on('click', function () {
            showPopup('#add-user-popup');
        });
        $('.close-popup').on('click', function () {
            closePopup($(this).data('target'));
        });
        $('#search-user-form').on('submit', function (event) {
            event.preventDefault();
            const input = $('#search-user-input');
            let id = input.val();
            input.empty();
            if (users_id.includes(parseInt(id))) {
                alert(`USER_ID ${id} is already a member`);
            }
            if (!addUserList.includes(parseInt(id)) && !users_id.includes(parseInt(id))) {
                $.ajax({
                    url: "{{route('user.info')}}" + `/${id}`,
                    type: "GET",
                    success: function (res) {
                        const user = res.user;
                        addUserList.push(user.id);
                        const template = $('#table-template');
                        const tr = template.find('.tr').clone(true);
                        tr.attr('id', `user-${user.id}`);
                        const name = createTd(user.name);
                        const email = createTd(user.email);
                        const id = createTd(user.id);
                        const btn = createTd("")
                        tr.append(id, name, email, btn);
                        tr.append(`<button data-id=${user.id} class='remove-add-user ml-auto font-semibold bg-red-500 ` +
                            "hover:bg-red-600 text-gray-800 py-1 px-4 " +
                            "duration-200 rounded shadow-xl'>X</button>")
                        $('#add-user-table').find('tbody').append(tr);
                    },
                    error: function (xhr) {
                        xhr = JSON.parse(xhr.responseText);
                        const error = xhr.message;
                        alert(error);
                    }
                })
            }
        });
        $('#add-user-table').on('click', '.remove-add-user', function () {
            const id = $(this).data('id');
            $(`#user-${id}`).remove();
            addUserList = addUserList.filter(item => item !== id);
            console.log(addUserList);
        });
        $('#submit-add-user-list').on('click', function () {
            if (addUserList.length) {
                $.ajax({
                    url: "{{route('admin.group.user.add')}}",
                    type: "POST",
                    data: {
                        addUserList: addUserList,
                        group_id: "{{$group->id}}"
                    },
                    success: function (res) {
                        addUserList = [];
                        reloadContent('#user-list');
                        $('#add-user-table').find('tbody').empty();
                    },
                    error: function (xhr) {
                        xhr = JSON.parse(xhr.responseText);
                        alert(xhr.message);
                    }
                })
            }
        });
        $('#user-list').on('click', '.remove-user', function () {
            const id = $(this).data('id');
            const url = "{{route('admin.group.user.remove',['groupId'=>$group->id])}}" + `/${id}`;
            console.log(url);
            $.ajax({
                url: url,
                type: "GET",
                success: function (res) {
                    users_id = res.users_id;
                    reloadContent('#user-list');
                },
                error: function (xhr) {
                    xhr = JSON.parse(xhr.responseText);
                    alert(xhr.message);
                }
            })
        })

        messageList.on('click', '.remove-message', function () {
            const id = $(this).data('id');
            $.ajax({
                url: "{{route('admin.message.remove')}}" + `/group/${id}`,
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
            $.ajax({
                url: "{{route('admin.message.restore')}}" + `/group/${id}`,
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
