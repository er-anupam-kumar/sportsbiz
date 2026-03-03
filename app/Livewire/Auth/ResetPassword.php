<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.auth')]
class ResetPassword extends Component
{
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public ?string $status = null;

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = (string) request()->query('email', '');
    }

    public function resetPassword()
    {
        $validated = $this->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $result = Password::reset($validated, function ($user, string $password) {
            $user->password = $password;
            $user->save();
        });

        if ($result === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', __($result));
        }

        $this->status = __($result);

        return null;
    }

    public function render()
    {
        return view('livewire.auth.reset-password');
    }
}
