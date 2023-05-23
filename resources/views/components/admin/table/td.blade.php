@props(['odd'=>false])
<td class="td text-gray-300 px-5 py-2 {{$odd ? 'bg-gray-700' : ''}}">{{$slot}}</td>
