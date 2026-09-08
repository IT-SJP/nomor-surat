# Design Specification: Aturan Penomoran Surat Per Cabang & Reset Tahunan serta Command Renumbering

- **Tanggal**: 2026-09-08
- **Status**: Proposed
- **Terkait**: Penomoran Surat PT Selamat Jaya Persada & Cabang

---

## 1. Latar Belakang & Masalah
Sebelumnya, penomoran surat pada sistem direset setiap bulan (`where('branch_code', $branchCode)->where('year', $year)->where('month', $month)`).
Sesuai aturan operasional terbaru perusahaan:
1. Penomoran surat **dibedakan per cabang** dan **direset per tahun** (bukan per bulan).
2. Perpindahan bulan tetap melanjutkan nomor urut sebelumnya dalam tahun yang sama (misal 31 Januari nomor terakhir 29, maka 1 Februari lanjut ke nomor 30), namun angka romawi bulan tetap berubah sesuai bulan surat yang bersangkutan (`I` -> `II`).
3. Seluruh data riwayat surat yang sudah ada di database (contoh: 90 surat cabang `PRTM` tahun 2026) harus direvisi penomorannya menggunakan Laravel Artisan Command agar berurutan secara benar dari bulan ke bulan.

---

## 2. Spesifikasi Teknis

### A. Layanan Penomoran (`LetterNumberService`)
- **Penentuan Nomor Urut Baru (`createLetter`)**:
  - Mengunci baris (`lockForUpdate()`) dan mencari nomor urut tertinggi berdasarkan `branch_code` dan `year`:
    ```php
    $latestLetter = Letter::query()
        ->where('branch_code', $branchCode)
        ->where('year', $year)
        ->lockForUpdate()
        ->orderByDesc('sequence_number')
        ->first();

    $nextSequence = ($latestLetter ? $latestLetter->sequence_number : 0) + 1;
    ```
  - Format referensi tetap mempertahankan bulan romawi sesuai tanggal/input bulan surat:
    `{paddedSequence}/{targetCode}/{branchCode}/{monthRoman}/{year}`
- **Pratinjau Nomor Berikutnya (`previewNextNumber`)**:
  - Mengambil `max('sequence_number')` dengan filter `branch_code` dan `year`:
    ```php
    $maxSeq = Letter::query()
        ->where('branch_code', $branchCode)
        ->where('year', $year)
        ->max('sequence_number') ?? 0;
    ```
  - Menampilkan bulan romawi berdasarkan parameter `$month` yang dipilih user di form.

### B. Layanan Impor CSV (`LetterImportService`)
- **Key Cache Counter**:
  - Mengubah key cache sequence counter dari `{$branchCode}_{$year}_{$month}` menjadi `{$branchCode}_{$year}`.
  - Query awal mengambil max sequence berdasarkan `branch_code` dan `year`.
  - Penomoran saat impor akan berlanjut secara kontinu melintasi baris-baris bulan berikutnya dalam tahun yang sama.

### C. Indeks Database PostgreSQL (`letters` table)
- Sesuai kaidah PostgreSQL / Supabase, menambahkan indeks majemuk:
  `['branch_code', 'year', 'sequence_number']` melalui migrasi database untuk mempercepat query sequence locking & max value.

### D. Artisan Command: `letter:renumber`
- **Signature**:
  ```bash
  php artisan letter:renumber {--branch= : Filter kode cabang spesifik (e.g. PRTM)} {--year= : Filter tahun tertentu (e.g. 2026)} {--dry-run : Pratinjau hasil tanpa menyimpan perubahan}
  ```
- **Logika Pengurutan**:
  - Mengelompokkan per cabang (`branch_code`) dan tahun (`year`).
  - Mengurutkan record secara deterministik:
    `ORDER BY month ASC, sequence_number ASC, created_at ASC, id ASC`.
- **Pembaruan Teks Referensi (`reference_number`)**:
  - Mengganti nomor urut pada prefix string:
    `$newRef = preg_replace('/^\d+/', $paddedSequence, $letter->reference_number);`
  - Mempertahankan keaslian format target khusus, cabang, bulan romawi, dan tahun.
- **Keamanan Transaksi & Unique Constraint**:
  - Dibungkus dalam satu `DB::transaction(...)`.
  - Menggunakan teknik *two-pass update* (temporary prefix `TEMP_{id}_{ref}`) sebelum menetapkan nomor final untuk mencegah konflik *unique constraint* pada tabel `letters`.

---

## 3. Rencana Verifikasi & Pengujian
1. **Unit & Feature Test (`tests/Feature/LetterNumberServiceTest.php`)**:
   - Uji kontinuitas nomor urut antar bulan dalam tahun yang sama (Januari -> Februari berlanjut).
   - Uji pemisahan nomor urut antar cabang berbeda (SJP dan CSI masing-masing punya urutan sendiri).
   - Uji reset nomor urut saat berganti tahun (2026 -> 2027 kembali ke 001).
2. **Feature Test Import (`tests/Feature/LetterImportTest.php`)**:
   - Memperbarui test import agar memvalidasi nomor urut berlanjut antar bulan dalam satu tahun.
3. **Feature Test Command (`tests/Feature/LetterRenumberCommandTest.php`)**:
   - Uji opsi `--dry-run` (memastikan database tidak berubah).
   - Uji opsi `--branch` dan `--year`.
   - Uji eksekusi riil dan validasi seluruh urutan nomor surat.
4. **Verifikasi Database Aktif**:
   - Menjalankan `php artisan letter:renumber --dry-run` untuk melihat pratinjau perubahan 90 data cabang PRTM.
   - Menjalankan `php artisan letter:renumber` untuk merevisi database secara aktual.
   - Memastikan tidak ada nomor urut yang terlewat / duplikat.
