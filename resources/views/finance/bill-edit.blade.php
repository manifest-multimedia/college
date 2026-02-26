<x-dashboard.default title="Edit Bill">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="card-title">
                    <i class="ki-duotone ki-document fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Edit Bill
                </h1>
                <div>
                    <a href="{{ route('finance.bill.view', ['id' => $billId]) }}" class="btn btn-secondary">
                        <i class="ki-duotone ki-arrow-left fs-2"></i> Back to Bill
                    </a>
                    <a href="{{ route('finance.billing') }}" class="btn btn-light ms-2">
                        <i class="ki-duotone ki-exit-right fs-2"></i> Back to Billing
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <livewire:finance.bill-editor :billId="$billId" />
        </div>
    </div>
</x-dashboard.default>

