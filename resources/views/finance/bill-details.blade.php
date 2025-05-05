<x-dashboard.default title="Bill Details">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="card-title">
                    <i class="ki-duotone ki-document fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Bill Details
                </h1>
                <div>
                    <a href="{{ route('finance.billing') }}" class="btn btn-secondary">
                        <i class="ki-duotone ki-arrow-left fs-2"></i> Back to Bills
                    </a>
                    <a href="{{ url('finance.bill.print', $billId) }}" class="btn btn-info ms-2">
                        <i class="ki-duotone ki-printer fs-2"></i> Print Bill
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <livewire:finance.bill-detail-viewer :billId="$billId" />
        </div>
    </div>
</x-dashboard.default>