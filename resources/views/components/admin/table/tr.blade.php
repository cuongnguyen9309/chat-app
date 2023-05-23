@props(['id'=>''])
<tr class="tr" {{$id ? "id=$id" : ''}}>
    <td class="w-[1rem]"></td>
    {{$slot}}
    <td class="w-[1rem]"></td>
</tr>
