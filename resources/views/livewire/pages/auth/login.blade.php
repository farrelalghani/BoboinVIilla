<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $user = auth()->user();

        if ($user->hasRole('admin')) {
            $this->redirect(route('admin.dashboard'), navigate: true);
        } else {
            $this->redirect(route('home'), navigate: true);
        }
    }
}; ?>

<div>
    <h2 class="text-2xl font-medium text-[#3a6484] mb-2">Masuk</h2>
    <p class="text-sm text-gray-500 mb-8">Selamat datang kembali di Boboin Villa</p>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="space-y-5">
        <div>
            <label for="email" class="block text-sm font-medium text-[#3a6484] mb-1">Email</label>
            <input
                wire:model="form.email"
                id="email"
                type="email"
                name="email"
                required
                autofocus
                autocomplete="username"
                placeholder="email@contoh.com"
                class="w-full px-4 py-3 rounded-lg border border-[#3a6484]/30 focus:outline-none focus:ring-2 focus:ring-[#3a6484]/40 focus:border-[#3a6484] text-gray-800 placeholder-gray-400 transition"
            />
            <x-input-error :messages="$errors->get('form.email')" class="mt-1" />
        </div>

        <div>
            <div class="flex items-center justify-between mb-1">
                <label for="password" class="block text-sm font-medium text-[#3a6484]">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" wire:navigate class="text-xs text-[#3a6484]/70 hover:text-[#3a6484] transition">
                        Lupa password?
                    </a>
                @endif
            </div>
            <input
                wire:model="form.password"
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="••••••••"
                class="w-full px-4 py-3 rounded-lg border border-[#3a6484]/30 focus:outline-none focus:ring-2 focus:ring-[#3a6484]/40 focus:border-[#3a6484] text-gray-800 placeholder-gray-400 transition"
            />
            <x-input-error :messages="$errors->get('form.password')" class="mt-1" />
        </div>

        <div class="flex items-center gap-2">
            <input
                wire:model="form.remember"
                id="remember"
                type="checkbox"
                class="w-4 h-4 rounded border-[#3a6484]/40 text-[#3a6484] focus:ring-[#3a6484]/40"
            />
            <label for="remember" class="text-sm text-gray-600">Ingat saya</label>
        </div>

        <button
            type="submit"
            class="w-full py-3 bg-[#3a6484] text-white rounded-lg font-medium hover:bg-[#2f5370] transition focus:outline-none focus:ring-2 focus:ring-[#3a6484]/50"
        >
            Masuk
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500">
        Belum punya akun?
        <a href="{{ route('register') }}" wire:navigate class="text-[#3a6484] font-medium hover:underline">
            Daftar sekarang
        </a>
    </p>
</div>
