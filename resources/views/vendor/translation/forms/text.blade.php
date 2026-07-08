<div class="mt-3">
    <label for="{{ $field }}">
        {{ $label }}
    </label>
    <input 
        class="@if($errors->has($field)) error @endif input w-full border mt-2" 
        name="{{ $field }}" 
        id="{{ $field }}" 
        type="text" 
        placeholder="{{ isset($placeholder) ? $placeholder : '' }}"
        value="{{ old($field) }}"
        {{ isset($required) ? 'required' : '' }}>
    @if($errors->has($field))
        @foreach($errors->get($field) as $error)
            <p class="error-text">{!! $error !!}</p>
        @endforeach
    @endif
</div>

