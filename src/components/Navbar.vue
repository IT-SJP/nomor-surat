<template>
  <header class="sticky top-0 z-50 bg-base-100/95 backdrop-blur-md border-b border-base-200 shadow-xs">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16 sm:h-20">
        <!-- Brand / Logo -->
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-teal-50 border border-teal-100 flex items-center justify-center shadow-xs p-1.5">
            <img 
              :src="logoUrl" 
              alt="Logo SJP Holding" 
              class="w-full h-full object-contain"
            />
          </div>
          <div>
            <div class="flex items-center gap-2">
              <span class="font-extrabold text-slate-900 tracking-tight text-base sm:text-lg">SJP HOLDING</span>
              <span class="hidden sm:inline-block text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full font-semibold border border-slate-200">Official</span>
            </div>
            <p class="text-xs text-slate-500 font-medium hidden sm:block">Sistem Layanan Nomor Surat Internal</p>
          </div>
        </div>

        <!-- Status & Portal Link -->
        <div class="flex items-center gap-2 sm:gap-4">
          <div class="badge badge-warning gap-1.5 font-semibold py-3 px-3 sm:px-3.5 text-xs sm:text-sm shadow-xs border border-amber-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span>Dialihkan ke Sistem Baru</span>
          </div>

          <a 
            href="https://absenkusjp.com" 
            target="_blank" 
            rel="noopener noreferrer"
            class="btn btn-outline btn-sm sm:btn-md border-teal-600 text-teal-700 hover:bg-teal-600 hover:border-teal-600 hover:text-white transition-all duration-200 hidden md:inline-flex gap-1.5"
          >
            <span>Buka Portal</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
            </svg>
          </a>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import logoUrl from '../assets/sjp-logo.png'
</script>
