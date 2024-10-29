<?php
namespace App\Http\Livewire;

use Livewire\Component;

class QrCodeModal extends Component
{
    public $qrCodeUrl;

    protected $listeners = ['openQrCodeModal'];

    public function openQrCodeModal($qrCodeUrl)
    {
        $this->qrCodeUrl = $qrCodeUrl;
        $this->dispatchBrowserEvent('open-qr-code-modal');
    }

    public function render()
    {
        return view('livewire.qr-code-modal');
    }
}
