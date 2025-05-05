<div>
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <h3 class="card-title">
                    <i class="ki-duotone ki-message-text-2 fs-1 me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                    Send SMS
                </h3>
            </div>
        </div>
        
        <div class="card-body">
            @if (session()->has('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session()->has('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <form wire:submit.prevent="sendSms">
                <div class="mb-5">
                    <label class="form-label fw-semibold">SMS Type</label>
                    <div class="d-flex flex-wrap gap-3">
                        <div class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="radio" value="single" id="single" wire:model="sendType">
                            <label class="form-check-label" for="single">
                                Single Recipient
                            </label>
                        </div>
                        <div class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="radio" value="bulk" id="bulk" wire:model="sendType">
                            <label class="form-check-label" for="bulk">
                                Multiple Recipients
                            </label>
                        </div>
                        <div class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="radio" value="group" id="group" wire:model="sendType">
                            <label class="form-check-label" for="group">
                                Recipient Group
                            </label>
                        </div>
                    </div>
                </div>

                @if ($sendType === 'single')
                    <div class="mb-5">
                        <label class="form-label required">Recipient Phone Number</label>
                        <input type="text" class="form-control" placeholder="+1234567890" wire:model="recipient">
                        @error('recipient') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                @elseif ($sendType === 'bulk')
                    <div class="mb-5">
                        <label class="form-label">Add Recipients</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" placeholder="+1234567890" wire:model="recipient">
                            <button class="btn btn-secondary" type="button" wire:click="addRecipient">Add</button>
                        </div>
                        
                        @if (count($recipients) > 0)
                            <div class="mt-3">
                                <h6>Recipients ({{ count($recipients) }})</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($recipients as $index => $recip)
                                        <span class="badge bg-light text-dark p-2">
                                            {{ $recip }}
                                            <i class="ki-duotone ki-cross fs-7 ms-2" style="cursor: pointer" wire:click="removeRecipient({{ $index }})">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @elseif ($sendType === 'group')
                    <div class="mb-5">
                        <label class="form-label required">Select Recipient Group</label>
                        <select class="form-select" wire:model="recipientListId">
                            <option value="">Select a group</option>
                            @foreach ($recipientLists as $list)
                                <option value="{{ $list['id'] }}">{{ $list['name'] }}</option>
                            @endforeach
                        </select>
                        @error('recipientListId') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                @endif

                <div class="mb-5">
                    <label class="form-label required">Message</label>
                    <textarea class="form-control" rows="5" wire:model="message" placeholder="Type your SMS message here..."></textarea>
                    @error('message') <span class="text-danger">{{ $message }}</span> @enderror
                    <div class="d-flex justify-content-between mt-2">
                        <small class="text-muted">Max 160 characters</small>
                        <small>{{ strlen($message) }}/160</small>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading wire:target="sendSms" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Send SMS
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
