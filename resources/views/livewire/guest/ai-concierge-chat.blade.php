<?php

use App\Services\GeminiChatService;
use Livewire\Volt\Component;

new class extends Component {
    public bool $isOpen = false;
    public string $message = '';
    public array $history = [];

    public function mount(): void
    {
        $this->history = [
            [
                'role' => 'assistant',
                'text' => "Halo! Saya **Boboin AI Concierge** ✨\nAda yang bisa saya bantu seputar rekomendasi villa, kapasitas tamu, fasilitas, atau cara pemesanan?",
                'time' => now()->format('H:i'),
            ],
        ];
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
            'time' => now()->format('H:i'),
        ];

        $this->message = '';

        // Dapatkan jawaban dari AI Gemini
        $reply = $chatService->reply($this->history, $text);

        $this->history[] = [
            'role' => 'assistant',
            'text' => $reply,
            'time' => now()->format('H:i'),
        ];

        // Trigger scroll ke bawah di sisi client
        $this->dispatch('chat-updated');
    }
}; ?>

<div>
    {{-- Tombol Mengambang "Tanya AI ✨" --}}
    <div class="fixed bottom-20 right-4 sm:bottom-24 sm:right-6 z-40">
        <button wire:click="toggleChat"
                type="button"
                id="boboin-ai-chat-btn"
                class="flex items-center gap-2 bg-[#3a6484] hover:bg-[#2f5370] text-white pl-3 pr-4 py-2.5 sm:py-3 rounded-full shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-0.5 group focus:outline-none"
                aria-label="Tanya AI Concierge">
            <span class="relative flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
            </span>

            {{-- Sparkle Icon --}}
            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-amber-300 animate-pulse" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2L14.4 7.6L20 10L14.4 12.4L12 18L9.6 12.4L4 10L9.6 7.6L12 2Z"/>
            </svg>

            <span class="text-xs sm:text-sm font-semibold tracking-wide whitespace-nowrap">
                Tanya AI
            </span>
        </button>
    </div>

    {{-- Jendela Chat Modal --}}
    @if($isOpen)
        <div id="ai-chat-modal"
             class="fixed bottom-20 right-3 sm:bottom-24 sm:right-6 z-50 w-[94vw] sm:w-[410px] h-[550px] max-h-[82vh] bg-white rounded-3xl shadow-2xl border border-[#3a6484]/20 flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">

            {{-- Header Chat --}}
            <div class="bg-gradient-to-r from-[#1a3a52] to-[#3a6484] text-white px-4 py-3.5 flex items-center justify-between shrink-0 shadow-md">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-white/10 ring-1 ring-white/20 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-amber-300" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L14.4 7.6L20 10L14.4 12.4L12 18L9.6 12.4L4 10L9.6 7.6L12 2Z"/>
                        </svg>
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
                            <span class="text-[10px] text-slate-400 mt-1 ml-1 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                                Boboin AI • {{ $item['time'] ?? '' }}
                            </span>
                        </div>
                    @endif
                @endforeach

                {{-- Indikator Sedang Mengetik (Thinking Loading) --}}
                <div wire:loading wire:target="send,askQuickPrompt" class="flex items-center gap-2 text-slate-500 py-1">
                    <div class="bg-white border border-slate-200 rounded-2xl rounded-tl-xs px-3.5 py-2 shadow-sm flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-[#3a6484] animate-bounce"></span>
                        <span class="w-2 h-2 rounded-full bg-[#3a6484] animate-bounce [animation-delay:0.2s]"></span>
                        <span class="w-2 h-2 rounded-full bg-[#3a6484] animate-bounce [animation-delay:0.4s]"></span>
                        <span class="text-xs text-slate-500 ml-1">Boboin AI sedang berpikir...</span>
                    </div>
                </div>
            </div>

            {{-- Footer Input Box --}}
            <form wire:submit.prevent="send" class="p-3 bg-white border-t border-slate-100 flex items-center gap-2 shrink-0">
                <input wire:model="message"
                       type="text"
                       placeholder="Ketik pertanyaan seputar villa..."
                       class="flex-1 text-xs sm:text-sm bg-slate-100/80 border border-slate-200 rounded-full px-4 py-2.5 focus:outline-none focus:border-[#3a6484] focus:ring-1 focus:ring-[#3a6484] transition"
                       autofocus>

                <button type="submit"
                        wire:loading.attr="disabled"
                        wire:target="send,askQuickPrompt"
                        class="w-9 h-9 rounded-full bg-[#3a6484] hover:bg-[#2f5370] text-white flex items-center justify-center transition shadow-md shrink-0 disabled:opacity-50">
                    <svg class="w-4 h-4 rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </button>
            </form>
        </div>
    @endif
</div>
