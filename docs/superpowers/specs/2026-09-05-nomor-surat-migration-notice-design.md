# Design Specification: Halaman Pemberitahuan Migrasi Sistem Nomor Surat SJP

- **Tanggal**: 2026-09-05
- **Status**: Disetujui (Approved)
- **Target Deployment**: Vercel
- **Sistem Baru Target**: Laravel (diakses melalui Portal Karyawan SJP)

---

## 1. Latar Belakang & Tujuan
Sistem penomoran surat internal SJP Holding yang sebelumnya berbasis Google Sheets dan frontend statis mandiri telah dipensiunkan. Pengelolaan nomor surat yang baru kini telah diintegrasikan ke dalam ekosistem sistem internal perusahaan berbasis Laravel.

Repository ini dialihfungsikan menjadi **landing page pemberitahuan resmi (migration notice page)** yang mengedukasi seluruh karyawan SJP mengenai cara mengakses sistem nomor surat baru melalui akun karyawan masing-masing di portal Absenku SJP.

---

## 2. Sasaran Pengguna & Alur Kerja (User Flow)
1. **Pendaratan Pengguna**: Pengguna (karyawan SJP) yang membuka URL lama sistem nomor surat akan langsung disambut halaman pengumuman modern dan jelas.
2. **Pemberitahuan Status**: Banner peringatan (alert) menegaskan bahwa form pengajuan di URL lama sudah tidak berlaku.
3. **Panduan Visual & Langkah**:
   - Menampilkan screenshot panduan area profil karyawan yang memuat indikator poin Presensi & KPI.
   - Menyoroti badge **Human Resource and General Affairs** di bawah poin Presensi/KPI sebagai pintu masuk sistem nomor surat baru.
   - Menjelaskan 3–4 langkah mudah untuk login dan navigasi ke menu tersebut.
4. **Tindakan Langsung (CTA)**: Tombol besar **"Menuju Portal SJP"** mengarahkan karyawan ke `https://absenkusjp.com`.
5. **Dukungan / Helpdesk**: Kontak tim ICT/HR bagi karyawan yang membutuhkan bantuan akun.

---

## 3. Arsitektur & Tech Stack
- **Framework**: Vite + Vue 3 (Composition API dengan `<script setup>`).
- **Styling**: Tailwind CSS v3 + DaisyUI v4.
- **Tema DaisyUI**: `corporate` / `emerald` dengan aksen palet Teal korporat khas SJP.
- **Assets**:
  - `sjp-logo.png`: Logo resmi SJP Holding.
  - `guide-badge.png`: Screenshot bukti panduan badge akses (*Presensi: -80*, *KPI: 0*, dan *Human Resource and General Affairs*).
- **Hosting / Deploy**: Vercel (Auto-detected Vite preset: `npm run build` -> output `dist/`).

---

## 4. Struktur File Proyek
```text
nomor-surat/
├── docs/
│   └── superpowers/
│       └── specs/
│           └── 2026-09-05-nomor-surat-migration-notice-design.md
├── public/
│   └── favicon.ico
├── src/
│   ├── assets/
│   │   ├── sjp-logo.png
│   │   └── guide-badge.png
│   ├── components/
│   │   ├── Navbar.vue
│   │   ├── AnnouncementHero.vue
│   │   ├── GuideSteps.vue
│   │   └── FooterHelp.vue
│   ├── App.vue
│   ├── main.js
│   └── style.css
├── index.html
├── package.json
├── vite.config.js
├── tailwind.config.js
├── postcss.config.js
└── vercel.json
```

---

## 5. Rincian Komponen UI
1. **Navbar (`Navbar.vue`)**:
   - Menampilkan logo SJP Holding, nama sistem, serta badge peringatan DaisyUI: `badge badge-warning` ("Sistem Lama Dinonaktifkan").
2. **AnnouncementHero (`AnnouncementHero.vue`)**:
   - Alert box DaisyUI (`alert alert-warning shadow-md`) dengan pesan peringatan peralihan.
   - Headline pengumuman yang lugas dan ramah pengguna.
3. **GuideSteps (`GuideSteps.vue`)**:
   - Container card (`card bg-base-100 shadow-xl border border-base-200`).
   - Mockup visual yang membingkai gambar screenshot `guide-badge.png` dengan indikator penunjuk khusus.
   - DaisyUI `steps steps-vertical lg:steps-horizontal` untuk 4 langkah:
     1. Login ke Akun Karyawan di Absenku SJP.
     2. Temukan Poin KPI & Presensi di dashboard/profil akun.
     3. Klik badge **Human Resource and General Affairs**.
     4. Masuk ke aplikasi nomor surat Laravel.
   - Tombol CTA utama (`btn btn-primary btn-lg shadow-lg`): *"Menuju Portal SJP"* -> `https://absenkusjp.com`.
4. **FooterHelp (`FooterHelp.vue`)**:
   - Informasi bantuan kontak ICT dan hak cipta SJP Holding.

---

## 6. Rencana Pembersihan File Legacy
File berikut dihapus dari repository agar tidak meninggalkan kode usang yang tidak terpakai:
- `Google Apps Script.gs`
- `SETUP.conf`
- `SETUP_GUIDE.md`
- `style.css` (lama)
- `index.js` (lama)
- `dashboard-admin.html` (jika ada)
- `ecosystem.config.cjs` (lama)
- `storage/`, `vendor/`, `bootstrap/` (jika sisa legacy PHP/arsip lama)
Semua aset penting (logo) diselamatkan ke `src/assets/`.
