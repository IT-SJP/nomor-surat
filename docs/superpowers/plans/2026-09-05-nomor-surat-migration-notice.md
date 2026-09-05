# Halaman Pemberitahuan Migrasi Sistem Nomor Surat SJP Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Merombak source code lama menjadi landing page pemberitahuan migrasi sistem nomor surat SJP berbasis Vite + Vue 3 + Tailwind CSS + DaisyUI yang siap dideploy ke Vercel, dilengkapi panduan visual screenshot badge Human Resource and General Affairs dan tombol CTA menuju absenkusjp.com.

**Architecture:** Single Page Application (SPA) berbasis Vue 3 Composition API `<script setup>` dengan Vite bundler, dihiasi komponen UI DaisyUI v4 dan Tailwind CSS v3. Menghapus kode lama (Google Apps Script, form sheets statis) dan memigrasikan logo serta gambar panduan ke dalam folder aset baru.

**Tech Stack:** 
- Vue 3 (`vue`)
- Vite (`vite`, `@vitejs/plugin-vue`)
- Tailwind CSS (`tailwindcss`, `postcss`, `autoprefixer`)
- DaisyUI (`daisyui`)
- Vercel Hosting configuration (`vercel.json`)

## Global Constraints
- Framework: Vite + Vue 3 SFC (`<script setup>`)
- Komponen UI: DaisyUI v4 (`alert`, `card`, `steps`, `badge`, `btn`)
- Gambar panduan: screenshot badge Presensi/KPI & Human Resource and General Affairs
- Tombol CTA: Teks "Menuju Portal SJP" mengarah ke `https://absenkusjp.com` (target `_blank`, `rel="noopener noreferrer"`)
- Output build: `dist/`, zero error pada `npm run build`

---

### Task 1: Pembersihan File Legacy & Migrasi Aset Gambar

**Files:**
- Create: `src/assets/guide-badge.png`
- Create: `src/assets/sjp-logo.png`
- Create: `public/favicon.ico`
- Delete: `Google Apps Script.gs`
- Delete: `SETUP.conf`
- Delete: `SETUP_GUIDE.md`
- Delete: `index.js`
- Delete: `style.css`
- Delete: `ecosystem.config.cjs`

- [ ] **Step 1: Salin gambar screenshot panduan dan logo perusahaan ke folder assets baru**
```bash
mkdir -p src/assets public
cp /home/karimdevalio/.gemini/antigravity-ide/brain/ecd826f3-2f0e-4c78-bd4e-8872283c7bba/.user_uploaded/media_1788598588291.png src/assets/guide-badge.png
cp "assets/sjp_default_icon (2).png" src/assets/sjp-logo.png
cp "assets/sjp_default_icon (2).png" public/favicon.png
```

- [ ] **Step 2: Hapus file-file legacy yang sudah tidak digunakan**
```bash
rm -f "Google Apps Script.gs" SETUP.conf SETUP_GUIDE.md index.js style.css ecosystem.config.cjs
```

- [ ] **Step 3: Verifikasi ketersediaan file aset baru**
```bash
ls -la src/assets/ public/
```

- [ ] **Step 4: Commit perubahan Task 1**
```bash
git add src/assets/ public/ "Google Apps Script.gs" SETUP.conf SETUP_GUIDE.md index.js style.css ecosystem.config.cjs
git commit -m "chore: remove legacy scripts and migrate assets"
```

---

### Task 2: Setup Konfigurasi Vite, Vue 3, Tailwind CSS & DaisyUI

**Files:**
- Modify: `package.json`
- Create: `vite.config.js`
- Create: `tailwind.config.js`
- Create: `postcss.config.js`
- Create: `vercel.json`

- [ ] **Step 1: Update package.json dengan dependensi modern**
```json
{
  "name": "nomor-surat-migration-notice",
  "private": true,
  "version": "2.0.0",
  "type": "module",
  "scripts": {
    "dev": "vite",
    "build": "vite build",
    "preview": "vite preview"
  },
  "dependencies": {
    "vue": "^3.5.13"
  },
  "devDependencies": {
    "@vitejs/plugin-vue": "^5.2.1",
    "autoprefixer": "^10.4.20",
    "daisyui": "^4.12.23",
    "postcss": "^8.4.49",
    "tailwindcss": "^3.4.17",
    "vite": "^6.0.7"
  }
}
```

- [ ] **Step 2: Buat file vite.config.js**
```javascript
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  server: {
    port: 3000
  }
})
```

- [ ] **Step 3: Buat file tailwind.config.js dengan DaisyUI**
```javascript
/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        sjp: {
          teal: '#1b807a',
          dark: '#135c58',
          light: '#2bb3ab',
        }
      }
    },
  },
  plugins: [require("daisyui")],
  daisyui: {
    themes: ["corporate", "emerald", "dark"],
    defaultTheme: "corporate"
  }
}
```

- [ ] **Step 4: Buat file postcss.config.js**
```javascript
export default {
  plugins: {
    tailwindcss: {},
    autoprefixer: {},
  },
}
```

- [ ] **Step 5: Buat file vercel.json untuk routing SPA**
```json
{
  "rewrites": [
    {
      "source": "/(.*)",
      "destination": "/index.html"
    }
  ]
}
```

- [ ] **Step 6: Jalankan npm install dan verifikasi kelengkapan paket**
Run: `npm install`
Expected: Dependencies terinstall tanpa error.

- [ ] **Step 7: Commit perubahan Task 2**
```bash
git add package.json package-lock.json vite.config.js tailwind.config.js postcss.config.js vercel.json
git commit -m "build: configure vite, vue3, tailwindcss and daisyui"
```

---

### Task 3: Entry Point HTML & Styling Dasar

**Files:**
- Modify: `index.html`
- Create: `src/style.css`
- Create: `src/main.js`

- [ ] **Step 1: Buat file src/style.css dengan Tailwind directives**
```css
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer base {
  body {
    @apply min-h-screen bg-slate-50 text-slate-800 antialiased selection:bg-teal-500 selection:text-white;
  }
}
```

- [ ] **Step 2: Update index.html untuk Vite SPA**
```html
<!DOCTYPE html>
<html lang="id" data-theme="corporate">
  <head>
    <meta charset="UTF-8" />
    <link rel="icon" type="image/png" href="/favicon.png" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pemberitahuan Migrasi Sistem Nomor Surat - SJP Holding</title>
    <meta name="description" content="Informasi peralihan cara akses sistem nomor surat resmi SJP Holding melalui portal akun karyawan." />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
      body {
        font-family: 'Plus Jakarta Sans', sans-serif;
      }
    </style>
  </head>
  <body>
    <div id="app"></div>
    <script type="module" src="/src/main.js"></script>
  </body>
</html>
```

- [ ] **Step 3: Buat src/main.js**
```javascript
import { createApp } from 'vue'
import './style.css'
import App from './App.vue'

createApp(App).mount('#app')
```

- [ ] **Step 4: Commit perubahan Task 3**
```bash
git add index.html src/style.css src/main.js
git commit -m "feat: setup app entry point and base styles"
```

---

### Task 4: Komponen Navbar & Header

**Files:**
- Create: `src/components/Navbar.vue`

- [ ] **Step 1: Implementasi src/components/Navbar.vue**
Komponen navbar menggunakan DaisyUI `navbar bg-base-100/90 backdrop-blur shadow-sm border-b border-base-200 sticky top-0 z-50`.
Memuat:
- Logo SJP Holding (`src/assets/sjp-logo.png`)
- Judul instansi: "SJP HOLDING" dengan subtitle "Sistem Nomor Surat"
- DaisyUI Badge: `<span class="badge badge-warning gap-1.5 font-medium py-3 px-3 shadow-xs">⚠️ Sistem Lama Dialihkan</span>`
- Tombol bantuan cepat atau link ke portal.

- [ ] **Step 2: Commit perubahan Task 4**
```bash
git add src/components/Navbar.vue
git commit -m "feat: add Navbar component with SJP branding"
```

---

### Task 5: Komponen Hero & Alert Pengumuman

**Files:**
- Create: `src/components/AnnouncementHero.vue`

- [ ] **Step 1: Implementasi src/components/AnnouncementHero.vue**
Komponen pemberitahuan menggunakan:
- Alert DaisyUI: `<div class="alert alert-warning shadow-md border-amber-300">` berisi peringatan resmi bahwa formulir lama dinonaktifkan.
- Headline Hero yang informatif: "Pemberitahuan Migrasi Akses Sistem Nomor Surat".
- Deskripsi jelas: Menjelaskan bahwa proses pembuatan dan registrasi nomor surat kini terintegrasi penuh melalui sistem terpusat berbasis akun karyawan.

- [ ] **Step 2: Commit perubahan Task 5**
```bash
git add src/components/AnnouncementHero.vue
git commit -m "feat: add AnnouncementHero component with migration alert"
```

---

### Task 6: Komponen Panduan Visual & Langkah Akses (GuideSteps)

**Files:**
- Create: `src/components/GuideSteps.vue`

- [ ] **Step 1: Implementasi src/components/GuideSteps.vue**
Komponen inti instruksi yang memuat:
1. **Visual Guide Box**:
   - Menampilkan screenshot `guide-badge.png` di dalam mockup card dengan header aksen hijau SJP.
   - Pointers visual / animated highlight yang menunjuk badge **"Human Resource and General Affairs"** dan menjelaskan lokasinya di bawah poin **Presensi: -80** dan **KPI: 0**.
2. **DaisyUI Steps (`steps steps-vertical lg:steps-horizontal`)**:
   - **Step 1 (Login)**: Buka portal `absenkusjp.com` dan login dengan akun karyawan masing-masing.
   - **Step 2 (Dashboard)**: Perhatikan bagian ringkasan profil yang menampilkan poin Presensi dan KPI.
   - **Step 3 (Klik Menu)**: Klik badge **"Human Resource and General Affairs"** tepat di bawah indikator poin tersebut.
   - **Step 4 (Akses Surat)**: Anda akan langsung diarahkan ke aplikasi sistem nomor surat baru (Laravel).
3. **Primary Call-To-Action (CTA)**:
   - Tombol besar: `<a href="https://absenkusjp.com" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-lg shadow-xl gap-2 text-white">Menuju Portal SJP ↗</a>`

- [ ] **Step 2: Commit perubahan Task 6**
```bash
git add src/components/GuideSteps.vue
git commit -m "feat: add GuideSteps component with interactive visual walkthrough"
```

---

### Task 7: Komponen Footer & Helpdesk

**Files:**
- Create: `src/components/FooterHelp.vue`

- [ ] **Step 1: Implementasi src/components/FooterHelp.vue**
Komponen footer informatif:
- Informasi kontak bantuan Tim ICT & HRD SJP Holding bila ada kendala login atau hak akses nomor surat.
- Copyright SJP Holding 2026.

- [ ] **Step 2: Commit perubahan Task 7**
```bash
git add src/components/FooterHelp.vue
git commit -m "feat: add FooterHelp component"
```

---

### Task 8: Integrasi `App.vue`, Uji Build, & Validasi Tampilan

**Files:**
- Create: `src/App.vue`

- [ ] **Step 1: Implementasikan src/App.vue**
Menyusun `Navbar`, `AnnouncementHero`, `GuideSteps`, dan `FooterHelp` ke dalam layout yang rapi, responsif, dan elegan.

- [ ] **Step 2: Uji Build Produksi (Vercel Simulation)**
Run: `npm run build`
Expected: Folder `dist/` terbentuk tanpa error linter atau Vite build error.

- [ ] **Step 3: Uji Preview Server**
Run: `npm run preview` dan verifikasi bahwa halaman dapat dimuat dengan sempurna.

- [ ] **Step 4: Commit perubahan Task 8**
```bash
git add src/App.vue
git commit -m "feat: integrate all components into App.vue and verify production build"
```
