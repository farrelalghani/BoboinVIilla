<?php

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.admin')] class extends Component
{
    public function with(): array
    {
        $heroImage = SiteSetting::get('hero_image');
        $heroImageUrl = $heroImage ? Storage::url($heroImage) : '/images/images.jpg';

        return [
            'heroImage'      => $heroImage,
            'heroImageUrl'   => $heroImageUrl,
            'heroTitle'      => SiteSetting::get('hero_title', 'Temukan Villa Impian Anda'),
            'heroSubtitle'   => SiteSetting::get('hero_subtitle', 'Ribuan villa premium di destinasi wisata terbaik Indonesia siap menyambut Anda.'),
            'isCustomBanner' => !empty($heroImage),
        ];
    }
}; ?>

<div>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-6 sm:mb-8">
        <div>
            <h2 class="text-xl sm:text-2xl font-semibold text-gray-800">Pengaturan Tampilan</h2>
            <p class="text-sm text-gray-500 mt-0.5">Kelola foto cover banner dan konten tampilan halaman utama tamu</p>
        </div>
        <a href="{{ route('home') }}" target="_blank"
           class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 hover:text-[#1e3a8a] hover:border-[#1e3a8a] text-sm font-medium rounded-xl shadow-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
            Lihat Halaman Tamu
        </a>
    </div>

    {{-- Notifikasi Sukses --}}
    @if(session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl p-4 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <span class="text-xl">✅</span>
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 text-lg">✕</button>
        </div>
    @endif

    {{-- Error Validasi --}}
    @if($errors->any())
        <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl p-4 shadow-sm">
            <p class="text-sm font-semibold mb-1">Gagal memperbarui banner:</p>
            <ul class="list-disc list-inside text-xs space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">
        {{-- Form Pengaturan Banner --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 sm:p-6">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-6">
                    <div>
                        <h3 class="font-semibold text-gray-800 text-base">Banner Cover Halaman Tamu (Hero)</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Foto latar belakang besar yang pertama kali dilihat pengunjung saat membuka web</p>
                    </div>
                    @if($isCustomBanner)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                            Foto Kustom Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                            Foto Bawaan (Default)
                        </span>
                    @endif
                </div>

                <form method="POST" action="{{ route('admin.pengaturan.banner') }}" enctype="multipart/form-data"
                      x-data="{
                          previewUrl: '{{ $heroImageUrl }}',
                          fileChosen(e) {
                              const file = e.target.files[0];
                              if (file) {
                                  this.previewUrl = URL.createObjectURL(file);
                              }
                          }
                      }"
                      class="space-y-6">
                    @csrf

                    {{-- Upload Foto --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Foto Cover Baru</label>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                            <label class="cursor-pointer inline-flex items-center gap-2 px-4 py-2.5 bg-gray-50 border border-gray-300 hover:bg-gray-100 text-gray-700 text-sm font-medium rounded-xl transition shadow-sm">
                                <svg class="w-4 h-4 text-[#1e3a8a]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>Pilih Gambar dari Laptop/HP</span>
                                <input type="file" name="hero_image" class="hidden"
                                       accept="image/jpeg,image/png,image/webp"
                                       @change="fileChosen($event)">
                            </label>
                            <span class="text-xs text-gray-400">Format: JPG, PNG, WebP (iPhone HEIC otomatis dikonversi). Maks. 5MB.</span>
                        </div>
                    </div>

                    {{-- Teks Judul & Subjudul --}}
                    <div class="grid grid-cols-1 gap-4 pt-4 border-t border-gray-100">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Judul Utama Banner</label>
                            <input type="text" name="hero_title" value="{{ old('hero_title', $heroTitle) }}"
                                   placeholder="Contoh: Temukan Villa Impian Anda"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a8a]/20 focus:border-[#1e3a8a] transition">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Subjudul / Deskripsi Pendek</label>
                            <input type="text" name="hero_subtitle" value="{{ old('hero_subtitle', $heroSubtitle) }}"
                                   placeholder="Contoh: Ribuan villa premium di destinasi wisata terbaik..."
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a8a]/20 focus:border-[#1e3a8a] transition">
                        </div>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="pt-4 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3">
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#1e3a8a] hover:bg-blue-900 text-white text-sm font-semibold rounded-xl shadow transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>

                {{-- Form Reset Terpisah --}}
                @if($isCustomBanner)
                    <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Ingin kembali ke gambar bawaan?</p>
                            <p class="text-xs text-gray-400">Menghapus foto cover kustom dan menggunakan foto default Boboin Villa.</p>
                        </div>
                        <form method="POST" action="{{ route('admin.pengaturan.banner.reset') }}"
                              onsubmit="return confirm('Yakin ingin mereset foto cover ke tampilan bawaan?')">
                            @csrf
                            <button type="submit"
                                    class="px-3.5 py-2 text-rose-600 hover:text-rose-700 hover:bg-rose-50 border border-rose-200 text-xs font-semibold rounded-xl transition">
                                Reset ke Bawaan
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        {{-- Kolom Pratinjau (Live Preview) --}}
        <div>
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 sm:p-6 sticky top-6">
                <h4 class="font-semibold text-gray-800 text-sm mb-3 flex items-center gap-2">
                    <span>👁️</span> Pratinjau Tampilan Beranda
                </h4>

                <div class="w-full rounded-xl overflow-hidden relative shadow-md aspect-[16/10] bg-gray-900">
                    <img src="{{ $heroImageUrl }}" alt="Preview Cover"
                         class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-[#1a3a52]/70 flex flex-col items-center justify-center p-4 text-center">
                        <p class="text-white font-medium text-base sm:text-lg leading-tight mb-1.5 drop-shadow">
                            {{ $heroTitle }}
                        </p>
                        <p class="text-white/80 text-[11px] leading-relaxed line-clamp-2 drop-shadow-sm mb-3">
                            {{ $heroSubtitle }}
                        </p>
                        <div class="w-3/4 bg-white/90 rounded-lg py-1.5 px-3 flex items-center justify-between text-[10px] text-gray-400 shadow">
                            <span>Check-in — Check-out</span>
                            <span class="bg-[#3a6484] text-white px-2 py-0.5 rounded text-[9px] font-medium">Cari</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 p-3 bg-blue-50/60 rounded-xl border border-blue-100 text-xs text-blue-900 space-y-1">
                    <p class="font-semibold flex items-center gap-1">
                        <span>💡</span> Tips Foto Cover:
                    </p>
                    <p class="text-blue-800/80 leading-relaxed">
                        Gunakan foto pemandangan villa bersudut lebar (*landscape*), dengan resolusi minimal 1920x1080 pixel untuk hasil terbaik di layar laptop maupun HP.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

