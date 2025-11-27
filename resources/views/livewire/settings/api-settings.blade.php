<div>
    {{-- Flash Messages --}}
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @if(session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @if(session()->has('warning'))
        <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center">
                <i class="fas fa-cogs fs-1 text-primary me-3"></i>
                <div>
                    <h1 class="fs-2 fw-bold text-gray-800 mb-0">API Settings</h1>
                    <p class="text-muted mb-0">Manage 360 College integrations and API credentials</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="authcentral-tab" data-bs-toggle="tab" href="#authcentral-config" role="tab" aria-controls="authcentral-config" aria-selected="true">
                <i class="fas fa-link me-2"></i>AuthCentral Configuration
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="api-keys-tab" data-bs-toggle="tab" href="#api-keys" role="tab" aria-controls="api-keys" aria-selected="false">
                <i class="fas fa-key me-2"></i>API Keys Management
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="webhooks-tab" data-bs-toggle="tab" href="#webhooks" role="tab" aria-controls="webhooks" aria-selected="false">
                <i class="fas fa-plug me-2"></i>Webhook Settings
            </a>
        </li>
    </ul>

    {{-- Tab Content --}}
    <div class="tab-content" id="apiSettingsTabContent">
        {{-- AuthCentral Configuration Tab --}}
        <div class="tab-pane fade show active" id="authcentral-config" role="tabpanel" aria-labelledby="authcentral-tab">
            <div class="card mb-5">
                <div class="card-header">
                    <h3 class="card-title fw-bold">
                        <i class="fas fa-university me-2 text-primary"></i>AuthCentral Integration
                    </h3>
                </div>
                <div class="card-body">
                    <form wire:submit="saveAuthCentralSettings">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold required">Login URL</label>
                                <input type="url" wire:model="authcentralLoginUrl" 
                                       class="form-control @error('authcentralLoginUrl') is-invalid @enderror"
                                       placeholder="https://auth.example.edu/login">
                                @error('authcentralLoginUrl')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">URL where users will be redirected for authentication</div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold required">API URL</label>
                                <input type="url" wire:model="authcentralApiUrl" 
                                       class="form-control @error('authcentralApiUrl') is-invalid @enderror"
                                       placeholder="https://auth.example.edu/api/user">
                                @error('authcentralApiUrl')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">API endpoint for retrieving user information</div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold">Signup URL</label>
                                <input type="url" wire:model="authcentralSignupUrl" 
                                       class="form-control @error('authcentralSignupUrl') is-invalid @enderror"
                                       placeholder="https://auth.example.edu/sign-up">
                                @error('authcentralSignupUrl')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">URL for new user registration</div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold">Student Registration URL</label>
                                <input type="url" wire:model="authcentralStudentRegistrationUrl" 
                                       class="form-control @error('authcentralStudentRegistrationUrl') is-invalid @enderror"
                                       placeholder="https://auth.example.edu/student/register">
                                @error('authcentralStudentRegistrationUrl')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">URL for student-specific registration</div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" wire:click="testAuthCentralConnection" class="btn btn-light-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="testAuthCentralConnection">
                                    <i class="fas fa-vial me-2"></i>Test Connection
                                </span>
                                <span wire:loading wire:target="testAuthCentralConnection">
                                    <i class="fas fa-spinner fa-spin me-2"></i>Testing...
                                </span>
                            </button>

                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="saveAuthCentralSettings">
                                    <i class="fas fa-save me-2"></i>Save Settings
                                </span>
                                <span wire:loading wire:target="saveAuthCentralSettings">
                                    <i class="fas fa-spinner fa-spin me-2"></i>Saving...
                                </span>
                            </button>
                        </div>
                    </form>

                    {{-- Connection Test Result --}}
                    @if($testConnectionResult && $testConnectionStatus !== 'testing')
                        <div class="alert alert-{{ $testConnectionStatus === 'success' ? 'success' : ($testConnectionStatus === 'warning' ? 'warning' : 'danger') }} mt-4" role="alert">
                            <i class="fas fa-{{ $testConnectionStatus === 'success' ? 'check-circle' : 'exclamation-triangle' }} me-2"></i>
                            {{ $testConnectionResult }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- API Keys Management Tab --}}
        <div class="tab-pane fade" id="api-keys" role="tabpanel" aria-labelledby="api-keys-tab">
            <div class="card mb-5">
                <div class="card-header">
                    <h3 class="card-title fw-bold">
                        <i class="fas fa-key me-2 text-warning"></i>Password Sync API Key
                    </h3>
                </div>
                <div class="card-body">
                    {{-- Current API Key Display --}}
                    <div class="mb-5">
                        <label class="form-label fw-bold">Current API Key</label>
                        <div class="input-group">
                            <input type="text" readonly value="{{ $maskedApiKey }}" class="form-control bg-light" id="apiKeyDisplay">
                            <button type="button" wire:click="toggleShowApiKey" class="btn btn-light-primary">
                                <i class="fas fa-eye{{ $showApiKey ? '-slash' : '' }} me-2"></i>
                                {{ $showApiKey ? 'Hide' : 'Show' }}
                            </button>
                            @if($passwordSyncApiKey)
                            <button type="button" class="btn btn-light-success" onclick="copyApiKey()">
                                <i class="fas fa-copy me-2"></i>Copy
                            </button>
                            @endif
                        </div>
                        <div class="form-text mt-2">
                            @if($keyGeneratedAt)
                                <i class="fas fa-clock me-1"></i>Generated: {{ \Carbon\Carbon::parse($keyGeneratedAt)->format('M d, Y H:i:s') }}
                            @else
                                <i class="fas fa-info-circle me-1"></i>No API key generated yet
                            @endif
                        </div>
                    </div>

                    {{-- Generate New Key --}}
                    <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                        <i class="fas fa-exclamation-triangle fs-2 me-3"></i>
                        <div>
                            <strong>Warning:</strong> Generating a new API key will invalidate the current key. 
                            Make sure to update AuthCentral with the new key immediately.
                        </div>
                    </div>

                    <button type="button" wire:click="generateApiKey" 
                            class="btn btn-warning" 
                            wire:loading.attr="disabled"
                            onclick="return confirm('Are you sure you want to generate a new API key? This will invalidate the current key and may break existing integrations.')">
                        <span wire:loading.remove wire:target="generateApiKey">
                            <i class="fas fa-sync-alt me-2"></i>Generate New API Key
                        </span>
                        <span wire:loading wire:target="generateApiKey">
                            <i class="fas fa-spinner fa-spin me-2"></i>Generating...
                        </span>
                    </button>

                    {{-- Usage Information --}}
                    <div class="mt-5 p-4 bg-light rounded">
                        <h5 class="fw-bold mb-3"><i class="fas fa-info-circle me-2 text-info"></i>Integration Instructions</h5>
                        <p class="mb-2"><strong>For AuthCentral Configuration:</strong></p>
                        <ol class="mb-3">
                            <li>Copy the API key above</li>
                            <li>Add to AuthCentral's <code>.env</code> file:
                                <pre class="bg-dark text-white p-3 rounded mt-2 mb-0"><code>CIS_PASSWORD_SYNC_API_KEY={{ $passwordSyncApiKey ? '"' . $maskedApiKey . '"' : 'your-api-key-here' }}</code></pre>
                            </li>
                            <li>Clear AuthCentral's config cache: <code>php artisan config:clear</code></li>
                        </ol>
                        
                        <p class="mb-2 mt-4"><strong>Security Best Practices:</strong></p>
                        <ul class="mb-0">
                            <li>Store the API key securely in environment variables</li>
                            <li>Never commit API keys to version control</li>
                            <li>Rotate keys periodically for enhanced security</li>
                            <li>Monitor API usage logs for suspicious activity</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Webhook Settings Tab --}}
        <div class="tab-pane fade" id="webhooks" role="tabpanel" aria-labelledby="webhooks-tab">
            <div class="card mb-5">
                <div class="card-header">
                    <h3 class="card-title fw-bold">
                        <i class="fas fa-exchange-alt me-2 text-success"></i>Password Synchronization Webhook
                    </h3>
                </div>
                <div class="card-body">
                    <form wire:submit="savePasswordSyncSettings">
                        {{-- Enable/Disable Toggle --}}
                        <div class="mb-5">
                            <label class="form-label fw-bold">Password Synchronization</label>
                            <div class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" wire:model="passwordSyncEnabled" id="passwordSyncToggle">
                                <label class="form-check-label" for="passwordSyncToggle">
                                    {{ $passwordSyncEnabled ? 'Enabled' : 'Disabled' }}
                                </label>
                            </div>
                            <div class="form-text">
                                When enabled, password changes in AuthCentral will automatically sync to CIS
                            </div>
                        </div>

                        {{-- Webhook URL Display --}}
                        <div class="mb-5">
                            <label class="form-label fw-bold">CIS Webhook Endpoint</label>
                            <div class="input-group">
                                <input type="text" readonly value="{{ $cisWebhookUrl }}" class="form-control bg-light">
                                <button type="button" class="btn btn-light-success" onclick="copyWebhookUrl()">
                                    <i class="fas fa-copy me-2"></i>Copy
                                </button>
                            </div>
                            <div class="form-text">Use this URL in AuthCentral's password sync configuration</div>
                        </div>

                        {{-- Health Check URL --}}
                        <div class="mb-5">
                            <label class="form-label fw-bold">Health Check Endpoint</label>
                            <div class="input-group">
                                <input type="text" readonly value="{{ $cisWebhookUrl }}-health" class="form-control bg-light">
                                <button type="button" wire:click="testWebhookConnection" class="btn btn-light-primary" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="testWebhookConnection">
                                        <i class="fas fa-heartbeat me-2"></i>Test
                                    </span>
                                    <span wire:loading wire:target="testWebhookConnection">
                                        <i class="fas fa-spinner fa-spin me-2"></i>Testing...
                                    </span>
                                </button>
                            </div>
                        </div>

                        {{-- Webhook Test Result --}}
                        @if($testConnectionResult && $testConnectionStatus !== 'testing')
                            <div class="alert alert-{{ $testConnectionStatus === 'success' ? 'success' : 'danger' }} mb-4" role="alert">
                                <i class="fas fa-{{ $testConnectionStatus === 'success' ? 'check-circle' : 'exclamation-circle' }} me-2"></i>
                                {{ $testConnectionResult }}
                            </div>
                        @endif

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="savePasswordSyncSettings">
                                    <i class="fas fa-save me-2"></i>Save Settings
                                </span>
                                <span wire:loading wire:target="savePasswordSyncSettings">
                                    <i class="fas fa-spinner fa-spin me-2"></i>Saving...
                                </span>
                            </button>
                        </div>
                    </form>

                    {{-- Webhook Documentation --}}
                    <div class="mt-5 p-4 bg-light rounded">
                        <h5 class="fw-bold mb-3"><i class="fas fa-book me-2 text-info"></i>Webhook Documentation</h5>
                        
                        <p class="mb-2"><strong>Endpoint:</strong> <code>POST {{ $cisWebhookUrl }}</code></p>
                        
                        <p class="mb-2 mt-3"><strong>Required Headers:</strong></p>
                        <pre class="bg-dark text-white p-3 rounded"><code>Content-Type: application/json
X-API-Key: {{ $passwordSyncApiKey ? $maskedApiKey : 'your-api-key' }}</code></pre>
                        
                        <p class="mb-2 mt-3"><strong>Request Body:</strong></p>
                        <pre class="bg-dark text-white p-3 rounded"><code>{
    "email": "user@example.com",
    "password": "plaintext-password",
    "event": "password_changed|user_registered|password_reset",
    "api_key": "{{ $passwordSyncApiKey ? $maskedApiKey : 'your-api-key' }}"
}</code></pre>

                        <p class="mb-2 mt-3"><strong>Response (Success):</strong></p>
                        <pre class="bg-dark text-white p-3 rounded mb-0"><code>{
    "success": true,
    "message": "Password synchronized successfully"
}</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal for Newly Generated Key --}}
    @if($showGeneratedKey && $newlyGeneratedKey)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="fas fa-key me-2"></i>New API Key Generated
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeGeneratedKeyModal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-4">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Important:</strong> Copy this key now! You won't be able to see it again.
                    </div>
                    
                    <label class="form-label fw-bold">Your New API Key:</label>
                    <div class="input-group mb-3">
                        <input type="text" readonly value="{{ $newlyGeneratedKey }}" class="form-control font-monospace" id="newApiKey">
                        <button type="button" class="btn btn-success" onclick="copyNewApiKey()">
                            <i class="fas fa-copy me-2"></i>Copy
                        </button>
                    </div>
                    
                    <p class="text-muted mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Update this key in AuthCentral's <code>.env</code> file immediately.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" wire:click="closeGeneratedKeyModal">
                        I've Copied the Key
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    function copyApiKey() {
        const apiKey = @json($passwordSyncApiKey);
        navigator.clipboard.writeText(apiKey).then(() => {
            alert('API key copied to clipboard!');
        });
    }
    
    function copyNewApiKey() {
        const input = document.getElementById('newApiKey');
        input.select();
        navigator.clipboard.writeText(input.value).then(() => {
            alert('New API key copied to clipboard!');
        });
    }
    
    function copyWebhookUrl() {
        const url = @json($cisWebhookUrl);
        navigator.clipboard.writeText(url).then(() => {
            alert('Webhook URL copied to clipboard!');
        });
    }
</script>
@endpush
