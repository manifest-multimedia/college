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

            @hasanyrole('System|Super Admin')
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 border rounded p-4 mb-5 bg-light">
                    <div>
                        <span class="fw-bold">Callbly balances</span>
                        @if($balance && ($balance['success'] ?? false))
                            <span class="text-muted ms-3">SMS credits: <strong>{{ $balance['sms_balance'] ?? '—' }}</strong></span>
                            <span class="text-muted ms-3">Wallet: <strong>{{ $balance['formatted_wallet_balance'] ?? (($balance['currency'] ?? 'GHS').' '.($balance['wallet_balance'] ?? '—')) }}</strong></span>
                        @elseif($balance)
                            <span class="text-danger ms-3">{{ $balance['message'] ?? 'Unable to retrieve Callbly balances.' }}</span>
                        @else
                            <span class="text-muted ms-3">Loading balances…</span>
                        @endif
                    </div>
                    <button type="button" class="btn btn-light-primary btn-sm" wire:click="refreshBalances" wire:loading.attr="disabled">Refresh balances</button>
                </div>
            @endhasanyrole

            <form wire:submit.prevent="sendSms">
                <div class="mb-5">
                    <label class="form-label fw-semibold">SMS Type</label>
                    <div class="d-flex flex-wrap gap-3">
                        <div class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="radio" value="single" id="single" wire:model.live="sendType">
                            <label class="form-check-label" for="single">
                                Single Recipient
                            </label>
                        </div>
                        <div class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="radio" value="bulk" id="bulk" wire:model.live="sendType">
                            <label class="form-check-label" for="bulk">
                                Multiple Recipients
                            </label>
                        </div>
                        <div class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="radio" value="group" id="group" wire:model.live="sendType">
                            <label class="form-check-label" for="group">
                                Recipient Group
                            </label>
                        </div>
                        <div class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="radio" value="audience" id="audience" wire:model.live="sendType">
                            <label class="form-check-label" for="audience">
                                Institution Audience
                            </label>
                        </div>
                    </div>
                </div>

                @if ($sendType === 'single')
                    <div class="mb-5">
                        <label class="form-label required">Recipient Phone Number</label>
                        <input type="text" class="form-control @error('recipient') is-invalid @enderror" placeholder="+1234567890" wire:model.live.debounce.300ms="recipient">
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
                        <select class="form-select @error('recipientListId') is-invalid @enderror" wire:model.live="recipientListId">
                            <option value="">Select a group</option>
                            @foreach ($recipientLists as $list)
                                <option value="{{ $list['id'] }}">
                                    {{ $list['name'] }}@if(isset($list['items_count'])) ({{ $list['items_count'] }} {{ \Illuminate\Support\Str::plural('recipient', $list['items_count']) }})@endif
                                </option>
                            @endforeach
                        </select>
                        @error('recipientListId') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                @elseif ($sendType === 'audience')
                    <div class="mb-5">
                        <label class="form-label required">Audience</label>
                        <select class="form-select @error('audience') is-invalid @enderror" wire:model.live="audience">
                            <option value="all_students">All active students</option>
                            <option value="cohort">Students in a cohort</option>
                            <option value="program">Students in a program</option>
                            <option value="all_staff">All staff</option>
                        </select>
                        @error('audience') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    @if($audience === 'cohort')
                        <div class="mb-5">
                            <label class="form-label required">Cohort</label>
                            <select class="form-select @error('audienceCohortId') is-invalid @enderror" wire:model.live="audienceCohortId">
                                <option value="">Select a cohort</option>
                                @foreach($cohorts as $cohort)<option value="{{ $cohort['id'] }}">{{ $cohort['name'] }}</option>@endforeach
                            </select>
                            @error('audienceCohortId') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    @elseif($audience === 'program')
                        <div class="mb-5">
                            <label class="form-label required">Program</label>
                            <select class="form-select @error('audienceProgramId') is-invalid @enderror" wire:model.live="audienceProgramId">
                                <option value="">Select a program</option>
                                @foreach($programs as $program)<option value="{{ $program['id'] }}">{{ $program['name'] }}</option>@endforeach
                            </select>
                            @error('audienceProgramId') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <div class="border rounded bg-light p-4 mb-5">
                        <div class="fw-bold mb-2">Recipient summary: {{ $audienceSummary['label'] ?? 'Selected audience' }}</div>
                        <div class="row g-3 small">
                            <div class="col-sm-3"><span class="text-muted d-block">Records found</span><strong>{{ $audienceSummary['total_records'] ?? 0 }}</strong></div>
                            <div class="col-sm-3"><span class="text-muted d-block">Valid mobile numbers</span><strong class="text-success">{{ $audienceSummary['valid_numbers'] ?? 0 }}</strong></div>
                            <div class="col-sm-3"><span class="text-muted d-block">No/invalid contact</span><strong class="text-warning">{{ $audienceSummary['skipped_records'] ?? 0 }}</strong></div>
                            <div class="col-sm-3"><span class="text-muted d-block">Duplicate numbers</span><strong>{{ $audienceSummary['duplicate_numbers'] ?? 0 }}</strong></div>
                        </div>
                    </div>
                @endif

                <div class="mb-5">
                    <label class="form-label required">Sender ID</label>
                    <select class="form-select @error('selectedSenderId') is-invalid @enderror" wire:model="selectedSenderId">
                        <option value="">Select an approved sender ID</option>
                        @foreach($senderIds as $senderId)
                            <option value="{{ $senderId }}">{{ $senderId }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Only sender IDs approved in Callbly can be used.</div>
                    @error('selectedSenderId') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="mb-5" x-data="{
                    text: @entangle('message').live,
                    get charCount() {
                        return (this.text || '').length;
                    },
                    get pages() {
                        return this.charCount > 0 ? Math.ceil(this.charCount / 160) : 1;
                    },
                    get remainingInPage() {
                        if (this.charCount === 0) return 160;
                        const remainder = this.charCount % 160;
                        return remainder === 0 ? 0 : (160 - remainder);
                    }
                }">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label required fw-semibold mb-0">Message</label>
                        <span class="badge" 
                              :class="pages > 1 ? 'badge-light-warning text-warning fw-bold' : 'badge-light-primary text-primary fw-semibold'"
                              x-text="pages + (pages === 1 ? ' SMS Page (1 credit / recipient)' : ' SMS Pages (' + pages + ' credits / recipient)')">
                            {{ $this->pageCount }} {{ $this->pageCount === 1 ? 'SMS Page (1 credit / recipient)' : 'SMS Pages ('.$this->pageCount.' credits / recipient)' }}
                        </span>
                    </div>

                    <textarea class="form-control @error('message') is-invalid @enderror" 
                              rows="5" 
                              wire:model.live.debounce.300ms="message" 
                              x-model="text" 
                              placeholder="Type your SMS message here..."></textarea>
                    @error('message') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror

                    <div class="d-flex flex-wrap justify-content-between align-items-center mt-2 text-muted fs-7">
                        <div>
                            <span>160 characters per SMS page.</span>
                            <span class="text-muted ms-1">Longer messages are split into multiple pages and charged 1 credit per page.</span>
                        </div>
                        <div class="text-end">
                            <span class="fw-bold text-gray-800" x-text="charCount">{{ strlen($message) }}</span> characters
                            <span class="text-gray-300 mx-1">•</span>
                            <span class="fw-semibold" x-text="remainingInPage">{{ (strlen($message) % 160 === 0 && strlen($message) > 0) ? 0 : (160 - (strlen($message) % 160)) }}</span> chars left on current page
                        </div>
                    </div>

                    <div x-show="pages > 1" class="alert alert-info py-2 px-3 mt-3 d-flex align-items-center mb-0" style="display: none;">
                        <i class="ki-duotone ki-information-2 fs-2 me-2 text-info">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        <div class="fs-7 text-gray-700">
                            This message is <strong x-text="charCount"></strong> characters long (<strong x-text="pages"></strong> pages). Each recipient will consume <strong x-text="pages"></strong> SMS credits.
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="text-muted fs-7">
                        @if($this->estimatedRecipientsCount > 0)
                            Estimated deduction: 
                            <strong class="text-gray-800">{{ $this->estimatedRecipientsCount }} {{ \Illuminate\Support\Str::plural('recipient', $this->estimatedRecipientsCount) }}</strong> 
                            &times; 
                            <strong class="text-gray-800">{{ $this->pageCount }} {{ \Illuminate\Support\Str::plural('page', $this->pageCount) }}</strong> 
                            = 
                            <span class="badge badge-light-success fw-bold fs-7">{{ $this->estimatedCredits }} {{ \Illuminate\Support\Str::plural('credit', $this->estimatedCredits) }}</span>
                        @endif
                    </div>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading wire:target="sendSms" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Send SMS
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
