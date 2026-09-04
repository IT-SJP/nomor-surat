<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Baris Bahasa Validasi
    |--------------------------------------------------------------------------
    |
    | Baris bahasa berikut berisi pesan kesalahan standar yang digunakan oleh
    | kelas validator. Beberapa aturan memiliki beberapa versi seperti
    | aturan ukuran. Silakan sesuaikan setiap pesan di sini.
    |
    */

    'accepted' => ':Attribute harus diterima.',
    'accepted_if' => ':Attribute harus diterima ketika :other bernilai :value.',
    'active_url' => ':Attribute harus berupa URL yang valid.',
    'after' => ':Attribute harus berupa tanggal setelah :date.',
    'after_or_equal' => ':Attribute harus berupa tanggal setelah atau sama dengan :date.',
    'alpha' => ':Attribute hanya boleh berisi huruf.',
    'alpha_dash' => ':Attribute hanya boleh berisi huruf, angka, setrip, dan garis bawah.',
    'alpha_num' => ':Attribute hanya boleh berisi huruf dan angka.',
    'any_of' => ':Attribute tidak valid.',
    'array' => ':Attribute harus berupa sebuah array.',
    'ascii' => ':Attribute hanya boleh berisi karakter alfanumerik dan simbol single-byte.',
    'before' => ':Attribute harus berupa tanggal sebelum :date.',
    'before_or_equal' => ':Attribute harus berupa tanggal sebelum atau sama dengan :date.',
    'between' => [
        'array' => ':Attribute harus memiliki antara :min dan :max anggota.',
        'file' => ':Attribute harus berukuran antara :min dan :max kilobita.',
        'numeric' => ':Attribute harus bernilai antara :min dan :max.',
        'string' => ':Attribute harus berisi antara :min dan :max karakter.',
    ],
    'boolean' => ':Attribute harus bernilai benar atau salah.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'date' => ':Attribute bukan tanggal yang valid.',
    'date_equals' => ':Attribute harus berupa tanggal yang sama dengan :date.',
    'date_format' => ':Attribute tidak cocok dengan format :format.',
    'different' => ':Attribute dan :other harus berbeda.',
    'digits' => ':Attribute harus terdiri dari :digits angka.',
    'digits_between' => ':Attribute harus terdiri dari :min sampai :max angka.',
    'email' => ':Attribute harus berupa alamat email yang valid.',
    'ends_with' => ':Attribute harus diakhiri salah satu dari berikut: :values.',
    'enum' => ':Attribute yang dipilih tidak valid.',
    'exists' => ':Attribute yang dipilih tidak valid.',
    'file' => ':Attribute harus berupa sebuah berkas.',
    'filled' => ':Attribute harus memiliki nilai.',
    'gt' => [
        'array' => ':Attribute harus memiliki lebih dari :value anggota.',
        'file' => ':Attribute harus berukuran lebih besar dari :value kilobita.',
        'numeric' => ':Attribute harus bernilai lebih besar dari :value.',
        'string' => ':Attribute harus berisi lebih dari :value karakter.',
    ],
    'gte' => [
        'array' => ':Attribute harus terdiri dari :value anggota atau lebih.',
        'file' => ':Attribute harus berukuran lebih besar dari atau sama dengan :value kilobita.',
        'numeric' => ':Attribute harus bernilai lebih besar dari atau sama dengan :value.',
        'string' => ':Attribute harus berisi lebih dari atau sama dengan :value karakter.',
    ],
    'image' => ':Attribute harus berupa gambar.',
    'in' => ':Attribute yang dipilih tidak valid.',
    'in_array' => ':Attribute tidak ada di dalam :other.',
    'integer' => ':Attribute harus berupa bilangan bulat.',
    'lt' => [
        'array' => ':Attribute harus memiliki kurang dari :value anggota.',
        'file' => ':Attribute harus berukuran kurang dari :value kilobita.',
        'numeric' => ':Attribute harus bernilai kurang dari :value.',
        'string' => ':Attribute harus berisi kurang dari :value karakter.',
    ],
    'lte' => [
        'array' => ':Attribute harus tidak lebih dari :value anggota.',
        'file' => ':Attribute harus berukuran kurang dari atau sama dengan :value kilobita.',
        'numeric' => ':Attribute harus bernilai kurang dari atau sama dengan :value.',
        'string' => ':Attribute harus berisi kurang dari atau sama dengan :value karakter.',
    ],
    'max' => [
        'array' => ':Attribute maksimal terdiri dari :max anggota.',
        'file' => ':Attribute maksimal berukuran :max kilobita.',
        'numeric' => ':Attribute maksimal bernilai :max.',
        'string' => ':Attribute maksimal berisi :max karakter.',
    ],
    'mimes' => ':Attribute harus berupa berkas berjenis: :values.',
    'mimetypes' => ':Attribute harus berupa berkas berjenis: :values.',
    'min' => [
        'array' => ':Attribute minimal terdiri dari :min anggota.',
        'file' => ':Attribute minimal berukuran :min kilobita.',
        'numeric' => ':Attribute minimal bernilai :min.',
        'string' => ':Attribute minimal berisi :min karakter.',
    ],
    'numeric' => ':Attribute harus berupa angka.',
    'present' => ':Attribute wajib ada.',
    'required' => ':Attribute wajib diisi.',
    'same' => ':Attribute dan :other harus sama.',
    'size' => [
        'array' => ':Attribute harus mengandung :size anggota.',
        'file' => ':Attribute harus berukuran :size kilobyte.',
        'numeric' => ':Attribute harus berukuran :size.',
        'string' => ':Attribute harus berukuran :size karakter.',
    ],
    'string' => ':Attribute harus berupa string.',
    'unique' => ':Attribute sudah ada sebelumnya.',
    'url' => ':Attribute formatnya tidak valid.',

    /*
    |--------------------------------------------------------------------------
    | Baris Bahasa Kustom untuk Validasi
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'target_code' => [
            'required' => 'Tujuan / penerima surat wajib diisi.',
            'min' => 'Tujuan / penerima surat minimal 2 karakter.',
        ],
        'branch_code' => [
            'required' => 'Cabang surat wajib dipilih.',
        ],
        'subject' => [
            'required' => 'Perihal surat wajib diisi.',
            'min' => 'Perihal surat minimal 3 karakter.',
        ],
        'requestor_name' => [
            'required' => 'Nama pemohon surat wajib diisi.',
            'min' => 'Nama pemohon surat minimal 2 karakter.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Atribut Kustom
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        'target_code' => 'Tujuan surat',
        'branch_code' => 'Cabang surat',
        'subject' => 'Perihal surat',
        'purpose' => 'Keperluan / keterangan',
        'archive_location' => 'Lokasi arsip',
        'requestor_name' => 'Nama pemohon',
        'requestor_email' => 'Email pemohon',
        'requestor_phone' => 'Nomor telepon pemohon',
        'month' => 'Bulan surat',
        'year' => 'Tahun surat',
    ],

];
