<div class="sidebar-header m-5 flex flex-row items-center">
    <div class="relative search-bar-wrapper flex flex-row items-center w-3/5">
        <form id="search-form" action="">
            <input id="filter-input" type="text" placeholder="Search friends ..."
                   class="bg-none w-full focus:outline-none text-black p-2  rounded-md" autocomplete="off">
        </form>
        <i class="fa-solid fa-magnifying-glass absolute right-0 text-gray-500 mr-2 hover:cursor-pointer"></i>
    </div>
    <button id="close-search-window" class=" bg-gray-100 rounded-xl px-4 py-2 text-black ml-auto hidden ">Close</button>
    <div id="add-socials" class="ml-auto flex flex-row">
        <div id="add-friend"
             class="group icon-wrapper bg-white flex items-center justify-center rounded-full h-10 w-10 mr-2 hover:cursor-pointer hover:bg-green-400 duration-200">
            <i class="fa-solid fa-user-plus text-gray-500 group-hover:text-white duration-200"></i>
        </div>
        <div id="create-group"
             class="group icon-wrapper bg-white flex items-center justify-center rounded-full h-10 w-10 hover:cursor-pointer hover:bg-green-400 duration-200">
            <i class="fa-solid fa-user-group text-gray-500 group-hover:text-white duration-200"></i>
        </div>
    </div>
</div>
