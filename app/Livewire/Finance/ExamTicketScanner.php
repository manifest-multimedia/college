<?php

namespace App\Livewire\Finance;

use App\Models\ExamEntryTicket;
use App\Services\ExamClearanceManager;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ExamTicketScanner extends Component
{
    public $qrCode;
    public $verificationResult = null;
    public $scannerActive = true;
    
    protected $listeners = [
        'qrCodeScanned' => 'verifyQrCode'
    ];
    
    public function verifyQrCode($qrCode)
    {
        $this->qrCode = $qrCode;
        
        $clearanceManager = new ExamClearanceManager();
        $this->verificationResult = $clearanceManager->verifyExamEntryTicket(
            $this->qrCode,
            Auth::id(),
            'Exam center', // Can be made configurable
            Request::ip()
        );
        
        $this->scannerActive = false;
    }
    
    public function resetScanner()
    {
        $this->qrCode = null;
        $this->verificationResult = null;
        $this->scannerActive = true;
    }
    
    public function render()
    {
        return view('livewire.finance.exam-ticket-scanner');
    }
}