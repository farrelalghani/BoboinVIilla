@php
    $currentLocale = app()->getLocale();
@endphp

<div x-data="{
        locale: '{{ $currentLocale }}',
        toggle() {
            const next = this.locale === 'id' ? 'en' : 'id';
            this.setLang(next);
        },
        setLang(target) {
            if (this.locale === target) return;
            this.locale = target;
            window.location.href = '{{ url('/lang') }}/' + target;
        }
    }"
    @click="toggle()"
    class="relative inline-flex items-center bg-[#17191e] border border-neutral-700/60 rounded-full p-0.5 sm:p-1 h-7 sm:h-8 select-none cursor-pointer shadow-inner hover:border-neutral-500 transition-colors"
    title="Ganti Bahasa / Switch Language"
    role="button"
    aria-label="Switch Language ID/EN">

    {{-- White sliding pill indicator --}}
    <div class="absolute left-0.5 sm:left-1 top-0.5 sm:top-1 h-6 w-8 sm:w-9 bg-white rounded-full shadow-md transition-transform duration-300 ease-out pointer-events-none"
         :class="locale === 'en' ? 'translate-x-8 sm:translate-x-9' : 'translate-x-0'">
    </div>

    {{-- ID Option --}}
    <button type="button"
            @click.stop="setLang('id')"
            class="relative z-10 w-8 sm:w-9 h-6 flex items-center justify-center text-[11px] sm:text-xs font-bold transition-colors duration-200 tracking-wider"
            :class="locale === 'id' ? 'text-black font-extrabold' : 'text-neutral-400 hover:text-neutral-200'">
        ID
    </button>

    {{-- EN Option --}}
    <button type="button"
            @click.stop="setLang('en')"
            class="relative z-10 w-8 sm:w-9 h-6 flex items-center justify-center text-[11px] sm:text-xs font-bold transition-colors duration-200 tracking-wider"
            :class="locale === 'en' ? 'text-black font-extrabold' : 'text-neutral-400 hover:text-neutral-200'">
        EN
    </button>
</div>
