@props([
    'name',
    'label' => null,
    'id' => null,
    'required' => false,
    'hint' => null,
])

@php
    $fieldId = $id ?: $name;
@endphp

<div class="form-group">
    @if ($label)
        <label for="{{ $fieldId }}">{{ $label }}</label>
    @endif

    <select
        id="{{ $fieldId }}"
        name="{{ $name }}"
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'form-control '.($errors->has($name) ? 'is-invalid' : '')]) }}
    >
        {{ $slot }}
    </select>

    @if ($hint)
        <small class="form-text text-muted">{{ $hint }}</small>
    @endif

    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

