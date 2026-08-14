@props([
    'alt' => config('app.name', 'Travel Manager').' logo',
])

<img src="{{ asset('logo-europolo.png') }}" alt="{{ $alt }}" {{ $attributes }} />
