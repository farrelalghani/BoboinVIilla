<?php

use App\Services\GeminiChatService;
use Livewire\Volt\Component;

new class extends Component {
    public bool $isOpen = false;
    public string $message = '';
    public array $history = [];

    protected function currentTimestamp(): string
    {
        return now()->timezone(config('app.timezone', 'Asia/Jakarta'))->format('H:i');
    }

    public function mount(): void
    {
        $this->history = [
            [
                'role' => 'assistant',
                'text' => "Halo! Saya **Boboin AI Concierge** ✨\nAda yang bisa saya bantu seputar rekomendasi villa, kapasitas tamu, fasilitas, atau cara pemesanan?",
                'time' => $this->currentTimestamp(),
            ],
        ];
    }

    public function openChat(): void
    {
        $this->isOpen = true;
    }

    public function toggleChat(): void
    {
        $this->isOpen = !$this->isOpen;
    }

    public function closeChat(): void
    {
        $this->isOpen = false;
    }

    public function clearChat(): void
    {
        $this->mount();
    }

    public function askQuickPrompt(string $prompt, ?GeminiChatService $chatService = null): void
    {
        $this->message = $prompt;
        $this->send($chatService);
    }

    public function send(?GeminiChatService $chatService = null): void
    {
        $text = trim($this->message);
        if ($text === '') {
            return;
        }

        $chatService = $chatService ?? app(GeminiChatService::class);

        // Simpan pesan tamu
        $this->history[] = [
            'role' => 'user',
            'text' => $text,
            'time' => $this->currentTimestamp(),
        ];

        $this->message = '';

        // Dapatkan jawaban dari AI Gemini
        $reply = $chatService->reply($this->history, $text);

        $this->history[] = [
            'role' => 'assistant',
            'text' => $reply,
            'time' => $this->currentTimestamp(),
        ];

        // Trigger scroll ke bawah di sisi client
        $this->dispatch('chat-updated');
    }
}; ?>

<div>
    {{-- Satu Launcher Mengambang Ringkas di Pojok Kanan Bawah (Hemat Ruang & Tidak Menghalangi Konten) --}}
    @if(!$isOpen)
        <div x-data="{ menuOpen: false }"
             @click.outside="menuOpen = false"
             class="fixed bottom-4 right-4 sm:bottom-5 sm:right-5 z-40 flex flex-col items-end">

            {{-- Popover Pilihan Cepat: Tanya AI & WhatsApp --}}
            <div x-show="menuOpen"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                 class="mb-2.5 w-64 bg-white rounded-2xl shadow-2xl border border-[#3a6484]/20 p-2 space-y-1 origin-bottom-right"
                 style="display: none;">

                {{-- Pilihan 1: Buka Boboin AI Concierge --}}
                <button wire:click="openChat"
                        @click="menuOpen = false"
                        type="button"
                        class="w-full flex items-center gap-3 p-2.5 rounded-xl hover:bg-[#3a6484]/10 transition text-left group">
                    <div class="relative w-9 h-9 rounded-full bg-white ring-1 ring-[#3a6484]/20 overflow-hidden shrink-0">
                        <img src="{{ asset('images/logo.jpg') }}" alt="Boboin AI" class="w-full h-full object-cover">
                        <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-1 ring-white"></span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold text-[#1a3a52] group-hover:text-[#3a6484] flex items-center gap-1">
                            <span>Tanya Boboin AI</span>
                            <span class="text-amber-500">✨</span>
                        </p>
                        <p class="text-[11px] text-slate-500 truncate">{{ __('Rekomendasi & bantuan instan') }}</p>
                    </div>
                </button>

                {{-- Pilihan 2: WhatsApp Admin --}}
                <a href="https://wa.me/6285724821667?text=Halo%20Boboin%20Villa%2C%20saya%20ingin%20bertanya%20mengenai%20pemesanan%20villa."
                   target="_blank" rel="noopener"
                   @click="menuOpen = false"
                   class="w-full flex items-center gap-3 p-2.5 rounded-xl hover:bg-emerald-50 transition text-left group">
                    <div class="w-9 h-9 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                            <path d="M12 0C5.373 0 0 5.373 0 12c0 2.126.558 4.121 1.532 5.849L.057 23.516a.75.75 0 00.927.927l5.666-1.475A11.934 11.934 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.75a9.734 9.734 0 01-4.964-1.359l-.356-.212-3.693.96.982-3.591-.232-.369A9.733 9.733 0 012.25 12C2.25 6.615 6.615 2.25 12 2.25S21.75 6.615 21.75 12 17.385 21.75 12 21.75z"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold text-gray-800 group-hover:text-emerald-700">{{ __('Hubungi Kami') }} (WhatsApp)</p>
                        <p class="text-[11px] text-slate-500 truncate">{{ __('Chat langsung dengan admin') }}</p>
                    </div>
                </a>
            </div>

            {{-- Tombol Bulat Tunggal Compact (48px × 48px) --}}
            <button @click="menuOpen = !menuOpen"
                    type="button"
                    id="boboin-ai-chat-btn"
                    title="{{ __('Bantuan & Tanya AI') }}"
                    aria-label="{{ __('Bantuan & Tanya AI') }}"
                    class="relative w-12 h-12 rounded-full bg-[#3a6484] hover:bg-[#2f5370] text-white shadow-xl hover:shadow-2xl ring-2 ring-white/80 flex items-center justify-center transition-all duration-200 hover:scale-105 focus:outline-none cursor-pointer">
                <div x-show="!menuOpen" class="w-10 h-10 rounded-full bg-white overflow-hidden">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Boboin Villa" class="w-full h-full object-cover">
                </div>
                <svg x-show="menuOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M6 18L18 6M6 6l12 12"/>
                </svg>

                {{-- Badge Online / Sparkle --}}
                <span x-show="!menuOpen" class="absolute -top-0.5 -right-0.5 w-4 h-4 rounded-full bg-emerald-500 ring-2 ring-white flex items-center justify-center text-[9px] leading-none shadow-xs">
                    ✨
                </span>
            </button>
        </div>
    @endif

    {{-- Jendela Chat Modal --}}
    @if($isOpen)
        <div id="ai-chat-modal"
             class="fixed bottom-4 right-3 sm:bottom-5 sm:right-5 z-50 w-[94vw] sm:w-[400px] h-[530px] max-h-[82vh] bg-white rounded-3xl shadow-2xl border border-[#3a6484]/20 flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">

            {{-- Header Chat --}}
            <div class="bg-gradient-to-r from-[#1a3a52] to-[#3a6484] text-white px-4 py-3.5 flex items-center justify-between shrink-0 shadow-md">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-white ring-2 ring-white/30 overflow-hidden flex items-center justify-center shrink-0 shadow-xs">
                        <img src="{{ asset('images/logo.jpg') }}" alt="Boboin Villa" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-sm sm:text-base leading-tight">Boboin AI Concierge</h3>
                            <span class="bg-emerald-500/20 text-emerald-300 text-[10px] px-1.5 py-0.5 rounded-full font-semibold border border-emerald-400/30">Online</span>
                        </div>
                        <p class="text-[11px] text-white/70">Asisten Cerdas Reservasi Villa</p>
                    </div>
                </div>

                <div class="flex items-center gap-1">
                    {{-- Shortcut WhatsApp Admin --}}
                    <a href="https://wa.me/6285724821667?text=Halo%20Boboin%20Villa%2C%20saya%20ingin%20bertanya%20mengenai%20pemesanan%20villa."
                       target="_blank" rel="noopener"
                       title="{{ __('Hubungi kami via WhatsApp') }}"
                       class="p-1.5 text-emerald-300 hover:text-white hover:bg-white/10 rounded-lg transition">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                            <path d="M12 0C5.373 0 0 5.373 0 12c0 2.126.558 4.121 1.532 5.849L.057 23.516a.75.75 0 00.927.927l5.666-1.475A11.934 11.934 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.75a9.734 9.734 0 01-4.964-1.359l-.356-.212-3.693.96.982-3.591-.232-.369A9.733 9.733 0 012.25 12C2.25 6.615 6.615 2.25 12 2.25S21.75 6.615 21.75 12 17.385 21.75 12 21.75z"/>
                        </svg>
                    </a>

                    {{-- Reset / Clear Chat --}}
                    <button wire:click="clearChat"
                            type="button"
                            title="Mulai Percakapan Baru"
                            class="p-1.5 text-white/70 hover:text-white hover:bg-white/10 rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </button>

                    {{-- Close Button --}}
                    <button wire:click="closeChat"
                            type="button"
                            title="Tutup Chat"
                            class="p-1.5 text-white/70 hover:text-white hover:bg-white/10 rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Body Percakapan --}}
            <div id="ai-chat-body"
                 x-data="{
                     scrollToBottom() {
                         $nextTick(() => {
                             this.$el.scrollTop = this.$el.scrollHeight;
                         });
                     }
                 }"
                 x-init="scrollToBottom()"
                 @chat-updated.window="scrollToBottom()"
                 class="flex-1 p-4 overflow-y-auto space-y-3.5 bg-slate-50/70 text-xs sm:text-sm">

                {{-- Quick Prompts (Tampil jika percakapan masih awal) --}}
                @if(count($history) <= 1)
                    <div class="pt-1 pb-2">
                        <p class="text-[11px] font-semibold text-slate-500 mb-2">Pertanyaan Populer:</p>
                        <div class="flex flex-col gap-1.5">
                            <button wire:click="askQuickPrompt('Rekomendasi villa untuk rombongan keluarga besar')"
                                    type="button"
                                    class="text-left text-xs bg-white hover:bg-[#3a6484]/10 text-[#3a6484] border border-[#3a6484]/20 rounded-xl px-3 py-2 transition shadow-2xs font-medium">
                                👨‍👩‍👧‍👦 Rekomendasi villa keluarga besar
                            </button>
                            <button wire:click="askQuickPrompt('Ada villa yang memiliki fasilitas kolam renang privat?')"
                                    type="button"
                                    class="text-left text-xs bg-white hover:bg-[#3a6484]/10 text-[#3a6484] border border-[#3a6484]/20 rounded-xl px-3 py-2 transition shadow-2xs font-medium">
                                🏊 Villa dengan private pool
                            </button>
                            <button wire:click="askQuickPrompt('Bagaimana cara memesan villa dan cara pembayarannya?')"
                                    type="button"
                                    class="text-left text-xs bg-white hover:bg-[#3a6484]/10 text-[#3a6484] border border-[#3a6484]/20 rounded-xl px-3 py-2 transition shadow-2xs font-medium">
                                💳 Cara pemesanan & metode bayar
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Riwayat Pesan --}}
                @foreach($history as $item)
                    @if($item['role'] === 'user')
                        {{-- Pesan Tamu --}}
                        <div class="flex flex-col items-end">
                            <div class="bg-[#3a6484] text-white px-3.5 py-2.5 rounded-2xl rounded-tr-xs max-w-[85%] shadow-sm leading-relaxed whitespace-pre-line">
                                {{ $item['text'] }}
                            </div>
                            <span class="text-[10px] text-slate-400 mt-1 mr-1">{{ $item['time'] ?? '' }}</span>
                        </div>
                    @else
                        {{-- Jawaban AI Concierge --}}
                        <div class="flex flex-col items-start">
                            <div class="bg-white text-slate-800 border border-slate-200/90 px-3.5 py-2.5 rounded-2xl rounded-tl-xs max-w-[90%] shadow-sm leading-relaxed">
                                {!! nl2br(e($item['text'])) !!}
                            </div>
                            <span class="text-[10px] text-slate-400 mt-1 ml-1 flex items-center gap-1.5">
                                <img src="{{ asset('images/logo.jpg') }}" alt="Boboin Villa" class="w-3.5 h-3.5 rounded-full object-cover ring-1 ring-slate-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                                Boboin AI • {{ $item['time'] ?? '' }}
                            </span>
                        </div>
                    @endif
                @endforeach

                {{-- Indikator Sedang Mengetik (Thinking Loading) --}}
                <div wire:loading wire:target="send,askQuickPrompt" class="flex items-center gap-2 text-slate-500 py-1">
                    <div class="bg-white border border-slate-200 rounded-2xl rounded-tl-xs px-3.5 py-2 shadow-sm flex items-center gap-2">
                        <img src="{{ asset('images/logo.jpg') }}" alt="Boboin Villa" class="w-4 h-4 rounded-full object-cover">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#3a6484] animate-bounce"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-[#3a6484] animate-bounce [animation-delay:0.2s]"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-[#3a6484] animate-bounce [animation-delay:0.4s]"></span>
                        <span class="text-xs text-slate-500 ml-0.5">Boboin AI sedang berpikir...</span>
                    </div>
                </div>
            </div>

            {{-- Footer Input Box --}}
            <form wire:submit.prevent="send"
                  @submit="$nextTick(() => { const b = document.getElementById('ai-chat-body'); if(b) b.scrollTop = b.scrollHeight; })"
                  class="p-3 bg-white border-t border-slate-100 flex items-center gap-2 shrink-0">
                <input wire:model="message"
                       type="text"
                       placeholder="Ketik pertanyaan seputar villa..."
                       class="flex-1 text-xs sm:text-sm bg-slate-100/80 border border-slate-200 rounded-full px-4 py-2.5 focus:outline-none focus:border-[#3a6484] focus:ring-1 focus:ring-[#3a6484] transition"
                       autofocus>

                <button type="submit"
                        wire:loading.attr="disabled"
                        wire:target="send,askQuickPrompt"
                        class="w-9 h-9 rounded-full bg-[#3a6484] hover:bg-[#2f5370] text-white flex items-center justify-center transition shadow-md shrink-0 disabled:opacity-60 cursor-pointer">
                    <span wire:loading.remove wire:target="send,askQuickPrompt">
                        <svg class="w-4 h-4 rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </span>
                    <span wire:loading wire:target="send,askQuickPrompt">
                        <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                </button>
            </form>
        </div>
    @endif
</div>
