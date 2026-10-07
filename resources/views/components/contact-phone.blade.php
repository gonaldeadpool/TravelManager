@props(['number' => null])
@php
    $number = trim((string) $number);
    $tel = preg_replace('/(?!^\+)[^\d]/', '', $number);
    $wa = preg_replace('/\D+/', '', $number);
@endphp
@if ($number === '')
    -
@else
    <span class="hidden md:inline">{{ $number }}</span>
    <span class="md:hidden" x-data="{ open: false }" @click.outside="open = false">
        <button type="button" @click="open = !open" :aria-expanded="open" class="break-words text-left text-blue-600 underline decoration-dotted underline-offset-2">{{ $number }}</button>
        <span x-cloak x-show="open" class="mt-2 flex gap-2">
            <a href="tel:{{ $tel }}" aria-label="Chiama {{ $number }}" title="Chiama" class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-green-100 text-green-700 hover:bg-green-200">
                                            <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 3.18 2 2 0 0 1 4.11 1h3a2 2 0 0 1 2 1.72c.12.96.35 1.9.69 2.8a2 2 0 0 1-.45 2.11L8.08 8.92a16 16 0 0 0 6 6l1.29-1.27a2 2 0 0 1 2.11-.45c.9.34 1.84.57 2.8.69A2 2 0 0 1 22 16.92z" />
                                            </svg>
                                        </a>
                                        <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener noreferrer" aria-label="Apri WhatsApp per {{ $number }}" title="WhatsApp" class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-green-100 text-green-700 hover:bg-green-200">
                                            <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M20.52 3.48A11.86 11.86 0 0 0 12.08 0C5.5 0 .14 5.35.14 11.94c0 2.1.55 4.15 1.6 5.96L.05 24l6.25-1.64a11.9 11.9 0 0 0 5.77 1.47h.01c6.58 0 11.94-5.36 11.94-11.94 0-3.19-1.24-6.19-3.5-8.41ZM12.08 21.8h-.01a9.9 9.9 0 0 1-5.04-1.38l-.36-.21-3.71.97.99-3.62-.24-.37a9.87 9.87 0 0 1-1.52-5.25c0-5.46 4.44-9.9 9.9-9.9a9.83 9.83 0 0 1 7 2.9 9.83 9.83 0 0 1 2.9 7c0 5.46-4.44 9.9-9.91 9.9Zm5.43-7.41c-.3-.15-1.77-.87-2.05-.97-.28-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.47-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.08-.8.37-.27.3-1.04 1.02-1.04 2.49s1.07 2.89 1.22 3.09c.15.2 2.1 3.2 5.08 4.49.71.31 1.27.5 1.7.64.72.23 1.37.2 1.89.12.58-.09 1.77-.72 2.02-1.42.25-.7.25-1.3.17-1.42-.07-.13-.27-.2-.57-.35Z" />
                                            </svg>
                                        </a>
        </span>
    </span>
@endif
