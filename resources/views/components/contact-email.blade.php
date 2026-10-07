@props(['cliente', 'defer' => false])
@php
    $emailCounter = (int) request()->attributes->get('email_dialog_counter', 0) + 1;
    request()->attributes->set('email_dialog_counter', $emailCounter);
    $uid = $cliente->id . '-' . $emailCounter;
@endphp
@if (filter_var($cliente->email, FILTER_VALIDATE_EMAIL))
    <button type="button" onclick="document.getElementById('email-cliente-dialog-{{ $uid }}').showModal()" title="Invia email" class="break-all text-left text-blue-600 hover:underline">{{ $cliente->email }}</button>
    @if ($defer)
        @push('dialogs')
            @include('clienti._email-dialog', ['cliente' => $cliente, 'uid' => $uid])
        @endpush
    @else
        @include('clienti._email-dialog', ['cliente' => $cliente, 'uid' => $uid])
    @endif
@else
    {{ $cliente->email ?: '-' }}
@endif
