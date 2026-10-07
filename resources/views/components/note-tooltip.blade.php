@props(['note'])

@if (filled($note))
    <div class="relative inline-flex" x-data="{ open: false }">
        <button type="button" @click="open = !open" @mouseenter="open = true" @mouseleave="open = false" @click.outside="open = false" title="{{ $note }}" aria-label="Mostra note della pratica" class="inline-flex h-6 w-6 items-center justify-center rounded-full text-blue-600 hover:bg-blue-50">
            <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10" /><line x1="12" y1="16" x2="12" y2="12" /><line x1="12" y1="8" x2="12.01" y2="8" /></svg>
        </button>
        <div x-show="open" x-cloak x-transition.opacity class="absolute right-0 top-full z-20 mt-1 w-64 whitespace-pre-line rounded border border-gray-200 bg-white p-3 text-xs text-gray-700 shadow-lg">{{ $note }}</div>
    </div>
@endif
