<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.auth')]
class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function login()
    {
        $credentials = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ]);

        if (! Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $this->remember)) {
            $this->addError('email', 'Invalid credentials.');

            return null;
        }

        request()->session()->regenerate();

        $user = Auth::user();

        if ($user->hasRole('SuperAdmin')) {
            return redirect()->route('super-admin.dashboard');
        }

        if ($user->hasRole('Admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('Team')) {
            return redirect()->route('team.dashboard');
        }

        return redirect('/');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
