<?php

namespace App\Livewire\Admin;

use App\Models\PaymentProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class PaymentProvidersManager extends Component
{
    public $providers;
    
    // Form fields
    public $name;
    public $code;
    
    // UI State
    public $showForm = false;
    public $newlyGeneratedKey = null;
    public $showGeneratedKey = false;
    
    public function mount()
    {
        $this->loadProviders();
    }
    
    public function loadProviders()
    {
        $this->providers = PaymentProvider::with('creator')->orderBy('created_at', 'desc')->get();
    }
    
    public function toggleForm()
    {
        $this->showForm = !$this->showForm;
        $this->reset(['name', 'code']);
        $this->resetValidation();
    }
    
    public function generateCredentials()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:payment_providers,code',
        ]);

        try {
            DB::beginTransaction();

            // Create payment provider
            $provider = PaymentProvider::create([
                'name' => $this->name,
                'code' => strtolower($this->code),
                'status' => 'active',
                'created_by' => auth()->id(),
            ]);

            // Generate Sanctum token
            $tokenResult = $provider->createToken('API Token for ' . $provider->name);

            DB::commit();

            Log::info("Generated API credentials for payment provider {$provider->name} via UI", [
                'provider_id' => $provider->id,
                'generated_by' => auth()->id(),
            ]);

            $this->newlyGeneratedKey = $tokenResult->plainTextToken;
            $this->showGeneratedKey = true;
            $this->showForm = false;
            
            $this->loadProviders();
            
            session()->flash('success', 'API credentials generated successfully.');
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Failed to generate payment provider credentials via UI', [
                'error' => $e->getMessage(),
                'name' => $this->name,
            ]);

            session()->flash('error', 'Failed to generate credentials: ' . $e->getMessage());
        }
    }
    
    public function closeGeneratedKeyModal()
    {
        $this->showGeneratedKey = false;
        $this->newlyGeneratedKey = null;
    }

    public function toggleStatus($providerId)
    {
        $provider = PaymentProvider::findOrFail($providerId);
        $provider->status = $provider->status === 'active' ? 'inactive' : 'active';
        $provider->save();
        
        $this->loadProviders();
        session()->flash('success', 'Provider status updated.');
    }
    
    public function render()
    {
        return view('livewire.admin.payment-providers-manager');
    }
}
