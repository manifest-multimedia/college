<div>
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="ki-duotone ki-message-text-2 fs-2 me-2"></i>Callbly SMS Provider</h3></div>
        <div class="card-body">
            @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
            @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
            <div class="alert alert-info">Callbly is the only supported SMS provider. Credentials are stored securely in this institution's System Settings.</div>
            <form wire:submit="save">
                <div class="row g-4">
                    <div class="col-md-7"><label class="form-label required">Callbly API token</label><input type="password" wire:model="apiToken" class="form-control @error('apiToken') is-invalid @enderror" placeholder="{{ $hasApiToken ? 'Configured — enter a new value only to rotate it' : 'Paste the Callbly bearer token' }}"><div class="form-text">{{ $hasApiToken ? 'A token is already configured and is never displayed here.' : 'Use the API token from Callbly.' }}</div>@error('apiToken')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-5"><label class="form-label required">Approved sender ID</label><input type="text" wire:model="senderName" maxlength="11" class="form-control @error('senderName') is-invalid @enderror" placeholder="College360"><div class="form-text">Maximum 11 characters; must be approved in Callbly.</div>@error('senderName')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                </div>
                <div class="form-check form-switch mt-5"><input class="form-check-input" type="checkbox" id="callblyEnabled" wire:model="enabled"><label class="form-check-label" for="callblyEnabled">Enable Callbly SMS sending</label></div>
                <div class="d-flex gap-3 mt-5"><button class="btn btn-primary" type="submit" wire:loading.attr="disabled">Save Callbly Settings</button><button class="btn btn-light-primary" type="button" wire:click="refreshBalances" wire:loading.attr="disabled">Check SMS & Wallet Balances</button></div>
            </form>
            @if($balance)
                <div class="row g-4 mt-2"><div class="col-md-6"><div class="border rounded p-4"><div class="text-muted">SMS credits</div><div class="fs-2 fw-bold">{{ $balance['sms_balance'] ?? '—' }}</div></div></div><div class="col-md-6"><div class="border rounded p-4"><div class="text-muted">Wallet balance</div><div class="fs-2 fw-bold">{{ $balance['formatted_wallet_balance'] ?? (($balance['currency'] ?? 'GHS').' '.($balance['wallet_balance'] ?? '—')) }}</div></div></div></div>
            @endif
        </div>
    </div>
</div>
