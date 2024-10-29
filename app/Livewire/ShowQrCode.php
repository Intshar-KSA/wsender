<?php

namespace App\Http\Livewire;

use Livewire\Component;

class ShowQrCode extends Component
{
    public function viewQrCode($qrCodeUrl)
    {
        $this->dispatchBrowserEvent('open-modal', [
            'title' => 'QR Code',
            'body' => "<img src='{$qrCodeUrl}' alt='QR Code' />",
        ]);
    }

    public function render()
    {
        return view('livewire.show-qr-code');
    }
}
