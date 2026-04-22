@props([
    'name',
    'label' => null,
    'type' => 'text',
    'id' => null,
    'value' => null,
    'required' => false,
    'readonly' => false,
    'disabled' => false,
    'placeholder' => null,
    'autocomplete' => null,
    'hint' => null,
])

@php
    $fieldId = $id ?: $name;
    $inputValue = old($name, $value);
@endphp

<div class="form-group">
    @if ($label)
        <label for="{{ $fieldId }}">{{ $label }}</label>
    @endif

    <input
        id="{{ $fieldId }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ $inputValue }}"
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        @if ($required) required @endif
        @if ($readonly) readonly @endif
        @if ($disabled) disabled @endif
        {{ $attributes->merge(['class' => 'form-control '.($errors->has($name) ? 'is-invalid' : '')]) }}
    >

    @if ($hint)
        <small class="form-text text-muted">{{ $hint }}</small>
    @endif

    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

