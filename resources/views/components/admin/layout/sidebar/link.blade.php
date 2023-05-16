@php use Illuminate\Support\Facades\Route;
@endphp
@props(['route'=>'','to'=>''])

<li class="px-5 pb-1 {{Route::currentRouteName() === $route ? 'text-white' : 'text-gray-400'}}"><a
        href="{{route($route)}}">{{$to}}</a>
</li>
