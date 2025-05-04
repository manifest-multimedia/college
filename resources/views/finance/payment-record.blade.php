<x-dashboard.default title="Record Payment">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="card-title">
                    <i class="ki-duotone ki-credit-cart fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Record Payment
                </h1>
                <div>
                    <a href="{{ route('finance.billing') }}" class="btn btn-secondary">
                        <i class="ki-duotone ki-arrow-left fs-2"></i> Back to Bills
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <livewire:finance.payment-recorder :billId="$billId" />
        </div>
    </div>
</x-dashboard.default>