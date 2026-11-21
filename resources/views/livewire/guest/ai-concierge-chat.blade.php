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
    {{-- Tombol Mengambang "Tanya AI ✨" --}}
    <div class="fixed bottom-20 right-4 sm:bottom-24 sm:right-6 z-40">
        <button wire:click="toggleChat"
                type="button"
                id="boboin-ai-chat-btn"
                class="flex items-center gap-2.5 bg-[#3a6484] hover:bg-[#2f5370] text-white pl-2.5 pr-4 py-2 sm:py-2.5 rounded-full shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-0.5 group focus:outline-none"
                aria-label="Tanya AI Concierge">
            <div class="relative w-7 h-7 rounded-full bg-white ring-2 ring-white/50 overflow-hidden shrink-0 shadow-xs">
                <img src="{{ asset('images/logo.jpg') }}" alt="Boboin Villa" class="w-full h-full object-cover">
                <span class="absolute bottom-0 right-0 w-2 h-2 rounded-full bg-emerald-500 ring-1 ring-white"></span>
            </div>

            <span class="text-xs sm:text-sm font-semibold tracking-wide whitespace-nowrap">
                Tanya AI ✨
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
