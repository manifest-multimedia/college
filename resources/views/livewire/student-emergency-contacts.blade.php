<div class="card shadow-sm mt-5 mb-5">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3 py-3">
        <h3 class="card-title fw-bold mb-0"><i class="fas fa-address-book me-2" aria-hidden="true"></i>Emergency Contacts</h3>
        <div class="card-toolbar m-0">
            <button type="button" class="btn btn-sm btn-light-primary" wire:click="addContact">
                <i class="fas fa-plus me-1" aria-hidden="true"></i> Add Contact
            </button>
        </div>
    </div>
    <div class="card-body">
        @if(session()->has('emergency_contact_status'))
            <div class="alert alert-success" role="status">{{ session('emergency_contact_status') }}</div>
        @endif
        @if($showForm)
            <form wire:submit="saveContact" class="border rounded p-4 mb-5">
                <h4 class="mb-4">{{ $editingContactId ? 'Edit Emergency Contact' : 'Add Emergency Contact' }}</h4>
                @include('livewire.partials.emergency-contact-fields', ['prefix' => 'contact', 'inputId' => 'emergency-contact'])
                <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
                    <button type="button" class="btn btn-light" wire:click="cancel">Cancel</button>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="saveContact">
                        <span wire:loading.remove wire:target="saveContact">Save Contact</span>
                        <span wire:loading wire:target="saveContact">Saving…</span>
                    </button>
                </div>
            </form>
        @endif
        <div class="row g-4">
            @forelse($contacts as $entry)
                <div class="col-md-6" wire:key="emergency-contact-{{ $entry->id }}">
                    <div class="border rounded p-4 h-100">
                        <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
                            <div><h4 class="mb-1">{{ $entry->full_name }}</h4><span class="text-muted">{{ $entry->relationship }}</span></div>
                            <div class="d-flex gap-2 align-items-start">
                                <button type="button" class="btn btn-sm btn-light-primary" wire:click="editContact({{ $entry->id }})" aria-label="Edit contact {{ $entry->full_name }}">Edit</button>
                                <button type="button" class="btn btn-sm btn-light-danger" wire:click="deleteContact({{ $entry->id }})" wire:confirm="Remove this emergency contact?" aria-label="Remove contact {{ $entry->full_name }}">Remove</button>
                            </div>
                        </div>
                        <dl class="row mb-0">
                            @foreach(\App\Services\StudentEmergencyContactService::FIELDS as $field => $label)
                                @if($field !== 'full_name' && $field !== 'relationship' && $entry->{$field})
                                    <dt class="col-sm-5 text-muted fw-normal">{{ $label }}</dt>
                                    <dd class="col-sm-7 text-break" style="white-space: pre-line">{{ $entry->{$field} }}</dd>
                                @endif
                            @endforeach
                        </dl>
                    </div>
                </div>
            @empty
                <div class="col-12 text-muted py-3">No emergency contacts added. You can add a contact whenever the information is available.</div>
            @endforelse
        </div>
    </div>
</div>
