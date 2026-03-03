<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.auth')]
class ForgotPassword extends Component
{
    public string $email = '';
    public ?string $status = null;

    public function sendResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'email'],
        ]);

        $result = Password::sendResetLink(['email' => $this->email]);
        $this->status = __($result);
    }

    public function render()
    {
        return view('livewire.auth.forgot-password');
    }
}
