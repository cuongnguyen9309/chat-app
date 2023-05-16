@props(['odd'=>false])
<td class="px-5 py-2 {{$odd ? 'bg-gray-100' : ''}}">{{$slot}}</td>
