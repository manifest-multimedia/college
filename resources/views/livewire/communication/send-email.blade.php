<div>
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <h3 class="card-title">
                    <i class="ki-duotone ki-sms fs-1 me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                    Send Email
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

            <form wire:submit.prevent="sendEmail">
                <div class="mb-5">
                    <label class="form-label fw-semibold">Email Type</label>
                    <div class="d-flex flex-wrap gap-3">
                        <div class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="radio" value="single" id="single_email" wire:model.live="sendType">
                            <label class="form-check-label" for="single_email">
                                Single Recipient
                            </label>
                        </div>
                        <div class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="radio" value="bulk" id="bulk_email" wire:model.live="sendType">
                            <label class="form-check-label" for="bulk_email">
                                Multiple Recipients
                            </label>
                        </div>
                        <div class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="radio" value="group" id="group_email" wire:model.live="sendType">
                            <label class="form-check-label" for="group_email">
                                Recipient Group
                            </label>
                        </div>
                    </div>
                </div>

                @if ($sendType === 'single')
                    <div class="mb-5">
                        <label class="form-label required">Recipient Email</label>
                        <input type="email" class="form-control" placeholder="email@example.com" wire:model="recipient">
                        @error('recipient') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                @elseif ($sendType === 'bulk')
                    <div class="mb-5">
                        <label class="form-label">Add Recipients</label>
                        <div class="input-group mb-3">
                            <input type="email" class="form-control" placeholder="email@example.com" wire:model="recipient">
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
                    <label class="form-label required">Subject</label>
                    <input type="text" class="form-control" placeholder="Email subject" wire:model="subject">
                    @error('subject') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="row mb-5">
                    <div class="col-md-6">
                        <label class="form-label">CC</label>
                        <input type="text" class="form-control" placeholder="cc@example.com" wire:model="cc">
                        @error('cc') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">BCC</label>
                        <input type="text" class="form-control" placeholder="bcc@example.com" wire:model="bcc">
                        @error('bcc') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mb-5">
                    <label class="form-label">Template (Optional)</label>
                    <select class="form-select" wire:model="template">
                        <option value="">Default Template</option>
                        @foreach ($templates as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-5">
                    <label class="form-label">Attachment (Optional)</label>
                    <input type="file" class="form-control" wire:model="attachment">
                    <div wire:loading wire:target="attachment">
                        <small class="text-muted">Uploading...</small>
                    </div>
                    @error('attachment') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="mb-5">
                    <label class="form-label required">Message</label>
                    <textarea class="form-control" rows="8" wire:model="message" placeholder="Type your email message here..."></textarea>
                    @error('message') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading wire:target="sendEmail" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Send Email
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
