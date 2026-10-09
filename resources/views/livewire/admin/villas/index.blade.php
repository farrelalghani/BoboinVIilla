<?php

use App\Models\Villa;
use App\Models\VillaPeakSeason;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.admin')] class extends Component
{
    public bool   $showModal    = false;
    public bool   $showConfirm  = false;
    public bool   $showImage    = false;
    public ?int   $editingId    = null;
    public ?int   $deletingId   = null;
    public ?int   $imageVillaId = null;

    // Form fields
    public string $name            = '';
    public string $description     = '';
    public string $weekday_price   = '';
    public string $weekend_price   = '';
    public string $capacity        = '';
    public string $city            = '';
    public string $address         = '';
    public string $status               = 'active';
    public array  $selectedFacilities   = [];
    public array  $nearbyDestinations   = [];
    public array  $houseRules           = [];

    // Peak season form
    public string $peakStart = '';
    public string $peakEnd   = '';
    public string $peakPrice = '';
    public string $peakLabel = '';

    public const FACILITIES = [
        'kolam_renang' => 'Kolam Renang',
        'wifi'         => 'WiFi',
        'ac'           => 'AC',
        'dapur'        => 'Dapur',
        'parkir'       => 'Parkir',
        'bbq'          => 'Area BBQ',
        'tv'           => 'TV',
        'mesin_cuci'   => 'Mesin Cuci',
        'karaoke'      => 'Karaoke',
        'taman'        => 'Taman',
        'cctv'         => 'CCTV',
        'sarapan'      => 'Sarapan',
    ];

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'description'   => ['required', 'string'],
            'weekday_price' => ['required', 'numeric', 'min:0'],
            'weekend_price' => ['required', 'numeric', 'min:0'],
            'capacity'      => ['required', 'integer', 'min:1'],
            'city'          => ['required', 'string', 'max:100'],
            'address'       => ['required', 'string'],
            'status'        => ['required', 'in:active,inactive'],
        ];
    }

    public const CATEGORIES = [
        'pantai'       => ['label' => 'Pantai',        'icon' => '🏖️'],
        'gunung'       => ['label' => 'Gunung',        'icon' => '⛰️'],
        'air_terjun'   => ['label' => 'Air Terjun',    'icon' => '💧'],
        'danau'        => ['label' => 'Danau',         'icon' => '🏞️'],
        'kuliner'      => ['label' => 'Kuliner',       'icon' => '🍽️'],
        'pasar'        => ['label' => 'Pasar/Belanja', 'icon' => '🛍️'],
        'taman_wisata' => ['label' => 'Taman Wisata',  'icon' => '🌿'],
        'lainnya'      => ['label' => 'Lainnya',       'icon' => '📍'],
    ];

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'description', 'weekday_price', 'weekend_price', 'capacity', 'city', 'address', 'selectedFacilities', 'nearbyDestinations', 'houseRules']);
        $this->status    = 'active';
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $villa = Villa::findOrFail($id);
        $this->editingId            = $villa->id;
        $this->name                 = $villa->name;
        $this->description          = $villa->description;
        $this->weekday_price        = $villa->weekday_price;
        $this->weekend_price        = $villa->weekend_price;
        $this->capacity             = $villa->capacity;
        $this->city                 = $villa->city;
        $this->address              = $villa->address;
        $this->status               = $villa->status;
        $this->selectedFacilities   = $villa->facilities ?? [];
        $this->nearbyDestinations   = $villa->nearby_destinations ?? [];
        $this->houseRules           = $villa->house_rules ?? [];
        $this->showModal            = true;
    }

    public function addPeakSeason(): void
    {
        $this->validate([
            'peakStart' => ['required', 'date'],
            'peakEnd'   => ['required', 'date', 'after_or_equal:peakStart'],
            'peakPrice' => ['required', 'numeric', 'min:0'],
            'peakLabel' => ['nullable', 'string', 'max:100'],
        ]);

        VillaPeakSeason::create([
            'villa_id'   => $this->editingId,
            'start_date' => $this->peakStart,
            'end_date'   => $this->peakEnd,
            'price'      => $this->peakPrice,
            'label'      => $this->peakLabel ?: null,
        ]);

        $this->reset(['peakStart', 'peakEnd', 'peakPrice', 'peakLabel']);
    }

    public function removePeakSeason(int $id): void
    {
        VillaPeakSeason::where('id', $id)->delete();
    }

    public function addDestination(): void
    {
        $this->nearbyDestinations[] = ['name' => '', 'distance' => '', 'category' => 'lainnya'];
    }

    public function removeDestination(int $index): void
    {
        array_splice($this->nearbyDestinations, $index, 1);
    }

    public function addHouseRule(): void
    {
        $this->houseRules[] = '';
    }

    public function removeHouseRule(int $index): void
    {
        array_splice($this->houseRules, $index, 1);
    }

    public function save(): void
    {
        $data = $this->validate();
        $data['facilities']          = $this->selectedFacilities;
        $data['nearby_destinations'] = collect($this->nearbyDestinations)
            ->filter(fn($d) => !empty($d['name']))
            ->values()
            ->toArray();
        $data['house_rules']         = collect($this->houseRules)
            ->filter(fn($r) => !empty(trim($r)))
            ->values()
            ->toArray();

        if ($this->editingId) {
            $villa = Villa::findOrFail($this->editingId);
            if ($villa->name !== $data['name']) {
                $data['slug'] = Str::slug($data['name']);
            }
            $villa->update($data);
        } else {
            $slug  = Str::slug($data['name']);
            $count = Villa::where('slug', 'like', $slug . '%')->count();
            $data['slug'] = $count ? $slug . '-' . ($count + 1) : $slug;
            Villa::create($data);
        }

        $this->showModal = false;
        $this->reset(['editingId', 'name', 'description', 'weekday_price', 'weekend_price', 'capacity', 'city', 'address', 'selectedFacilities', 'nearbyDestinations', 'houseRules']);
        $this->status = 'active';
    }

    public function openImageUpload(int $id): void
    {
        $this->imageVillaId = $id;
        $this->showImage    = true;
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId  = $id;
        $this->showConfirm = true;
    }

    public function delete(): void
    {
        $villa = Villa::findOrFail($this->deletingId);
        if ($villa->image) {
            Storage::disk('public')->delete($villa->image);
        }
        $villa->delete();
        $this->showConfirm = false;
        $this->deletingId  = null;
    }

    public function toggleStatus(int $id): void
    {
        $villa = Villa::findOrFail($id);
        $villa->update(['status' => $villa->status === 'active' ? 'inactive' : 'active']);
    }

    public function with(): array
    {
        return [
            'villas'     => Villa::with('peakSeasons')->latest()->get(),
            'facilities' => self::FACILITIES,
            'categories' => self::CATEGORIES,
        ];
    }
}; ?>

<div>
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Kelola Villa</h2>
        <button wire:click="openCreate"
            class="flex items-center gap-2 px-4 py-2.5 bg-[#1e3a8a] text-white rounded-xl text-sm font-medium hover:bg-blue-800 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Villa
        </button>
    </div>

    @if(session('image_success'))
        <div class="mb-4 flex items-center gap-2 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-xl">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('image_success') }}
        </div>
    @endif

    {{-- Tabel --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        @if($villas->isEmpty())
            <div class="px-6 py-16 text-center text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/>
                </svg>
                <p class="font-medium">Belum ada villa.</p>
                <p class="text-sm mt-1">Klik "Tambah Villa" untuk mulai.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                        <tr>
                            <th class="px-5 py-3 text-left">Foto</th>
                            <th class="px-5 py-3 text-left">Nama Villa</th>
                            <th class="px-5 py-3 text-left">Kota</th>
                            <th class="px-5 py-3 text-left">Kapasitas</th>
                            <th class="px-5 py-3 text-left">Harga (Weekday / Weekend)</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($villas as $villa)
                            <tr class="hover:bg-gray-50 transition">
                                {{-- Thumbnail --}}
                                <td class="px-5 py-3.5">
                                    @if($villa->image)
                                        <img src="{{ Storage::url($villa->image) }}"
                                            class="w-16 h-12 object-cover rounded-lg border border-gray-200 cursor-pointer hover:opacity-80 transition"
                                            wire:click="openImageUpload({{ $villa->id }})"
                                            title="Klik untuk ganti foto">
                                    @else
                                        <button wire:click="openImageUpload({{ $villa->id }})"
                                            class="w-16 h-12 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center hover:border-[#1e3a8a] hover:bg-blue-50 transition group">
                                            <svg class="w-5 h-5 text-gray-300 group-hover:text-[#1e3a8a]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </button>
                                    @endif
                                </td>

                                <td class="px-5 py-3.5">
                                    <p class="font-medium text-gray-800">{{ $villa->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $villa->slug }}</p>
                                    @if($villa->facilities && count($villa->facilities) > 0)
                                        <p class="text-xs text-gray-400 mt-0.5">
                                            {{ collect($villa->facilities)->map(fn($k) => $facilities[$k] ?? $k)->take(3)->implode(', ') }}
                                            @if(count($villa->facilities) > 3)
                                                <span>+{{ count($villa->facilities) - 3 }} lainnya</span>
                                            @endif
                                        </p>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-gray-600">{{ $villa->city }}</td>
                                <td class="px-5 py-3.5 text-gray-600">{{ $villa->capacity }} tamu</td>
                                <td class="px-5 py-3.5 font-medium text-gray-800">
                                    <p>Rp {{ number_format($villa->weekday_price, 0, ',', '.') }} <span class="text-gray-400 font-normal text-xs">weekday</span></p>
                                    <p>Rp {{ number_format($villa->weekend_price, 0, ',', '.') }} <span class="text-gray-400 font-normal text-xs">weekend</span></p>
                                    @if($villa->peakSeasons->isNotEmpty())
                                        <p class="text-xs text-amber-600 mt-0.5">{{ $villa->peakSeasons->count() }} peak season</p>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <button wire:click="toggleStatus({{ $villa->id }})"
                                        class="px-2.5 py-1 rounded-full text-xs font-medium transition
                                            {{ $villa->status === 'active'
                                                ? 'bg-green-100 text-green-700 hover:bg-green-200'
                                                : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                        {{ $villa->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button wire:click="openEdit({{ $villa->id }})"
                                            class="p-1.5 text-gray-400 hover:text-[#1e3a8a] hover:bg-blue-50 rounded-lg transition" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <button wire:click="confirmDelete({{ $villa->id }})"
                                            class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Modal Tambah / Edit --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white">
                    <h3 class="font-semibold text-gray-800 text-lg">
                        {{ $editingId ? 'Edit Villa' : 'Tambah Villa Baru' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form wire:submit="save" class="p-6 space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        {{-- Nama --}}
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Villa</label>
                            <input wire:model="name" type="text" placeholder="Contoh: Villa Pantai Indah"
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a8a]/30 focus:border-[#1e3a8a]"/>
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Harga --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Harga Weekday (Rp) <span class="text-gray-400 font-normal">Min-Kam</span></label>
                            <input wire:model="weekday_price" type="number" min="0" placeholder="1500000"
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a8a]/30 focus:border-[#1e3a8a]"/>
                            @error('weekday_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Harga Weekend (Rp) <span class="text-gray-400 font-normal">Jum-Sab</span></label>
                            <input wire:model="weekend_price" type="number" min="0" placeholder="2000000"
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a8a]/30 focus:border-[#1e3a8a]"/>
                            @error('weekend_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Kapasitas --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kapasitas (orang)</label>
                            <input wire:model="capacity" type="number" min="1" placeholder="8"
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a8a]/30 focus:border-[#1e3a8a]"/>
                            @error('capacity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Kota & Status --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                            <select wire:model="city"
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a8a]/30 focus:border-[#1e3a8a]">
                                <option value="">-- Pilih Lokasi --</option>
                                <option value="Lembang, Bandung">Lembang, Bandung</option>
                                <option value="Dago, Bandung">Dago, Bandung</option>
                                <option value="Bogor">Bogor</option>
                            </select>
                            @error('city') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select wire:model="status"
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a8a]/30 focus:border-[#1e3a8a]">
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>

                        {{-- Alamat --}}
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap</label>
                            <input wire:model="address" type="text" placeholder="Jl. Pantai Kuta No. 1..."
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a8a]/30 focus:border-[#1e3a8a]"/>
                            @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Deskripsi --}}
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                            <textarea wire:model="description" rows="3" placeholder="Deskripsikan villa ini..."
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a8a]/30 focus:border-[#1e3a8a] resize-none"></textarea>
                            @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Fasilitas --}}
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fasilitas</label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                @foreach($facilities as $key => $label)
                                    <label class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl border cursor-pointer transition
                                        {{ in_array($key, $selectedFacilities)
                                            ? 'border-[#1e3a8a] bg-blue-50 text-[#1e3a8a]'
                                            : 'border-gray-200 text-gray-600 hover:border-gray-300' }}">
                                        <input type="checkbox"
                                            wire:model="selectedFacilities"
                                            value="{{ $key }}"
                                            class="w-4 h-4 rounded accent-[#1e3a8a]">
                                        <span class="text-sm">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Destinasi Terdekat --}}
                        <div class="sm:col-span-2">
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-sm font-medium text-gray-700">Destinasi Terdekat</label>
                                <button type="button" wire:click="addDestination"
                                    class="flex items-center gap-1 text-xs text-[#1e3a8a] hover:underline">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Tambah Destinasi
                                </button>
                            </div>

                            @if(count($nearbyDestinations) === 0)
                                <p class="text-xs text-gray-400 text-center py-4 border border-dashed border-gray-200 rounded-xl">
                                    Belum ada destinasi. Klik "Tambah Destinasi" untuk menambahkan.
                                </p>
                            @else
                                <div class="space-y-2">
                                    @foreach($nearbyDestinations as $i => $dest)
                                        <div class="flex items-center gap-2">
                                            <select wire:model="nearbyDestinations.{{ $i }}.category"
                                                class="border border-gray-200 rounded-lg px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a8a]/30 focus:border-[#1e3a8a] w-36 shrink-0">
                                                @foreach($categories as $key => $cat)
                                                    <option value="{{ $key }}">{{ $cat['icon'] }} {{ $cat['label'] }}</option>
                                                @endforeach
                                            </select>
                                            <input wire:model="nearbyDestinations.{{ $i }}.name"
                                                type="text" placeholder="Nama destinasi"
                                                class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a8a]/30 focus:border-[#1e3a8a]">
                                            <div class="flex items-center gap-1 shrink-0">
                                                <input wire:model="nearbyDestinations.{{ $i }}.distance"
                                                    type="number" min="0" step="0.1" placeholder="0"
                                                    class="w-16 border border-gray-200 rounded-lg px-2 py-2 text-sm text-center focus:outline-none focus:ring-2 focus:ring-[#1e3a8a]/30 focus:border-[#1e3a8a]">
                                                <span class="text-xs text-gray-400">km</span>
                                            </div>
                                            <button type="button" wire:click="removeDestination({{ $i }})"
                                                class="p-1.5 text-gray-300 hover:text-red-500 transition shrink-0">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Peraturan Khusus Villa --}}
                        <div class="sm:col-span-2">
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-sm font-medium text-gray-700">Peraturan Khusus Villa</label>
                                <button type="button" wire:click="addHouseRule"
                                    class="flex items-center gap-1 text-xs text-[#1e3a8a] hover:underline">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Tambah Peraturan
                                </button>
                            </div>

                            @if(count($houseRules) === 0)
                                <p class="text-xs text-gray-400 text-center py-4 border border-dashed border-gray-200 rounded-xl">
                                    Belum ada peraturan khusus. Klik "Tambah Peraturan" untuk menambahkan (contoh: "Dilarang merokok di dalam villa", "Deposit Rp 500.000").
                                </p>
                            @else
                                <div class="space-y-2">
                                    @foreach($houseRules as $i => $rule)
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-medium text-gray-400 w-5 text-center shrink-0">{{ $i + 1 }}.</span>
                                            <input wire:model="houseRules.{{ $i }}"
                                                type="text" placeholder="Contoh: Dilarang merokok di dalam villa"
                                                class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a8a]/30 focus:border-[#1e3a8a]">
                                            <button type="button" wire:click="removeHouseRule({{ $i }})"
                                                class="p-1.5 text-gray-300 hover:text-red-500 transition shrink-0">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Peak Season --}}
                        @if($editingId)
                            @php $editingVilla = $villas->find($editingId); @endphp
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Peak Season <span class="text-gray-400 font-normal">(harga khusus rentang tanggal tertentu, prioritas tertinggi)</span>
                                </label>

                                @if($editingVilla && $editingVilla->peakSeasons->isNotEmpty())
                                    <div class="space-y-2 mb-3">
                                        @foreach($editingVilla->peakSeasons as $peak)
                                            <div class="flex items-center justify-between gap-2 bg-amber-50 border border-amber-200/80 rounded-xl px-3.5 py-2.5 text-sm">
                                                <div>
                                                    <p class="font-medium text-amber-900">{{ $peak->label ?: 'Peak Season' }}</p>
                                                    <p class="text-xs text-amber-700">
                                                        {{ $peak->start_date->format('d M Y') }} — {{ $peak->end_date->format('d M Y') }}
                                                        · Rp {{ number_format($peak->price, 0, ',', '.') }}/malam
                                                    </p>
                                                </div>
                                                <button type="button" wire:click="removePeakSeason({{ $peak->id }})"
                                                    class="p-1.5 text-amber-400 hover:text-red-500 transition shrink-0">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 items-start bg-gray-50 rounded-xl p-3">
                                    <div>
                                        <input wire:model="peakStart" type="date"
                                            class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a8a]/30 focus:border-[#1e3a8a]">
                                        @error('peakStart') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <input wire:model="peakEnd" type="date"
                                            class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a8a]/30 focus:border-[#1e3a8a]">
                                        @error('peakEnd') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <input wire:model="peakPrice" type="number" min="0" placeholder="Harga/malam"
                                            class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a8a]/30 focus:border-[#1e3a8a]">
                                        @error('peakPrice') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="flex gap-1.5">
                                        <input wire:model="peakLabel" type="text" placeholder="Label (opsional)"
                                            class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a8a]/30 focus:border-[#1e3a8a]">
                                        <button type="button" wire:click="addPeakSeason"
                                            class="shrink-0 px-3 py-2 bg-[#1e3a8a] text-white rounded-lg text-sm hover:bg-blue-800 transition">
                                            +
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="sm:col-span-2 flex items-center gap-2 text-xs text-gray-400 bg-gray-50 rounded-xl px-4 py-3">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Untuk mengganti foto villa, klik thumbnail foto di tabel setelah menyimpan.
                            </div>
                        @endif
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit"
                            class="flex-1 py-3 bg-[#1e3a8a] text-white rounded-xl font-medium hover:bg-blue-800 transition text-sm">
                            {{ $editingId ? 'Simpan Perubahan' : 'Tambah Villa' }}
                        </button>
                        <button type="button" wire:click="$set('showModal', false)"
                            class="px-5 py-3 border border-gray-200 text-gray-600 rounded-xl font-medium hover:bg-gray-50 transition text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal Upload Foto Villa --}}
    @if($showImage && $imageVillaId)
        @php $imageVilla = $villas->find($imageVillaId); @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Foto Villa</h3>
                    <button wire:click="$set('showImage', false)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <p class="text-sm text-gray-500">{{ $imageVilla?->name }}</p>

                    @if($imageVilla?->image)
                        <img src="{{ Storage::url($imageVilla->image) }}"
                            class="w-full h-48 object-cover rounded-xl border border-gray-200">
                    @else
                        <div class="w-full h-40 rounded-xl bg-gray-100 border-2 border-dashed border-gray-300 flex items-center justify-center text-gray-400 text-sm">
                            Belum ada foto
                        </div>
                    @endif

                    @if($errors->has('image'))
                        <p class="text-xs text-red-500">{{ $errors->first('image') }}</p>
                    @endif

                    <form method="POST"
                          action="{{ route('admin.villas.image', $imageVillaId) }}"
                          enctype="multipart/form-data"
                          class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Pilih Foto Baru
                                <span class="text-gray-400 font-normal">(JPG, PNG, WebP — maks. 5MB)</span>
                            </label>
                            <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                                class="block w-full text-sm text-gray-500 border border-gray-200 rounded-xl p-1
                                    file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0
                                    file:text-sm file:font-medium file:bg-[#1e3a8a] file:text-white
                                    hover:file:bg-blue-800 cursor-pointer">
                        </div>
                        <button type="submit"
                            class="w-full py-2.5 bg-[#1e3a8a] text-white rounded-xl font-medium hover:bg-blue-800 transition text-sm">
                            {{ $imageVilla?->image ? 'Ganti Foto' : 'Upload Foto' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Konfirmasi Hapus --}}
    @if($showConfirm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm text-center">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-800 mb-2">Hapus Villa?</h3>
                <p class="text-gray-500 text-sm mb-6">Tindakan ini tidak bisa dibatalkan. Semua data dan foto villa akan ikut terhapus.</p>
                <div class="flex gap-3">
                    <button wire:click="delete"
                        class="flex-1 py-2.5 bg-red-600 text-white rounded-xl font-medium hover:bg-red-700 transition text-sm">
                        Ya, Hapus
                    </button>
                    <button wire:click="$set('showConfirm', false)"
                        class="flex-1 py-2.5 border border-gray-200 text-gray-600 rounded-xl font-medium hover:bg-gray-50 transition text-sm">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
