<?php

namespace App\Livewire\Communication;

use App\Models\SmsLog;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class SmsLogs extends Component
{
    use WithPagination;

    // pagination theme = bootstrap
    protected $paginationTheme = 'bootstrap';

    public $searchTerm = '';

    public $statusFilter = '';

    public $typeFilter = '';

    public $providerFilter = '';

    public $startDate = '';

    public $endDate = '';

    public $selectedLog = null;

    public function viewDetails($id)
    {
        try {
            $this->selectedLog = SmsLog::findOrFail($id);
            $this->dispatch('openLogDetailsModal');
        } catch (\Exception $e) {
            Log::error('Failed to load SMS log details', [
                'error' => $e->getMessage(),
                'id' => $id,
            ]);
            session()->flash('error', 'Failed to load SMS log details.');
        }
    }

    public function resendSms($id)
    {
        try {
            $log = SmsLog::findOrFail($id);

            // Get the SMS service from the container
            $smsService = app(\App\Services\Communication\SMS\SmsServiceInterface::class);

            // Resend the SMS
            $result = $smsService->sendSingle(
                $log->recipient,
                $log->message,
                [
                    'user_id' => auth()->id(),
                ]
            );

            if ($result['success']) {
                session()->flash('success', 'SMS resent successfully.');
            } else {
                session()->flash('error', 'Failed to resend SMS: '.($result['message'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            Log::error('Failed to resend SMS', [
                'error' => $e->getMessage(),
                'id' => $id,
            ]);
            session()->flash('error', 'Failed to resend SMS: '.$e->getMessage());
        }
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedTypeFilter()
    {
        $this->resetPage();
    }

    public function updatedProviderFilter()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        try {
            $log = SmsLog::findOrFail($id);
            $log->delete();
            session()->flash('success', 'SMS log deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete SMS log', [
                'error' => $e->getMessage(),
                'id' => $id,
            ]);
            session()->flash('error', 'Failed to delete SMS log: '.$e->getMessage());
        }
    }

    public function render()
    {
        $query = SmsLog::query();

        // Apply search filters
        if (! empty($this->searchTerm)) {
            $query->where(function ($q) {
                $q->where('recipient', 'like', '%'.$this->searchTerm.'%')
                    ->orWhere('message', 'like', '%'.$this->searchTerm.'%');
            });
        }

        if (! empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        if (! empty($this->typeFilter)) {
            $query->where('type', $this->typeFilter);
        }

        if (! empty($this->providerFilter)) {
            $query->where('provider', $this->providerFilter);
        }

        if (! empty($this->startDate)) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }

        if (! empty($this->endDate)) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        // Get distinct providers for filter dropdown
        $providers = SmsLog::select('provider')->distinct()->pluck('provider')->toArray();

        // Get logs with pagination
        $logs = $query->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.communication.sms-logs', [
            'logs' => $logs,
            'providers' => $providers,
        ])->layout('components.dashboard.default', ['title' => 'SMS Logs']);
    }
}
