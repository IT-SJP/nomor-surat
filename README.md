# 📢 Pemberitahuan Migrasi Sistem Nomor Surat - SJP Holding

Halaman landing page pengumuman resmi terkait peralihan akses sistem nomor surat internal SJP Holding menuju sistem terintegrasi baru berbasis akun portal karyawan (Absenku SJP).

---

## 🚀 Tech Stack
- **Framework:** Vue 3 (Composition API `<script setup>`)
- **Bundler:** Vite 6
- **Styling:** Tailwind CSS + DaisyUI
- **Target Deployment:** Vercel

---

## 📌 Alur Akses Sistem Nomor Surat Baru
1. Buka dan login ke portal akun karyawan: [https://absenkusjp.com](https://absenkusjp.com)
2. Periksa ringkasan profil pada bagian nilai **Presensi** & **KPI**
3. Klik badge **Human Resource and General Affairs** di bawah poin KPI/Presensi
4. Pengguna langsung diarahkan ke aplikasi sistem nomor surat (Laravel) resmi

---

## 💻 Pengembangan Lokal
```bash
# Install dependencies
npm install

# Jalankan server development
npm run dev

# Build untuk produksi
npm run build

# Preview build lokal
npm run preview
```

---

## 🌐 Deployment Vercel
Project ini otomatis dideteksi sebagai proyek **Vite** oleh Vercel:
- **Build Command:** `npm run build`
- **Output Directory:** `dist`
- **Framework Preset:** Vite

---

© 2026 **SJP Holding** — Team IT
