@php
    $user = auth()->user();
@endphp

<div class="flex items-center gap-3 px-4 py-3 border-t border-gray-200 dark:border-white/10">
    {{-- Avatar User --}}
    <div 
        class="w-9 h-9 rounded-full bg-gray-200 dark:bg-gray-700 bg-cover bg-center shrink-0"
        style="background-image: url('https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=7F9CF5&background=EBF4FF')"
    ></div>

    {{-- Info User (Nama & Email) --}}
    <div class="flex-1 min-w-0">
        <p class="text-sm font-medium text-gray-900 truncate dark:text-white">
            {{ $user->name }}
        </p>
        <p class="text-xs text-gray-500 truncate dark:text-gray-400">
            {{ $user->email }}
        </p>
    </div>

    {{-- Tombol Logout --}}
    <form action="{{ filament()->getLogoutUrl() }}" method="post">
        @csrf
        <button 
            type="submit"
            class="p-2 text-gray-500 rounded-lg hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition"
            title="Sign Out"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
            </svg>
        </button>
    </form>
</div>