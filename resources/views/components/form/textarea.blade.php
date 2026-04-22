@props([
    'name',
    'label' => null,
    'id' => null,
    'value' => null,
    'rows' => 4,
    'required' => false,
    'placeholder' => null,
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

    <textarea
        id="{{ $fieldId }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'form-control '.($errors->has($name) ? 'is-invalid' : '')]) }}
    >{{ $inputValue }}</textarea>

    @if ($hint)
        <small class="form-text text-muted">{{ $hint }}</small>
    @endif

    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

