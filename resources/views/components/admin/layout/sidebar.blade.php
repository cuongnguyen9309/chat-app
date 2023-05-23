@php use Illuminate\Support\Facades\Route; @endphp
<div class="sidebar w-1/5 max-w-[15rem] content-stretch bg-gray-900 text-white">
    <div class="logo font-logo text-green-400 tracking-wider text-6xl font-bold text-center mt-4">GREEN</div>
    <ul class="links pt-12">
        <x-admin.layout.sidebar.link route="admin.user.index" to="User"/>
        <x-admin.layout.sidebar.link route="admin.group.index" to="Group"/>
        <x-admin.layout.sidebar.link route="admin.message.index" to="Message"/>
    </ul>
</div>
