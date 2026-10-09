<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone_number = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function register(): void
    {
        $validated = $this->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone_number'          => ['nullable', 'string', 'max:20'],
            'password'              => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);
        $user->assignRole('guest');

        event(new Registered($user));

        Auth::login($user);

        $this->redirect(route('home'), navigate: true);
    }
}; ?>

<div>
    <h2 class="text-2xl font-medium text-[#3a6484] mb-2">Buat Akun</h2>
    <p class="text-sm text-gray-500 mb-8">Daftar dan mulai temukan villa terbaik</p>

    <form wire:submit="register" class="space-y-5">
        <div>
            <label for="name" class="block text-sm font-medium text-[#3a6484] mb-1">Nama Lengkap</label>
            <input
                wire:model="name"
                id="name"
                type="text"
                name="name"
                required
                autofocus
                autocomplete="name"
                placeholder="Nama lengkap Anda"
                class="w-full px-4 py-3 rounded-lg border border-[#3a6484]/30 focus:outline-none focus:ring-2 focus:ring-[#3a6484]/40 focus:border-[#3a6484] text-gray-800 placeholder-gray-400 transition"
            />
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-[#3a6484] mb-1">Email</label>
            <input
                wire:model="email"
                id="email"
                type="email"
                name="email"
                required
                autocomplete="username"
                placeholder="email@contoh.com"
                class="w-full px-4 py-3 rounded-lg border border-[#3a6484]/30 focus:outline-none focus:ring-2 focus:ring-[#3a6484]/40 focus:border-[#3a6484] text-gray-800 placeholder-gray-400 transition"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div>
            <label for="phone_number" class="block text-sm font-medium text-[#3a6484] mb-1">
                Nomor Telepon <span class="text-gray-400 font-normal">(opsional)</span>
            </label>
            <input
                wire:model="phone_number"
                id="phone_number"
                type="tel"
                name="phone_number"
                autocomplete="tel"
                placeholder="08xxxxxxxxxx"
                class="w-full px-4 py-3 rounded-lg border border-[#3a6484]/30 focus:outline-none focus:ring-2 focus:ring-[#3a6484]/40 focus:border-[#3a6484] text-gray-800 placeholder-gray-400 transition"
            />
            <x-input-error :messages="$errors->get('phone_number')" class="mt-1" />
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-[#3a6484] mb-1">Password</label>
            <input
                wire:model="password"
                id="password"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                placeholder="Min. 8 karakter"
                class="w-full px-4 py-3 rounded-lg border border-[#3a6484]/30 focus:outline-none focus:ring-2 focus:ring-[#3a6484]/40 focus:border-[#3a6484] text-gray-800 placeholder-gray-400 transition"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-[#3a6484] mb-1">Konfirmasi Password</label>
            <input
                wire:model="password_confirmation"
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="Ulangi password"
                class="w-full px-4 py-3 rounded-lg border border-[#3a6484]/30 focus:outline-none focus:ring-2 focus:ring-[#3a6484]/40 focus:border-[#3a6484] text-gray-800 placeholder-gray-400 transition"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <button
            type="submit"
            class="w-full py-3 bg-[#3a6484] text-white rounded-lg font-medium hover:bg-[#2f5370] transition focus:outline-none focus:ring-2 focus:ring-[#3a6484]/50"
        >
            Daftar
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500">
        Sudah punya akun?
        <a href="{{ route('login') }}" wire:navigate class="text-[#3a6484] font-medium hover:underline">
            Masuk di sini
        </a>
    </p>
</div>
