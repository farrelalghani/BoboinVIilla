@php
    $currentLocale = app()->getLocale();
@endphp

<div class="inline-flex items-center rounded-full border border-[#3a6484]/20 bg-[#3a6484]/[0.08] p-0.5 text-xs font-semibold backdrop-blur-md">
    <a href="{{ url('/lang/id') }}"
       class="rounded-full px-2.5 py-1 text-[11px] font-bold transition-all duration-200 {{ $currentLocale === 'id' ? 'bg-[#3a6484] text-white shadow-sm' : 'text-[#3a6484] hover:text-[#284860]' }}"
       title="Bahasa Indonesia">
        ID
    </a>
    <a href="{{ url('/lang/en') }}"
       class="rounded-full px-2.5 py-1 text-[11px] font-bold transition-all duration-200 {{ $currentLocale === 'en' ? 'bg-[#3a6484] text-white shadow-sm' : 'text-[#3a6484] hover:text-[#284860]' }}"
       title="English">
        EN
    </a>
</div>
