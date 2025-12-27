# 📘 Panduan Setup Aplikasi Request Nomor Surat

## ✅ Fitur Otomatis

- ✨ **Auto-create 12 sheet** untuk setiap kode perusahaan (SJP, SJPRA, SJK, SJE, SJR, SAS, PAS, SPEK, SKORD, BTJ, PTU, RBJ)
- ✨ **Auto-create header** dengan formatting warna hijau SJP
- ✨ **Auto-generate nomor surat** per kode perusahaan
- ✨ **Auto-send email konfirmasi** ke requestor

---

## 📋 Langkah Setup (5 Menit!)

### **STEP 1: Buat Google Sheets Kosong**

1. Buka [Google Sheets](https://sheets.google.com)
2. Klik **Blank** (buat spreadsheet kosong)
3. Rename spreadsheet: **"Request Nomor Surat - SJP"**
4. **Copy ID dari URL:**
   ```
   https://docs.google.com/spreadsheets/d/COPY_ID_INI/edit
   ```

---

### **STEP 2: Setup Google Apps Script**

1. Di Google Sheets, klik menu **Extensions → Apps Script**
2. Hapus semua code default yang ada
3. Copy **SEMUA** isi file `Google Apps Script.gs` ke editor
4. **Edit line 16** - Ganti `YOUR_GOOGLE_SHEET_ID_HERE` dengan ID dari STEP 1:
   ```javascript
   const SHEET_ID = 'paste_id_kamu_disini';
   ```

---

### **STEP 3: Auto-Create 12 Sheet Sekaligus**

1. Masih di Apps Script Editor
2. Pilih function dropdown (atas) → Pilih **`setupAllCompanySheets`**
3. Klik tombol **▶ Run**
4. **PERTAMA KALI** akan minta authorization:
   - Klik **Review Permissions**
   - Pilih akun Google Anda
   - Klik **Advanced** → **Go to [Project Name] (unsafe)**
   - Klik **Allow**
5. Tunggu proses selesai (lihat Execution log)

**Hasil:** 12 sheet otomatis terbuat dengan header hijau SJP! ✅

---

### **STEP 4: Deploy sebagai Web App**

1. Klik tombol **Deploy** (pojok kanan atas) → **New deployment**
2. Klik ikon ⚙️ → Pilih **Web app**
3. Isi konfigurasi:
   - **Description:** `Nomor Surat API v1.0`
   - **Execute as:** `Me (email@gmail.com)`
   - **Who has access:** `Anyone`
4. Klik **Deploy**
5. **COPY URL DEPLOYMENT** (simpan di notepad)
   ```
   https://script.google.com/macros/s/DEPLOYMENT_ID/exec
   ```

---

### **STEP 5: Update File Aplikasi**

#### **A. Update index.js**

1. Buka file `index.js`
2. **Line 5** - Ganti URL dengan deployment URL dari STEP 4:
   ```javascript
   const GOOGLE_APPS_SCRIPT_URL = 'https://script.google.com/macros/s/PASTE_DEPLOYMENT_URL_DISINI/exec';
   ```

#### **B. Update index.html**

1. Buka file `index.html`
2. **Line 234** - Ganti ID dengan Google Sheets ID dari STEP 1:
   ```html
   <iframe src="https://docs.google.com/spreadsheets/d/PASTE_SHEET_ID_DISINI/preview"
   ```

---

### **STEP 6: Jalankan Aplikasi**

**Cara Termudah:**
1. **Double-click** file `index.html`
2. Atau **klik kanan → Open with → Chrome/Edge**

**Menggunakan Live Server (Optional):**
```powershell
# Di folder project
npx live-server
```

---

## 🧪 Testing

### **Test 1: Cek Sheet Auto-Created**
1. Buka Google Sheets
2. Cek tab di bawah - harus ada 12 sheet:
   - ✅ SJP, SJPRA, SJK, SJE, SJR, SAS, PAS, SPEK, SKORD, BTJ, PTU, RBJ
3. Setiap sheet punya header hijau dengan kolom yang sama

### **Test 2: Submit Form**
1. Buka aplikasi di browser
2. Klik menu **Pengajuan Surat**
3. Isi semua field:
   - Kode Perusahaan: **SJP**
   - Kode Tujuan: **Jakarta**
   - Bulan: **I**
   - Tahun: **2025**
   - Perihal: **Test Aplikasi**
   - Dst...
4. Klik **Ajukan Pengajuan**
5. Tunggu alert sukses

### **Test 3: Verifikasi Data Masuk**
1. Buka Google Sheets
2. Klik tab **SJP** (sesuai kode perusahaan yang dipilih)
3. Data harus muncul di row 2 dengan:
   - ✅ No: 1
   - ✅ Timestamp
   - ✅ Nomor Surat: `SJP/I/2025/001`
   - ✅ Semua data form

### **Test 4: Cek Email**
1. Buka inbox email yang diinput di form
2. Harus ada email dari **noreply@google.com**
3. Subject: **Konfirmasi Request Nomor Surat - SJP/I/2025/001**
4. Isi email ada detail lengkap dengan warna SJP (hijau-biru)

---

## 🔧 Troubleshooting

### ❌ Error: "Cannot find function doPost"
**Solusi:** Pastikan sudah copy SEMUA isi file `Google Apps Script.gs` ke Apps Script Editor

### ❌ Error: "Access denied" saat deploy
**Solusi:** 
1. Pastikan **Who has access** diset ke `Anyone`
2. Re-deploy dengan klik **Deploy → Manage deployments → Edit → Deploy**

### ❌ Data tidak masuk ke Google Sheets
**Solusi:**
1. Cek SHEET_ID di Apps Script sudah benar
2. Cek Deployment URL di index.js sudah benar
3. Buka Console (F12) di browser - cek error

### ❌ Email tidak terkirim
**Solusi:**
1. Normal - Google Apps Script perlu authorize dulu untuk send email
2. Jalankan `testSubmission()` function di Apps Script untuk trigger authorization
3. Setelah authorize, email akan otomatis terkirim

### ❌ Nomor surat tidak increment
**Solusi:**
1. Cek kolom Bulan dan Tahun sudah terisi benar
2. Format Tahun harus string di Apps Script (sudah difix dengan `.toString()`)

---

## 📊 Struktur Sheet Per Kode Perusahaan

Setiap sheet (SJP, SJPRA, dll) punya struktur sama:

| No | Timestamp | Nomor Surat | Kode Tujuan | Bulan | Tahun | Perihal | Tujuan | Letak Arsip | Requestor | Email | Telepon |
|----|-----------|-------------|-------------|-------|-------|---------|--------|-------------|-----------|-------|---------|
| 1  | 27/12/2025 10:30 | SJP/I/2025/001 | Jakarta | I | 2025 | Test | Testing | Arsip A | User 1 | user@mail.com | 08123456789 |
| 2  | 27/12/2025 11:45 | SJP/I/2025/002 | Bandung | I | 2025 | ... | ... | ... | ... | ... | ... |

**Header:** Background hijau (#00B050), text putih, bold, center aligned

---

## 🎯 Cara Kerja Sistem

1. **User submit form** → Data dikirim ke Apps Script
2. **Apps Script check sheet** → Jika kode perusahaan belum ada, auto-create sheet
3. **Generate nomor surat** → Cek data di sheet kode perusahaan untuk nomor urut
4. **Save to sheet** → Data masuk ke sheet sesuai kode perusahaan
5. **Send email** → Kirim konfirmasi ke email requestor

**Nomor Surat Format:** `KODE_PERUSAHAAN/BULAN/TAHUN/URUT`
- Contoh: `SJP/I/2025/001`
- Urut per bulan dan tahun di masing-masing kode perusahaan

---

## 📞 Support

Jika ada masalah:
1. Cek Execution log di Apps Script (View → Logs)
2. Cek Browser Console (F12 → Console tab)
3. Verifikasi semua ID dan URL sudah benar

---

## ✅ Checklist Setup Complete

- [ ] Google Sheets created
- [ ] Apps Script code copied
- [ ] SHEET_ID updated di Apps Script
- [ ] Function setupAllCompanySheets() dijalankan
- [ ] 12 sheet dengan header hijau terbuat
- [ ] Web app deployed
- [ ] Deployment URL copied
- [ ] index.js updated dengan deployment URL
- [ ] index.html updated dengan sheet ID
- [ ] Test submission berhasil
- [ ] Data masuk ke sheet yang benar
- [ ] Email konfirmasi terkirim

**Jika semua ✅ → APLIKASI SIAP DIGUNAKAN!** 🚀
