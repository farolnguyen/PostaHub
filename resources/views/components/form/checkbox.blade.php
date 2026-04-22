@props([
    'name',
    'label',
    'id' => null,
    'value' => '1',
    'checked' => false,
])

@php
    $fieldId = $id ?: $name;
    $isChecked = old($name, $checked) ? true : false;
@endphp

<div class="form-group form-check">
    <input
        type="checkbox"
        id="{{ $fieldId }}"
        name="{{ $name }}"
        value="{{ $value }}"
        class="form-check-input {{ $errors->has($name) ? 'is-invalid' : '' }}"
        @checked($isChecked)
        {{ $attributes }}
    >
    <label class="form-check-label" for="{{ $fieldId }}">{{ $label }}</label>
    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

