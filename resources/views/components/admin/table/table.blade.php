@props(['id'=>''])
<table
    {{$id ? "id=$id" : ''}}
    class="table bg-gray-800 rounded-s w-full mt-5 shadow-[0px_2px_5px_0px_rgba(0,0,0,0.1)]">
    {{$slot}}
</table>
