@props([
    'name',
    'label' => null,
    'id' => null,
    'required' => false,
    'accept' => null,
    'hint' => null,
])

@php
    $fieldId = $id ?: $name;
@endphp

<div class="form-group">
    @if ($label)
        <label for="{{ $fieldId }}">{{ $label }}</label>
    @endif

    <input
        type="file"
        id="{{ $fieldId }}"
        name="{{ $name }}"
        @if ($accept) accept="{{ $accept }}" @endif
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'form-control-file '.($errors->has($name) ? 'is-invalid' : '')]) }}
    >

    @if ($hint)
        <small class="form-text text-muted">{{ $hint }}</small>
    @endif

    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

