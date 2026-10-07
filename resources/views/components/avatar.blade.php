@props(['user', 'size' => 'h-8 w-8'])
@php $url = $user->avatarUrl(); @endphp
@if ($url)
    <img src="{{ $url }}" alt="Foto profilo di {{ $user->name }}" {{ $attributes->merge(['class' => $size . ' shrink-0 rounded-full border border-gray-200 object-cover']) }}>
@else
    <span aria-hidden="true" {{ $attributes->merge(['class' => $size . ' inline-flex shrink-0 items-center justify-center rounded-full bg-gray-200 text-xs font-semibold uppercase text-gray-600']) }}>{{ mb_substr($user->name, 0, 1) }}</span>
@endif
