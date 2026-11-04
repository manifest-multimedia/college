<div class="row g-3">
    @foreach(\App\Services\StudentEmergencyContactService::FIELDS as $field => $label)
        @php($required = in_array($field, ['full_name', 'relationship', 'mobile_number'], true))
        <div class="{{ $field === 'residential_address' ? 'col-12' : 'col-md-6' }}">
            <label for="{{ $inputId }}-{{ $field }}" class="form-label">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>
            @if($field === 'residential_address')
                <textarea id="{{ $inputId }}-{{ $field }}" class="form-control @error($prefix.'.'.$field) is-invalid @enderror" wire:model="{{ $prefix }}.{{ $field }}" rows="2" maxlength="2000"></textarea>
            @else
                <input id="{{ $inputId }}-{{ $field }}" type="{{ $field === 'email' ? 'email' : (in_array($field, ['mobile_number', 'alternate_phone']) ? 'tel' : 'text') }}" class="form-control @error($prefix.'.'.$field) is-invalid @enderror" wire:model="{{ $prefix }}.{{ $field }}" maxlength="{{ in_array($field, ['mobile_number', 'alternate_phone']) ? 30 : ($field === 'relationship' ? 100 : 255) }}" @required($required)>
            @endif
            @error($prefix.'.'.$field)<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    @endforeach
</div>
