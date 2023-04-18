@props([
    'theme' => config('themes.default', 'bootstrap'),
    'name',
    'type'  => 'text',
    'id'    => null,
    'label' => null,
    'value' => null,
])

@php
    $frame = config('themes.framework.' . $theme . '.form');
    $groupClass = $frame['group'] ?? '';
    $labelClass = $frame['label'] ?? '';
    $inputClass = $frame['input'] ?? '';

    $id = $id ?? $name;
@endphp

<div class="{{ $groupClass }}">
    @if($label)
        <x-label
            :for="$id"
            :class="$labelClass"
            :label="$label"
        />
    @endif
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $id }}"
        value="{{ $value }}"
        class="{{ $inputClass }} {{ $attributes->get('class') }}"
        autocomplete="off"
        aria-autocomplete="off"
        {{ $attributes->whereDoesntStartWith('class') }}
    >
</div>
