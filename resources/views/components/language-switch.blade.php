@php
    $currentLocale = app()->getLocale();
@endphp

<div class="inline-flex items-center rounded-full border border-black/10 bg-black/[0.04] p-0.5 text-xs font-mono font-bold backdrop-blur-md">
    <a href="{{ url('/lang/id') }}"
       class="rounded-full px-2.5 py-1 text-[11px] font-bold transition-all duration-200 {{ $currentLocale === 'id' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
       title="Bahasa Indonesia">
        ID
    </a>
    <a href="{{ url('/lang/en') }}"
       class="rounded-full px-2.5 py-1 text-[11px] font-bold transition-all duration-200 {{ $currentLocale === 'en' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
       title="English">
        EN
    </a>
</div>
