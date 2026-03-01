<template>
    <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm">
            <!-- Abstract bookmark / open-book "B" icon -->
            <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none">
                <path d="M4 4.5A2.5 2.5 0 016.5 2H12v9l-3-2.5L6 11V4.5z" fill="currentColor" opacity="0.5"/>
                <path d="M12 2h5.5A2.5 2.5 0 0120 4.5v15a2.5 2.5 0 01-2.5 2.5h-11A2.5 2.5 0 014 19.5V11l5-2.5L12 11V2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
            </svg>
        </div>
        <span class="text-base text-gray-900" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; letter-spacing: -0.03em;">{{ $t('Bookable') }}</span>
    </div>
</template>
