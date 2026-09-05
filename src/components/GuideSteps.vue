<template>
  <section class="pt-2 sm:pt-3 pb-8 sm:pb-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 space-y-8 sm:space-y-10">
      <!-- Main Guide Card -->
      <div
        class="card bg-base-100 shadow-2xl border border-slate-200/80 rounded-3xl overflow-hidden">
        <div class="card-body p-6 sm:p-8 lg:p-10 space-y-8">
          <!-- Section Title inside card -->
          <div class="border-b border-slate-100 pb-5">
            <h2
              class="text-xl sm:text-2xl font-bold text-slate-900 flex items-center gap-2.5">
              Cara Mengakses Sistem Nomor Surat yang Baru
            </h2>
            <p class="text-sm sm:text-base text-slate-500 mt-1.5">
              Ikuti petunjuk di bawah ini untuk mengakses sistem nomor surat
              melalui akun portal karyawan.
            </p>
          </div>

          <!-- Visual Showcase Area with the user's screenshot -->
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="text-xs sm:text-sm font-semibold text-slate-700"
                  >Tampilan Pada Dashboard Akun Karyawan</span
                >
              </div>
            </div>

            <!-- Visual Preview Frame -->
            <div
              class="relative rounded-2xl border-2 border-teal-200/80 bg-gradient-to-br from-teal-900/5 via-teal-800/10 to-emerald-900/5 p-4 sm:p-8 overflow-hidden shadow-inner flex flex-col items-center">
              <!-- Subtle decorative background badge -->
              <div class="text-center mb-4 sm:mb-6">
                <p
                  class="text-xs font-semibold text-teal-800 uppercase tracking-wider mb-1">
                  Area Profil / Dashboard Pengguna
                </p>
              </div>

              <!-- The Image with Visual Pointer -->
              <div
                class="relative group max-w-md w-full bg-[#1b807a] p-4 sm:p-6 rounded-2xl shadow-lg border border-teal-700/50 flex flex-col items-center">
                <!-- Image itself -->
                <img
                  :src="guideUrl"
                  alt="Petunjuk Akses Badge Human Resource and General Affairs"
                  class="w-full max-w-[280px] sm:max-w-[340px] h-auto rounded-lg shadow-md object-contain border border-white/20" />

                <!-- Callout Marker on the badge -->
                <div
                  class="mt-4 w-full bg-white/95 backdrop-blur-md rounded-xl p-3.5 border border-teal-200 shadow-sm space-y-2">
                  <div
                    class="flex items-center gap-2 text-xs font-bold text-teal-900">
                    <span class="flex h-3 w-3 relative">
                      <span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                      <span
                        class="relative inline-flex rounded-full h-3 w-3 bg-teal-600"></span>
                    </span>
                    <span>PETUNJUK KLIK:</span>
                  </div>
                  <div
                    class="text-xs text-slate-700 leading-relaxed pl-5 border-l-2 border-teal-500 space-y-1">
                    <p>
                      1. Di bagian atas terlihat poin
                      <strong>⭐ Presensi</strong> dan <strong>⚡ KPI</strong>.
                    </p>
                    <p>
                      2. Klik badge di bawahnya yang bertuliskan: <br />
                      <strong
                        class="text-teal-800 bg-teal-50 px-2 py-0.5 rounded-md inline-block mt-1 border border-teal-200">
                        📄 (Departement Karyawan)
                      </strong>
                    </p>
                  </div>
                </div>
              </div>

              <div class="mt-4 text-center">
                <span class="text-xs text-slate-500 italic">
                  *Badge ini adalah pintu masuk resmi menuju sistem pengelolaan
                  nomor surat terbaru.
                </span>
              </div>
            </div>
          </div>

          <!-- DaisyUI Steps: Step by Step Workflow -->
          <div class="pt-2">
            <h3
              class="text-base sm:text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
              <span>4 Langkah Mudah Akses Sistem:</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Step Card 1 -->
              <div
                class="flex gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-200 hover:border-teal-300 transition-colors">
                <div
                  class="w-10 h-10 rounded-xl bg-teal-600 text-white font-black text-lg flex items-center justify-center shrink-0 shadow-xs">
                  1
                </div>
                <div class="space-y-1">
                  <h4 class="font-bold text-slate-900 text-sm sm:text-base">
                    Login Akun Karyawan
                  </h4>
                  <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Kunjungi portal <strong>absenkusjp.com</strong> dan masuk
                    menggunakan akun karyawan Anda.
                  </p>
                </div>
              </div>

              <!-- Step Card 2 -->
              <div
                class="flex gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-200 hover:border-teal-300 transition-colors">
                <div
                  class="w-10 h-10 rounded-xl bg-teal-600 text-white font-black text-lg flex items-center justify-center shrink-0 shadow-xs">
                  2
                </div>
                <div class="space-y-1">
                  <h4 class="font-bold text-slate-900 text-sm sm:text-base">
                    Lihat Indikator Poin
                  </h4>
                  <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Pada halaman profil atau dashboard utama akun, perhatikan
                    area nilai <strong>Presensi</strong> &
                    <strong>KPI</strong> Anda.
                  </p>
                </div>
              </div>

              <!-- Step Card 3 -->
              <div
                class="flex gap-4 p-4 rounded-2xl bg-teal-50/70 border-2 border-teal-300 shadow-xs">
                <div
                  class="w-10 h-10 rounded-xl bg-teal-700 text-white font-black text-lg flex items-center justify-center shrink-0 shadow-xs">
                  3
                </div>
                <div class="space-y-1">
                  <h4 class="font-bold text-teal-950 text-sm sm:text-base">
                    Klik Badge HR & GA
                  </h4>
                  <p class="text-xs sm:text-sm text-teal-900 leading-relaxed">
                    Klik badge
                    <strong>"Departement Karyawan"</strong> yang berada tepat di
                    bawah poin KPI/Presensi tersebut.
                  </p>
                </div>
              </div>

              <!-- Step Card 4 -->
              <div
                class="flex gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-200 hover:border-teal-300 transition-colors">
                <div
                  class="w-10 h-10 rounded-xl bg-teal-600 text-white font-black text-lg flex items-center justify-center shrink-0 shadow-xs">
                  4
                </div>
                <div class="space-y-1">
                  <h4 class="font-bold text-slate-900 text-sm sm:text-base">
                    Masuk Sistem Nomor Surat
                  </h4>
                  <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Anda akan otomatis terhubung ke sistem penomoran surat resmi
                    dengan hak akses yang terverifikasi.
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Primary CTA Button Area -->
          <div
            class="pt-6 border-t border-slate-100 flex flex-col items-center text-center space-y-4">
            <a
              href="https://absenkusjp.com"
              target="_blank"
              rel="noopener noreferrer"
              class="btn btn-primary btn-lg shadow-xl hover:shadow-2xl text-white font-bold text-base sm:text-lg px-8 sm:px-12 py-3 sm:py-4 rounded-2xl bg-gradient-to-r from-teal-700 to-teal-600 hover:from-teal-800 hover:to-teal-700 border-none transition-all duration-300 transform hover:-translate-y-0.5 flex items-center gap-3">
              <span>Menuju Portal Absensi Karyawan</span>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M14 5l7 7m0 0l-7 7m7-7H3" />
              </svg>
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import guideUrl from "../assets/guide-badge.png";
</script>
