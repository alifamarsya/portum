<?php

// Konfigurasi mesin CRUD generik Portum (Operasional & Referensi)
return array (
  'kendaraan' => 
  array (
    'model' => 'App\\Models\\UmKendaraan',
    'perm' => 'umum_rt',
    'modul' => 'Umum & Rumah Tangga',
    'judul' => 'Kendaraan & Driver',
    'maker_checker' => false,
    'fields' => 
    array (
      'no_polisi' => 
      array (
        'label' => 'No. Polisi',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'jenis' => 
      array (
        'label' => 'Jenis',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'merk' => 
      array (
        'label' => 'Merk',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'tahun' => 
      array (
        'label' => 'Tahun',
        'type' => 'text',
        'list' => false,
        'req' => true,
      ),
      'peruntukan' => 
      array (
        'label' => 'Peruntukan',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'driver' => 
      array (
        'label' => 'Driver',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'status' => 
      array (
        'label' => 'Status',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Aktif',
          1 => 'Servis',
          2 => 'Nonaktif',
        ),
      ),
      'keterangan' => 
      array (
        'label' => 'Keterangan',
        'type' => 'textarea',
        'list' => false,
        'req' => true,
      ),
    ),
  ),
  'biaya_harian' => 
  array (
    'model' => 'App\\Models\\UmBiayaHarian',
    'perm' => 'umum_rt',
    'modul' => 'Umum & Rumah Tangga',
    'judul' => 'Biaya BBM / Perawatan / Rumah Tangga',
    'maker_checker' => false,
    'fields' => 
    array (
      'tanggal' => 
      array (
        'label' => 'Tanggal',
        'type' => 'date',
        'list' => true,
        'fmt' => 'date',
        'req' => true,
      ),
      'kategori' => 
      array (
        'label' => 'Kategori',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'BBM',
          1 => 'Perawatan',
          2 => 'Rumah Tangga',
          3 => 'Kebersihan',
          4 => 'Keamanan',
          5 => 'Utilitas',
          6 => 'Lainnya',
        ),
      ),
      'kendaraan' => 
      array (
        'label' => 'Kendaraan',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'nama_beban' => 
      array (
        'label' => 'Nama Beban (Akun)',
        'type' => 'select',
        'list' => false,
        'req' => true,
        'opts_sql' => 'SELECT nama_beban AS v, nama_beban || " — " || rekening_debet AS t FROM ref_akun ORDER BY nama_beban',
      ),
      'rekening_debet' => 
      array (
        'label' => 'Rekening Debet',
        'type' => 'text',
        'list' => false,
        'req' => true,
      ),
      'rekening_kredit' => 
      array (
        'label' => 'Rekening Kredit',
        'type' => 'text',
        'list' => false,
        'req' => true,
      ),
      'uraian' => 
      array (
        'label' => 'Uraian',
        'type' => 'textarea',
        'list' => true,
        'req' => true,
      ),
      'jumlah' => 
      array (
        'label' => 'Jumlah',
        'type' => 'money',
        'list' => true,
        'fmt' => 'money',
        'req' => true,
      ),
      'no_nota' => 
      array (
        'label' => 'No. Nota',
        'type' => 'text',
        'list' => false,
        'req' => true,
      ),
      'dibuat_oleh' => 
      array (
        'label' => 'Dibuat Oleh',
        'type' => 'auto_user',
        'list' => false,
        'req' => false,
      ),
    ),
  ),
  'fasilitas_kantor' => 
  array (
    'model' => 'App\\Models\\UmFasilitasKantor',
    'perm' => 'umum_rt',
    'modul' => 'Umum & Rumah Tangga',
    'judul' => 'Master Fasilitas Kantor',
    'maker_checker' => false,
    'fields' => 
    array (
      'nama_fasilitas' => 
      array (
        'label' => 'Nama Fasilitas',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'kode' => 
      array (
        'label' => 'Kode',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'kategori' => 
      array (
        'label' => 'Kategori',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Ruangan',
          1 => 'Peralatan',
          2 => 'Furnitur',
          3 => 'Elektronik',
          4 => 'Kendaraan',
          5 => 'Lainnya',
        ),
      ),
      'lokasi' => 
      array (
        'label' => 'Lokasi',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'kondisi' => 
      array (
        'label' => 'Kondisi',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Baik',
          1 => 'Perlu Perawatan',
          2 => 'Rusak Ringan',
          3 => 'Rusak Berat',
        ),
      ),
      'tanggal_perolehan' => 
      array (
        'label' => 'Tgl Perolehan',
        'type' => 'date',
        'list' => false,
        'fmt' => 'date',
        'req' => true,
      ),
      'penanggung_jawab' => 
      array (
        'label' => 'Penanggung Jawab',
        'type' => 'text',
        'list' => false,
        'req' => true,
      ),
      'keterangan' => 
      array (
        'label' => 'Keterangan',
        'type' => 'textarea',
        'list' => false,
        'req' => true,
      ),
    ),
  ),
  'pemeliharaan_gedung' => 
  array (
    'model' => 'App\\Models\\UmPemeliharaanGedung',
    'perm' => 'umum_rt',
    'modul' => 'Umum & Rumah Tangga',
    'judul' => 'Pemeliharaan Gedung & Utilitas',
    'maker_checker' => false,
    'fields' => 
    array (
      'jenis_pekerjaan' => 
      array (
        'label' => 'Jenis Pekerjaan',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Gedung',
          1 => 'Listrik',
          2 => 'Air & Sanitasi',
          3 => 'AC & Ventilasi',
          4 => 'Lift',
          5 => 'Taman',
          6 => 'Lainnya',
        ),
      ),
      'uraian' => 
      array (
        'label' => 'Uraian',
        'type' => 'textarea',
        'list' => true,
        'req' => true,
      ),
      'lokasi' => 
      array (
        'label' => 'Lokasi',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'tanggal_rencana' => 
      array (
        'label' => 'Tgl Rencana',
        'type' => 'date',
        'list' => true,
        'fmt' => 'date',
        'req' => true,
      ),
      'tanggal_realisasi' => 
      array (
        'label' => 'Tgl Realisasi',
        'type' => 'date',
        'list' => true,
        'fmt' => 'date',
        'req' => true,
      ),
      'vendor' => 
      array (
        'label' => 'Vendor / Pelaksana',
        'type' => 'text',
        'list' => false,
        'req' => true,
      ),
      'biaya' => 
      array (
        'label' => 'Biaya',
        'type' => 'money',
        'list' => true,
        'fmt' => 'money',
        'req' => true,
      ),
      'status' => 
      array (
        'label' => 'Status',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Dijadwalkan',
          1 => 'Berjalan',
          2 => 'Selesai',
          3 => 'Ditunda',
        ),
      ),
    ),
  ),
  'checklist_kebersihan' => 
  array (
    'model' => 'App\\Models\\UmChecklistKebersihan',
    'perm' => 'umum_rt',
    'modul' => 'Umum & Rumah Tangga',
    'judul' => 'Checklist Kebersihan & Keamanan',
    'maker_checker' => false,
    'fields' => 
    array (
      'tanggal' => 
      array (
        'label' => 'Tanggal',
        'type' => 'date',
        'list' => true,
        'fmt' => 'date',
        'req' => true,
        'default' => 'today',
      ),
      'jenis' => 
      array (
        'label' => 'Jenis',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Kebersihan Harian',
          1 => 'Kebersihan Mingguan',
          2 => 'Keamanan Harian',
          3 => 'Keamanan Mingguan',
        ),
      ),
      'area' => 
      array (
        'label' => 'Area',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'petugas' => 
      array (
        'label' => 'Petugas',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'status' => 
      array (
        'label' => 'Status',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Belum Dilakukan',
          1 => 'Selesai',
          2 => 'Ada Catatan',
        ),
      ),
      'catatan' => 
      array (
        'label' => 'Catatan',
        'type' => 'textarea',
        'list' => false,
        'req' => true,
      ),
      'dibuat_oleh' => 
      array (
        'label' => 'Dibuat Oleh',
        'type' => 'auto_user',
        'list' => false,
        'req' => false,
      ),
    ),
  ),
  'k3_insiden' => 
  array (
    'model' => 'App\\Models\\UmK3Insiden',
    'perm' => 'umum_rt',
    'modul' => 'Umum & Rumah Tangga',
    'judul' => 'Catatan K3 & Lingkungan',
    'maker_checker' => false,
    'fields' => 
    array (
      'tanggal' => 
      array (
        'label' => 'Tanggal Kejadian',
        'type' => 'date',
        'list' => true,
        'fmt' => 'date',
        'req' => true,
        'default' => 'today',
      ),
      'jenis' => 
      array (
        'label' => 'Jenis',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Insiden K3',
          1 => 'Near Miss',
          2 => 'Isu Lingkungan',
          3 => 'Isu Ergonomi',
          4 => 'Lainnya',
        ),
      ),
      'lokasi' => 
      array (
        'label' => 'Lokasi',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'uraian' => 
      array (
        'label' => 'Uraian Kejadian',
        'type' => 'textarea',
        'list' => true,
        'req' => true,
      ),
      'korban' => 
      array (
        'label' => 'Korban / Yang Terdampak',
        'type' => 'text',
        'list' => false,
        'req' => true,
      ),
      'tindak_lanjut' => 
      array (
        'label' => 'Tindak Lanjut',
        'type' => 'textarea',
        'list' => false,
        'req' => true,
      ),
      'status' => 
      array (
        'label' => 'Status',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Open',
          1 => 'Dalam Penanganan',
          2 => 'Selesai',
        ),
      ),
      'dibuat_oleh' => 
      array (
        'label' => 'Dilaporkan Oleh',
        'type' => 'auto_user',
        'list' => false,
        'req' => false,
      ),
    ),
  ),
  'invoice_sewa' => 
  array (
    'model' => 'App\\Models\\AsInvoiceSewa',
    'perm' => 'logistik_pelaporan',
    'modul' => 'Logistik & Pelaporan',
    'judul' => 'Tagihan / Invoice Sewa',
    'maker_checker' => false,
    'fields' => 
    array (
      'no_invoice' => 
      array (
        'label' => 'No. Invoice',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'vendor' => 
      array (
        'label' => 'Vendor',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'jenis_sewa' => 
      array (
        'label' => 'Jenis Sewa',
        'type' => 'select',
        'list' => true,
        'req' => true,
        'opts' => 
        array (
          0 => 'Aplikasi',
          1 => 'Mesin ATM',
          2 => 'Brankas',
          3 => 'Perangkat IT',
          4 => 'Software',
          5 => 'Hardware',
        ),
      ),
      'periode_mulai' => 
      array (
        'label' => 'Periode Mulai',
        'type' => 'date',
        'list' => false,
        'req' => true,
      ),
      'periode_selesai' => 
      array (
        'label' => 'Periode Selesai',
        'type' => 'date',
        'list' => false,
        'req' => true,
      ),
      'nilai' => 
      array (
        'label' => 'Nilai',
        'type' => 'money',
        'list' => true,
        'fmt' => 'money',
        'req' => true,
      ),
      'jatuh_tempo' => 
      array (
        'label' => 'Jatuh Tempo',
        'type' => 'date',
        'list' => true,
        'fmt' => 'date',
        'req' => true,
      ),
      'status' => 
      array (
        'label' => 'Status',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Belum Bayar',
          1 => 'Proses',
          2 => 'Lunas',
        ),
      ),
      'keterangan' => 
      array (
        'label' => 'Keterangan',
        'type' => 'textarea',
        'list' => false,
        'req' => true,
      ),
    ),
  ),
  'aset' => 
  array (
    'model' => 'App\\Models\\AsAset',
    'perm' => 'administrasi_aset',
    'modul' => 'Administrasi Aset & Inventaris',
    'judul' => 'Inventarisasi Aset',
    'maker_checker' => false,
    'fields' => 
    array (
      'kode_aset' => 
      array (
        'label' => 'Kode Aset',
        'type' => 'select',
        'list' => true,
        'req' => true,
        'opts' => 
        array (
        ),
      ),
      'nama_aset' => 
      array (
        'label' => 'Nama Aset',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'kategori' => 
      array (
        'label' => 'Kategori',
        'type' => 'select',
        'list' => true,
        'req' => true,
        'opts' => 
        array (
          0 => 'Aset TI',
          1 => 'Aset Umum',
        ),
      ),
      'lokasi' => 
      array (
        'label' => 'Lokasi',
        'type' => 'select',
        'list' => true,
        'req' => true,
        'opts' => 
        array (
        ),
      ),
      'tanggal_perolehan' => 
      array (
        'label' => 'Tgl Perolehan',
        'type' => 'date',
        'list' => false,
        'fmt' => 'date',
        'req' => true,
      ),
      'tahun_pembelian' => 
      array (
        'label' => 'Tahun Pembelian',
        'type' => 'number',
        'list' => false,
        'req' => true,
      ),
      'tahun_penggunaan' => 
      array (
        'label' => 'Tahun Penggunaan',
        'type' => 'number',
        'list' => false,
        'req' => true,
      ),
      'nilai_perolehan' => 
      array (
        'label' => 'Nilai Perolehan',
        'type' => 'money',
        'list' => true,
        'fmt' => 'money',
        'req' => true,
      ),
      'umur_ekonomis' => 
      array (
        'label' => 'Umur Ekonomis (bln)',
        'type' => 'number',
        'list' => false,
        'req' => true,
      ),
      'kondisi' => 
      array (
        'label' => 'Kondisi',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Baik',
          1 => 'Rusak',
          2 => 'Butuh Maintenance',
        ),
      ),
      'penanggung_jawab' => 
      array (
        'label' => 'Penanggung Jawab',
        'type' => 'text',
        'list' => false,
        'req' => true,
      ),
      'keterangan' => 
      array (
        'label' => 'Keterangan',
        'type' => 'textarea',
        'list' => false,
        'req' => true,
      ),
    ),
  ),
  'amortisasi' => 
  array (
    'model' => 'App\\Models\\AsAmortisasi',
    'perm' => 'administrasi_aset',
    'modul' => 'Administrasi Aset & Inventaris',
    'judul' => 'Amortisasi Biaya',
    'maker_checker' => false,
    'fields' => 
    array (
      'nama_biaya' => 
      array (
        'label' => 'Nama Biaya',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'nilai_perolehan' => 
      array (
        'label' => 'Nilai Perolehan',
        'type' => 'money',
        'list' => true,
        'fmt' => 'money',
        'req' => true,
      ),
      'tanggal_mulai' => 
      array (
        'label' => 'Tgl Mulai',
        'type' => 'date',
        'list' => false,
        'fmt' => 'date',
        'req' => true,
      ),
      'umur_bulan' => 
      array (
        'label' => 'Umur (bulan)',
        'type' => 'number',
        'list' => true,
        'req' => true,
      ),
      'nilai_per_bulan' => 
      array (
        'label' => 'Nilai / Bulan',
        'type' => 'money',
        'list' => true,
        'fmt' => 'money',
        'req' => true,
        'help' => 'Dihitung otomatis bila dikosongkan (nilai ÷ umur)',
      ),
      'akumulasi' => 
      array (
        'label' => 'Akumulasi',
        'type' => 'money',
        'list' => false,
        'fmt' => 'money',
        'req' => true,
      ),
      'nilai_buku' => 
      array (
        'label' => 'Nilai Buku',
        'type' => 'money',
        'list' => true,
        'fmt' => 'money',
        'req' => true,
      ),
      'keterangan' => 
      array (
        'label' => 'Keterangan',
        'type' => 'textarea',
        'list' => false,
        'req' => true,
      ),
    ),
  ),
  'pks' => 
  array (
    'model' => 'App\\Models\\AsPks',
    'perm' => 'logistik_pelaporan',
    'modul' => 'Logistik & Pelaporan',
    'judul' => 'PKS & Jatuh Tempo',
    'maker_checker' => false,
    'fields' => 
    array (
      'no_pks' => 
      array (
        'label' => 'No. PKS',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'judul' => 
      array (
        'label' => 'Judul PKS',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'vendor' => 
      array (
        'label' => 'Vendor / Pihak',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'div_owner' => 
      array (
        'label' => 'Divisi Owner',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'tanggal_mulai' => 
      array (
        'label' => 'Tgl Mulai',
        'type' => 'date',
        'list' => false,
        'fmt' => 'date',
        'req' => true,
      ),
      'jatuh_tempo' => 
      array (
        'label' => 'Jatuh Tempo',
        'type' => 'date',
        'list' => true,
        'fmt' => 'date',
        'req' => true,
      ),
      'nilai' => 
      array (
        'label' => 'Nilai',
        'type' => 'money',
        'list' => false,
        'fmt' => 'money',
        'req' => true,
      ),
      'status' => 
      array (
        'label' => 'Status',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Aktif',
          1 => 'Akan Jatuh Tempo',
          2 => 'Berakhir',
          3 => 'Diperpanjang',
        ),
      ),
      'memo_dibuat' => 
      array (
        'label' => 'Memo ke Div Owner Dibuat',
        'type' => 'checkbox',
        'list' => true,
        'req' => true,
      ),
      'keterangan' => 
      array (
        'label' => 'Keterangan',
        'type' => 'textarea',
        'list' => false,
        'req' => true,
      ),
    ),
  ),
  'memo_sewa_cabang' => 
  array (
    'model' => 'App\\Models\\AsMemoSewaCabang',
    'perm' => 'logistik_pelaporan',
    'modul' => 'Logistik & Pelaporan',
    'judul' => 'Memo Sewa Cabang',
    'maker_checker' => false,
    'fields' => 
    array (
      'no_memo' => 
      array (
        'label' => 'No. Memo',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'cabang' => 
      array (
        'label' => 'Cabang',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'jenis' => 
      array (
        'label' => 'Jenis Sewa',
        'type' => 'select',
        'list' => true,
        'req' => true,
        'opts' => 
        array (
          0 => 'Galeri ATM',
          1 => 'Gedung Kantor',
          2 => 'Gudang',
          3 => 'Rumah Dinas',
        ),
      ),
      'tanggal' => 
      array (
        'label' => 'Tanggal',
        'type' => 'date',
        'list' => true,
        'fmt' => 'date',
        'req' => true,
      ),
      'nilai' => 
      array (
        'label' => 'Nilai',
        'type' => 'money',
        'list' => true,
        'fmt' => 'money',
        'req' => true,
      ),
      'keterangan' => 
      array (
        'label' => 'Keterangan',
        'type' => 'textarea',
        'list' => false,
        'req' => true,
      ),
    ),
  ),
  'temuan' => 
  array (
    'model' => 'App\\Models\\AsTemuan',
    'perm' => 'administrasi_aset',
    'modul' => 'Administrasi Aset & Inventaris',
    'judul' => 'Tindak Lanjut Temuan',
    'maker_checker' => false,
    'fields' => 
    array (
      'no_temuan' => 
      array (
        'label' => 'No. Temuan',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'sumber' => 
      array (
        'label' => 'Sumber',
        'type' => 'select',
        'list' => true,
        'req' => true,
        'opts' => 
        array (
          0 => 'Audit Internal',
          1 => 'OJK',
          2 => 'KAP',
          3 => 'Lainnya',
        ),
      ),
      'uraian' => 
      array (
        'label' => 'Uraian Temuan',
        'type' => 'textarea',
        'list' => true,
        'req' => true,
      ),
      'tanggal_temuan' => 
      array (
        'label' => 'Tgl Temuan',
        'type' => 'date',
        'list' => false,
        'fmt' => 'date',
        'req' => true,
      ),
      'batas_tindak_lanjut' => 
      array (
        'label' => 'Batas Tindak Lanjut',
        'type' => 'date',
        'list' => true,
        'fmt' => 'date',
        'req' => true,
      ),
      'status' => 
      array (
        'label' => 'Status',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Open',
          1 => 'Proses',
          2 => 'Selesai',
        ),
      ),
      'penanggung_jawab' => 
      array (
        'label' => 'Penanggung Jawab',
        'type' => 'text',
        'list' => false,
        'req' => true,
      ),
      'tindak_lanjut' => 
      array (
        'label' => 'Tindak Lanjut',
        'type' => 'textarea',
        'list' => false,
        'req' => true,
      ),
    ),
  ),
  'aset_history' => 
  array (
    'model' => 'App\\Models\\AsAsetHistory',
    'perm' => 'administrasi_aset',
    'modul' => 'Administrasi Aset & Inventaris',
    'judul' => 'Riwayat Pergerakan Aset',
    'maker_checker' => false,
    'fields' => 
    array (
      'aset_id' => 
      array (
        'label' => 'ID Aset',
        'type' => 'number',
        'list' => true,
        'req' => true,
      ),
      'field_changed' => 
      array (
        'label' => 'Perubahan',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'old_value' => 
      array (
        'label' => 'Nilai Lama',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'new_value' => 
      array (
        'label' => 'Nilai Baru',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'keterangan' => 
      array (
        'label' => 'Keterangan',
        'type' => 'textarea',
        'list' => true,
        'req' => true,
      ),
      'changed_at' => 
      array (
        'label' => 'Waktu Perubahan',
        'type' => 'date',
        'list' => true,
        'fmt' => 'date',
        'req' => true,
      ),
    ),
  ),
  'mutasi_aset' => 
  array (
    'model' => 'App\\Models\\AsMutasiAset',
    'perm' => 'administrasi_aset',
    'modul' => 'Administrasi Aset & Inventaris',
    'judul' => 'Mutasi Aset',
    'maker_checker' => false,
    'fields' => 
    array (
      'no_mutasi' => 
      array (
        'label' => 'No. Mutasi',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'aset_id' => 
      array (
        'label' => 'ID Aset',
        'type' => 'number',
        'list' => true,
        'req' => true,
      ),
      'dari_lokasi' => 
      array (
        'label' => 'Dari Lokasi',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'ke_lokasi' => 
      array (
        'label' => 'Ke Lokasi',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'dari_penanggung_jawab' => 
      array (
        'label' => 'Dari PJ',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'ke_penanggung_jawab' => 
      array (
        'label' => 'Ke PJ',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'alasan' => 
      array (
        'label' => 'Alasan Mutasi',
        'type' => 'textarea',
        'list' => false,
        'req' => true,
      ),
      'keterangan' => 
      array (
        'label' => 'Keterangan',
        'type' => 'textarea',
        'list' => false,
        'req' => true,
      ),
    ),
  ),
  'disposal_aset' => 
  array (
    'model' => 'App\\Models\\AsDisposalAset',
    'perm' => 'administrasi_aset',
    'modul' => 'Administrasi Aset & Inventaris',
    'judul' => 'Penghapusan Aset (Disposal)',
    'maker_checker' => false,
    'fields' => 
    array (
      'no_disposal' => 
      array (
        'label' => 'No. Disposal',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'aset_id' => 
      array (
        'label' => 'ID Aset',
        'type' => 'number',
        'list' => true,
        'req' => true,
      ),
      'tanggal_pengajuan' => 
      array (
        'label' => 'Tgl Pengajuan',
        'type' => 'date',
        'list' => true,
        'fmt' => 'date',
        'req' => true,
      ),
      'alasan_penghapusan' => 
      array (
        'label' => 'Alasan Penghapusan',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'metode' => 
      array (
        'label' => 'Metode',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Dihibahkan',
          1 => 'Dijual',
          2 => 'Dimusnahkan',
          3 => 'Dihapusbukukan',
        ),
      ),
      'nilai_buku_terakhir' => 
      array (
        'label' => 'Nilai Buku Terakhir',
        'type' => 'money',
        'list' => true,
        'fmt' => 'money',
        'req' => true,
      ),
      'keterangan' => 
      array (
        'label' => 'Keterangan',
        'type' => 'textarea',
        'list' => false,
        'req' => true,
      ),
    ),
  ),
  'rekonsiliasi_aset' => 
  array (
    'model' => 'App\\Models\\AsRekonsiliasiAset',
    'perm' => 'administrasi_aset',
    'modul' => 'Administrasi Aset & Inventaris',
    'judul' => 'Rekonsiliasi & Reklasifikasi Aset',
    'maker_checker' => false,
    'fields' => 
    array (
      'no_rekonsiliasi' => 
      array (
        'label' => 'No. Rekonsiliasi',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'periode' => 
      array (
        'label' => 'Periode',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'tanggal' => 
      array (
        'label' => 'Tanggal',
        'type' => 'date',
        'list' => true,
        'fmt' => 'date',
        'req' => true,
      ),
      'jenis' => 
      array (
        'label' => 'Jenis Kegiatan',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Rekonsiliasi',
          1 => 'Reklasifikasi',
        ),
      ),
      'aset_id' => 
      array (
        'label' => 'ID Aset',
        'type' => 'number',
        'list' => false,
        'req' => true,
      ),
      'kategori_awal' => 
      array (
        'label' => 'Kategori Semula',
        'type' => 'text',
        'list' => false,
        'req' => true,
      ),
      'kategori_baru' => 
      array (
        'label' => 'Kategori Baru',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'kondisi_awal' => 
      array (
        'label' => 'Kondisi Semula',
        'type' => 'select',
        'list' => false,
        'req' => true,
        'opts' => 
        array (
          0 => 'Baik',
          1 => 'Rusak Ringan',
          2 => 'Rusak Berat',
        ),
      ),
      'kondisi_baru' => 
      array (
        'label' => 'Kondisi Hasil Cek',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Baik',
          1 => 'Rusak Ringan',
          2 => 'Rusak Berat',
        ),
      ),
      'hasil_rekonsiliasi' => 
      array (
        'label' => 'Hasil Rekonsiliasi',
        'type' => 'textarea',
        'list' => true,
        'req' => true,
      ),
      'petugas' => 
      array (
        'label' => 'Petugas Pemeriksa',
        'type' => 'text',
        'list' => false,
        'req' => true,
      ),
      'status' => 
      array (
        'label' => 'Status',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Proses',
          1 => 'Selesai',
        ),
      ),
      'keterangan' => 
      array (
        'label' => 'Keterangan',
        'type' => 'textarea',
        'list' => false,
        'req' => true,
      ),
    ),
  ),
  'penerimaan_barang' => 
  array (
    'model' => 'App\\Models\\AsPenerimaanBarang',
    'perm' => 'logistik_pelaporan',
    'modul' => 'Logistik & Pelaporan',
    'judul' => 'Penerimaan Barang / Jasa',
    'maker_checker' => false,
    'fields' => 
    array (
      'no_penerimaan' => 
      array (
        'label' => 'No. Penerimaan',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'tanggal' => 
      array (
        'label' => 'Tanggal Masuk',
        'type' => 'date',
        'list' => true,
        'fmt' => 'date',
        'req' => true,
      ),
      'vendor' => 
      array (
        'label' => 'Vendor / Penyedia',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'nama_barang' => 
      array (
        'label' => 'Nama Barang / Jasa',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'jumlah' => 
      array (
        'label' => 'Jumlah',
        'type' => 'number',
        'list' => true,
        'req' => true,
      ),
      'satuan' => 
      array (
        'label' => 'Satuan',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'kondisi' => 
      array (
        'label' => 'Kondisi Barang',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Baik',
          1 => 'Rusak',
          2 => 'Tidak Sesuai',
        ),
      ),
      'penerima' => 
      array (
        'label' => 'Penerima',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'status' => 
      array (
        'label' => 'Status',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Diterima',
          1 => 'Diperiksa',
          2 => 'Disimpan',
        ),
      ),
      'keterangan' => 
      array (
        'label' => 'Keterangan',
        'type' => 'textarea',
        'list' => false,
        'req' => true,
      ),
    ),
  ),
  'distribusi_barang' => 
  array (
    'model' => 'App\\Models\\AsDistribusiBarang',
    'perm' => 'logistik_pelaporan',
    'modul' => 'Logistik & Pelaporan',
    'judul' => 'Distribusi Barang / Jasa',
    'maker_checker' => false,
    'fields' => 
    array (
      'no_distribusi' => 
      array (
        'label' => 'No. Distribusi',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'tanggal' => 
      array (
        'label' => 'Tanggal Keluar',
        'type' => 'date',
        'list' => true,
        'fmt' => 'date',
        'req' => true,
      ),
      'nama_barang' => 
      array (
        'label' => 'Nama Barang / Jasa',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'jumlah' => 
      array (
        'label' => 'Jumlah',
        'type' => 'number',
        'list' => true,
        'req' => true,
      ),
      'satuan' => 
      array (
        'label' => 'Satuan',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'tujuan_unit' => 
      array (
        'label' => 'Unit / Cabang Tujuan',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'penerima' => 
      array (
        'label' => 'Nama Penerima',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'status' => 
      array (
        'label' => 'Status',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Disiapkan',
          1 => 'Terkirim',
          2 => 'Diterima',
        ),
      ),
      'keterangan' => 
      array (
        'label' => 'Keterangan',
        'type' => 'textarea',
        'list' => false,
        'req' => true,
      ),
    ),
  ),
  'pembayaran_tagihan' => 
  array (
    'model' => 'App\\Models\\AsPembayaranTagihan',
    'perm' => 'logistik_pelaporan',
    'modul' => 'Logistik & Pelaporan',
    'judul' => 'Administrasi Pembayaran Tagihan',
    'maker_checker' => false,
    'fields' => 
    array (
      'no_tagihan' => 
      array (
        'label' => 'No. Tagihan',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'tanggal_tagihan' => 
      array (
        'label' => 'Tgl Tagihan',
        'type' => 'date',
        'list' => true,
        'fmt' => 'date',
        'req' => true,
      ),
      'tanggal_bayar' => 
      array (
        'label' => 'Tgl Bayar',
        'type' => 'date',
        'list' => true,
        'fmt' => 'date',
        'req' => true,
      ),
      'vendor' => 
      array (
        'label' => 'Vendor / Rekanan',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'uraian' => 
      array (
        'label' => 'Uraian Pembayaran',
        'type' => 'textarea',
        'list' => true,
        'req' => true,
      ),
      'nilai' => 
      array (
        'label' => 'Nilai Tagihan',
        'type' => 'money',
        'list' => true,
        'fmt' => 'money',
        'req' => true,
      ),
      'no_rekening' => 
      array (
        'label' => 'No. Rekening Tujuan',
        'type' => 'text',
        'list' => false,
        'req' => true,
      ),
      'status' => 
      array (
        'label' => 'Status Pembayaran',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Belum Bayar',
          1 => 'Proses',
          2 => 'Lunas',
        ),
      ),
      'keterangan' => 
      array (
        'label' => 'Keterangan',
        'type' => 'textarea',
        'list' => false,
        'req' => true,
      ),
    ),
  ),
  'memo_internal' => 
  array (
    'model' => 'App\\Models\\PgMemoInternal',
    'perm' => 'pengadaan',
    'modul' => 'Pengadaan & Pemeliharaan',
    'judul' => 'Memo Internal',
    'maker_checker' => false,
    'fields' => 
    array (
      'no_memo' => 
      array (
        'label' => 'No. Memo',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'dari_unit' => 
      array (
        'label' => 'Dari Unit',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'ke_unit' => 
      array (
        'label' => 'Ke Unit',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'perihal' => 
      array (
        'label' => 'Perihal',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'tanggal' => 
      array (
        'label' => 'Tanggal',
        'type' => 'date',
        'list' => true,
        'fmt' => 'date',
        'req' => true,
      ),
      'jenis' => 
      array (
        'label' => 'Jenis',
        'type' => 'select',
        'list' => false,
        'req' => true,
        'opts' => 
        array (
          0 => 'Divisi',
          1 => 'Cabang',
          2 => 'Cabang Pembantu',
          3 => 'Kantor Kas',
        ),
      ),
      'status' => 
      array (
        'label' => 'Status',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Masuk',
          1 => 'Diproses',
          2 => 'Selesai',
        ),
      ),
      'keterangan' => 
      array (
        'label' => 'Keterangan',
        'type' => 'textarea',
        'list' => false,
        'req' => true,
      ),
    ),
  ),
  'penawaran' => 
  array (
    'model' => 'App\\Models\\PgPenawaran',
    'perm' => 'pengadaan',
    'modul' => 'Pengadaan & Pemeliharaan',
    'judul' => 'Penawaran Vendor',
    'maker_checker' => false,
    'fields' => 
    array (
      'no_penawaran' => 
      array (
        'label' => 'No. Penawaran',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'vendor' => 
      array (
        'label' => 'Vendor',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'barang_jasa' => 
      array (
        'label' => 'Barang / Jasa',
        'type' => 'textarea',
        'list' => true,
        'req' => true,
      ),
      'nilai' => 
      array (
        'label' => 'Nilai Penawaran',
        'type' => 'money',
        'list' => true,
        'fmt' => 'money',
        'req' => true,
      ),
      'tanggal' => 
      array (
        'label' => 'Tanggal',
        'type' => 'date',
        'list' => true,
        'fmt' => 'date',
        'req' => true,
      ),
      'status' => 
      array (
        'label' => 'Status',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Diterima',
          1 => 'Evaluasi',
          2 => 'Negosiasi',
          3 => 'Ditolak',
        ),
      ),
      'keterangan' => 
      array (
        'label' => 'Keterangan',
        'type' => 'textarea',
        'list' => false,
        'req' => true,
      ),
    ),
  ),
  'negosiasi' => 
  array (
    'model' => 'App\\Models\\PgNegosiasi',
    'perm' => 'pengadaan',
    'modul' => 'Pengadaan & Pemeliharaan',
    'judul' => 'Negosiasi (Berita Acara)',
    'maker_checker' => false,
    'fields' => 
    array (
      'no_berita_acara' => 
      array (
        'label' => 'No. Berita Acara',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'vendor' => 
      array (
        'label' => 'Vendor',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'barang_jasa' => 
      array (
        'label' => 'Barang / Jasa',
        'type' => 'textarea',
        'list' => true,
        'req' => true,
      ),
      'nilai_awal' => 
      array (
        'label' => 'Nilai Awal',
        'type' => 'money',
        'list' => true,
        'fmt' => 'money',
        'req' => true,
      ),
      'nilai_nego' => 
      array (
        'label' => 'Nilai Nego',
        'type' => 'money',
        'list' => true,
        'fmt' => 'money',
        'req' => true,
      ),
      'tanggal' => 
      array (
        'label' => 'Tanggal',
        'type' => 'date',
        'list' => true,
        'fmt' => 'date',
        'req' => true,
      ),
      'hasil' => 
      array (
        'label' => 'Hasil',
        'type' => 'textarea',
        'list' => false,
        'req' => true,
      ),
    ),
  ),
  'draft_dokumen' => 
  array (
    'model' => 'App\\Models\\PgDraftDokumen',
    'perm' => 'pengadaan',
    'modul' => 'Pengadaan & Pemeliharaan',
    'judul' => 'Draft Dokumen (PKS/NDA/SPK)',
    'maker_checker' => false,
    'fields' => 
    array (
      'jenis' => 
      array (
        'label' => 'Jenis',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'PKS',
          1 => 'NDA',
          2 => 'SPK',
          3 => 'Lainnya',
        ),
      ),
      'no_dokumen' => 
      array (
        'label' => 'No. Dokumen',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'judul' => 
      array (
        'label' => 'Judul',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'vendor' => 
      array (
        'label' => 'Vendor',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'tanggal' => 
      array (
        'label' => 'Tanggal',
        'type' => 'date',
        'list' => true,
        'fmt' => 'date',
        'req' => true,
      ),
      'status' => 
      array (
        'label' => 'Status',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Draft',
          1 => 'Review',
          2 => 'Final',
        ),
      ),
      'keterangan' => 
      array (
        'label' => 'Keterangan',
        'type' => 'textarea',
        'list' => false,
        'req' => true,
      ),
    ),
  ),
  'spk' => 
  array (
    'model' => 'App\\Models\\PgSpk',
    'perm' => 'pengadaan',
    'modul' => 'Pengadaan & Pemeliharaan',
    'judul' => 'SPK (Surat Perintah Kerja)',
    'maker_checker' => false,
    'fields' => 
    array (
      'no_spk' => 
      array (
        'label' => 'No. SPK',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'vendor' => 
      array (
        'label' => 'Vendor',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'pekerjaan' => 
      array (
        'label' => 'Pekerjaan',
        'type' => 'textarea',
        'list' => true,
        'req' => true,
      ),
      'nilai' => 
      array (
        'label' => 'Nilai',
        'type' => 'money',
        'list' => true,
        'fmt' => 'money',
        'req' => true,
      ),
      'tanggal_terbit' => 
      array (
        'label' => 'Tgl Terbit',
        'type' => 'date',
        'list' => true,
        'fmt' => 'date',
        'req' => true,
      ),
      'tanggal_selesai' => 
      array (
        'label' => 'Tgl Selesai',
        'type' => 'date',
        'list' => false,
        'fmt' => 'date',
        'req' => true,
      ),
      'status' => 
      array (
        'label' => 'Status',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Berjalan',
          1 => 'Selesai',
          2 => 'Batal',
        ),
      ),
      'keterangan' => 
      array (
        'label' => 'Keterangan',
        'type' => 'textarea',
        'list' => false,
        'req' => true,
      ),
    ),
  ),
  'reminder' => 
  array (
    'model' => 'App\\Models\\PgReminder',
    'perm' => 'pengadaan',
    'modul' => 'Pengadaan & Pemeliharaan',
    'judul' => 'Reminder Schedule',
    'maker_checker' => false,
    'fields' => 
    array (
      'judul' => 
      array (
        'label' => 'Judul',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'kategori' => 
      array (
        'label' => 'Kategori',
        'type' => 'select',
        'list' => true,
        'req' => true,
        'opts' => 
        array (
          0 => 'PKS',
          1 => 'Sewa',
          2 => 'SPK',
          3 => 'Pemeliharaan',
          4 => 'Lainnya',
        ),
      ),
      'tanggal_jatuh_tempo' => 
      array (
        'label' => 'Tgl Jatuh Tempo',
        'type' => 'date',
        'list' => true,
        'fmt' => 'date',
        'req' => true,
      ),
      'status' => 
      array (
        'label' => 'Status',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Aktif',
          1 => 'Selesai',
        ),
      ),
      'catatan' => 
      array (
        'label' => 'Catatan',
        'type' => 'textarea',
        'list' => false,
        'req' => true,
      ),
    ),
  ),
  'ref_akun' => 
  array (
    'model' => 'App\\Models\\RefAkun',
    'perm' => 'ref_akun',
    'modul' => 'Pengaturan',
    'judul' => 'Referensi Akun Biaya',
    'maker_checker' => false,
    'fields' => 
    array (
      'nama_beban' => 
      array (
        'label' => 'Nama Beban',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'rekening_debet' => 
      array (
        'label' => 'Rekening Debet',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'contoh_keterangan' => 
      array (
        'label' => 'Contoh Keterangan',
        'type' => 'textarea',
        'list' => true,
        'req' => true,
      ),
    ),
  ),
  'surat_masuk' => 
  array (
    'model' => 'App\\Models\\SrSuratMasuk',
    'perm' => 'dokumen_arsip',
    'modul' => 'Dokumen & Kearsipan',
    'judul' => 'Surat Masuk',
    'maker_checker' => false,
    'fields' => 
    array (
      'nomor_agenda' => 
      array (
        'label' => 'Nomor Agenda',
        'type' => 'number',
        'list' => true,
        'req' => true,
        'help' => 'Rekomendasi otomatis (+10 setiap hari baru, +1 di hari yang sama) — boleh diubah / disisip nomor untuk tanggal sebelumnya.',
      ),
      'no_surat' => 
      array (
        'label' => 'No. Surat',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'pengirim' => 
      array (
        'label' => 'Pengirim',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'perihal' => 
      array (
        'label' => 'Perihal',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'tanggal' => 
      array (
        'label' => 'Tanggal',
        'type' => 'date',
        'list' => true,
        'fmt' => 'date',
        'req' => true,
        'default' => 'today',
      ),
      'penerima' => 
      array (
        'label' => 'Penerima',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'lokasi_arsip' => 
      array (
        'label' => 'Lokasi Arsip',
        'type' => 'text',
        'list' => true,
        'req' => true,
        'help' => 'cth: Lemari A / Ordner 3 / Map Merah',
      ),
      'masa_retensi' => 
      array (
        'label' => 'Masa Retensi (tahun)',
        'type' => 'number',
        'list' => false,
        'req' => true,
      ),
      'status_arsip' => 
      array (
        'label' => 'Status Arsip',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Aktif',
          1 => 'Arsip',
          2 => 'Musnah',
        ),
      ),
      'dibuat_oleh' => 
      array (
        'label' => 'Dibuat Oleh',
        'type' => 'auto_user',
        'list' => false,
        'req' => false,
      ),
    ),
  ),
  'surat_keluar' => 
  array (
    'model' => 'App\\Models\\SrSuratKeluar',
    'perm' => 'dokumen_arsip',
    'modul' => 'Dokumen & Kearsipan',
    'judul' => 'Surat Keluar',
    'maker_checker' => false,
    'fields' => 
    array (
      'nomor_agenda' => 
      array (
        'label' => 'Nomor Agenda',
        'type' => 'number',
        'list' => true,
        'req' => true,
        'help' => 'Rekomendasi otomatis (+10 setiap hari baru, +1 di hari yang sama) — boleh diubah / disisip nomor untuk tanggal sebelumnya.',
      ),
      'no_surat' => 
      array (
        'label' => 'No. Surat',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'pengirim' => 
      array (
        'label' => 'Pengirim (Unit)',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'perihal' => 
      array (
        'label' => 'Perihal',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'tanggal' => 
      array (
        'label' => 'Tanggal',
        'type' => 'date',
        'list' => true,
        'fmt' => 'date',
        'req' => true,
        'default' => 'today',
      ),
      'penerima' => 
      array (
        'label' => 'Penerima / Tujuan',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'lokasi_arsip' => 
      array (
        'label' => 'Lokasi Arsip',
        'type' => 'text',
        'list' => true,
        'req' => true,
        'help' => 'cth: Lemari A / Ordner 3 / Map Merah',
      ),
      'masa_retensi' => 
      array (
        'label' => 'Masa Retensi (tahun)',
        'type' => 'number',
        'list' => false,
        'req' => true,
      ),
      'status_arsip' => 
      array (
        'label' => 'Status Arsip',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Aktif',
          1 => 'Arsip',
          2 => 'Musnah',
        ),
      ),
      'dibuat_oleh' => 
      array (
        'label' => 'Dibuat Oleh',
        'type' => 'auto_user',
        'list' => false,
        'req' => false,
      ),
    ),
  ),
  'memo_masuk' => 
  array (
    'model' => 'App\\Models\\SrMemoMasuk',
    'perm' => 'dokumen_arsip',
    'modul' => 'Dokumen & Kearsipan',
    'judul' => 'Memo Masuk',
    'maker_checker' => false,
    'fields' => 
    array (
      'nomor_agenda' => 
      array (
        'label' => 'Nomor Agenda',
        'type' => 'number',
        'list' => true,
        'req' => true,
        'help' => 'Rekomendasi otomatis (+10 setiap hari baru, +1 di hari yang sama) — boleh diubah / disisip nomor untuk tanggal sebelumnya.',
      ),
      'no_surat' => 
      array (
        'label' => 'No. Surat',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'pengirim' => 
      array (
        'label' => 'Pengirim',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'perihal' => 
      array (
        'label' => 'Perihal',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'tanggal' => 
      array (
        'label' => 'Tanggal',
        'type' => 'date',
        'list' => true,
        'fmt' => 'date',
        'req' => true,
        'default' => 'today',
      ),
      'penerima' => 
      array (
        'label' => 'Penerima',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'lokasi_arsip' => 
      array (
        'label' => 'Lokasi Arsip',
        'type' => 'text',
        'list' => true,
        'req' => true,
        'help' => 'cth: Lemari A / Ordner 3 / Map Merah',
      ),
      'masa_retensi' => 
      array (
        'label' => 'Masa Retensi (tahun)',
        'type' => 'number',
        'list' => false,
        'req' => true,
      ),
      'status_arsip' => 
      array (
        'label' => 'Status Arsip',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Aktif',
          1 => 'Arsip',
          2 => 'Musnah',
        ),
      ),
      'dibuat_oleh' => 
      array (
        'label' => 'Dibuat Oleh',
        'type' => 'auto_user',
        'list' => false,
        'req' => false,
      ),
    ),
  ),
  'memo_keluar' => 
  array (
    'model' => 'App\\Models\\SrMemoKeluar',
    'perm' => 'dokumen_arsip',
    'modul' => 'Dokumen & Kearsipan',
    'judul' => 'Memo Keluar',
    'maker_checker' => false,
    'fields' => 
    array (
      'nomor_agenda' => 
      array (
        'label' => 'Nomor Agenda',
        'type' => 'number',
        'list' => true,
        'req' => true,
        'help' => 'Rekomendasi otomatis (+10 setiap hari baru, +1 di hari yang sama) — boleh diubah / disisip nomor untuk tanggal sebelumnya.',
      ),
      'no_surat' => 
      array (
        'label' => 'No. Surat',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'pengirim' => 
      array (
        'label' => 'Pengirim (Unit)',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'perihal' => 
      array (
        'label' => 'Perihal',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'tanggal' => 
      array (
        'label' => 'Tanggal',
        'type' => 'date',
        'list' => true,
        'fmt' => 'date',
        'req' => true,
        'default' => 'today',
      ),
      'penerima' => 
      array (
        'label' => 'Penerima / Tujuan',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'lokasi_arsip' => 
      array (
        'label' => 'Lokasi Arsip',
        'type' => 'text',
        'list' => true,
        'req' => true,
        'help' => 'cth: Lemari A / Ordner 3 / Map Merah',
      ),
      'masa_retensi' => 
      array (
        'label' => 'Masa Retensi (tahun)',
        'type' => 'number',
        'list' => false,
        'req' => true,
      ),
      'status_arsip' => 
      array (
        'label' => 'Status Arsip',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Aktif',
          1 => 'Arsip',
          2 => 'Musnah',
        ),
      ),
      'dibuat_oleh' => 
      array (
        'label' => 'Dibuat Oleh',
        'type' => 'auto_user',
        'list' => false,
        'req' => false,
      ),
    ),
  ),
  'arsip_dokumen' => 
  array (
    'model' => 'App\\Models\\DkArsipDokumen',
    'perm' => 'dokumen_arsip',
    'modul' => 'Dokumen & Kearsipan',
    'judul' => 'Master Arsip Dokumen',
    'maker_checker' => false,
    'fields' => 
    array (
      'kode_arsip' => 
      array (
        'label' => 'Kode Arsip',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'judul' => 
      array (
        'label' => 'Judul Dokumen',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'jenis' => 
      array (
        'label' => 'Jenis',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Fisik',
          1 => 'Digital',
          2 => 'Fisik & Digital',
        ),
      ),
      'kategori' => 
      array (
        'label' => 'Kategori',
        'type' => 'select',
        'list' => true,
        'req' => true,
        'opts' => 
        array (
          0 => 'Surat',
          1 => 'Memo',
          2 => 'Kontrak',
          3 => 'Laporan',
          4 => 'SK',
          5 => 'Kebijakan',
          6 => 'Lainnya',
        ),
      ),
      'tanggal_dokumen' => 
      array (
        'label' => 'Tanggal Dokumen',
        'type' => 'date',
        'list' => true,
        'fmt' => 'date',
        'req' => true,
      ),
      'lokasi_arsip' => 
      array (
        'label' => 'Lokasi Arsip',
        'type' => 'text',
        'list' => true,
        'req' => true,
        'help' => 'cth: Lemari A / Ordner 3 / Map Merah',
      ),
      'masa_retensi' => 
      array (
        'label' => 'Masa Retensi (tahun)',
        'type' => 'number',
        'list' => false,
        'req' => true,
      ),
      'status_arsip' => 
      array (
        'label' => 'Status Arsip',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Aktif',
          1 => 'Arsip',
          2 => 'Musnah',
        ),
      ),
      'keterangan' => 
      array (
        'label' => 'Keterangan',
        'type' => 'textarea',
        'list' => false,
        'req' => true,
      ),
      'dibuat_oleh' => 
      array (
        'label' => 'Diinput Oleh',
        'type' => 'auto_user',
        'list' => false,
        'req' => false,
      ),
    ),
  ),
  'dokumen_legalitas' => 
  array (
    'model' => 'App\\Models\\DkDokumenLegalitas',
    'perm' => 'dokumen_arsip',
    'modul' => 'Dokumen & Kearsipan',
    'judul' => 'Dokumen Legalitas',
    'maker_checker' => false,
    'fields' => 
    array (
      'nama_dokumen' => 
      array (
        'label' => 'Nama Dokumen',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'jenis' => 
      array (
        'label' => 'Jenis',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Izin Usaha',
          1 => 'Sertifikat',
          2 => 'NPWP',
          3 => 'Akta',
          4 => 'SK',
          5 => 'Lisensi',
          6 => 'Lainnya',
        ),
      ),
      'no_dokumen' => 
      array (
        'label' => 'No. Dokumen',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'penerbit' => 
      array (
        'label' => 'Penerbit / Instansi',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'tanggal_terbit' => 
      array (
        'label' => 'Tgl Terbit',
        'type' => 'date',
        'list' => true,
        'fmt' => 'date',
        'req' => true,
      ),
      'tanggal_berlaku' => 
      array (
        'label' => 'Tgl Berlaku Sampai',
        'type' => 'date',
        'list' => true,
        'fmt' => 'date',
        'req' => true,
      ),
      'status' => 
      array (
        'label' => 'Status',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Aktif',
          1 => 'Akan Kadaluarsa',
          2 => 'Kadaluarsa',
          3 => 'Diperpanjang',
        ),
      ),
      'keterangan' => 
      array (
        'label' => 'Keterangan',
        'type' => 'textarea',
        'list' => false,
        'req' => true,
      ),
    ),
  ),
  'perencanaan_kebutuhan' => 
  array (
    'model' => 'App\\Models\\PmPerencanaanKebutuhan',
    'perm' => 'pengadaan',
    'modul' => 'Pengadaan & Pemeliharaan',
    'judul' => 'Perencanaan Kebutuhan',
    'maker_checker' => false,
    'fields' => 
    array (
      'no_rencana' => 
      array (
        'label' => 'No. Rencana',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'periode' => 
      array (
        'label' => 'Periode',
        'type' => 'text',
        'list' => true,
        'req' => true,
        'help' => 'cth: Q1 2026 / Semester I 2026',
      ),
      'jenis_kebutuhan' => 
      array (
        'label' => 'Jenis Kebutuhan',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Barang',
          1 => 'Jasa',
          2 => 'Aset',
        ),
      ),
      'nama_item' => 
      array (
        'label' => 'Nama Barang / Jasa / Aset',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'jumlah' => 
      array (
        'label' => 'Jumlah',
        'type' => 'number',
        'list' => true,
        'req' => true,
      ),
      'satuan' => 
      array (
        'label' => 'Satuan',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'spesifikasi' => 
      array (
        'label' => 'Spesifikasi / Keterangan',
        'type' => 'textarea',
        'list' => false,
        'req' => true,
      ),
      'estimasi_harga' => 
      array (
        'label' => 'Estimasi Harga',
        'type' => 'money',
        'list' => true,
        'fmt' => 'money',
        'req' => true,
      ),
      'prioritas' => 
      array (
        'label' => 'Prioritas',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Darurat',
          1 => 'Tinggi',
          2 => 'Normal',
          3 => 'Rendah',
        ),
      ),
      'keterangan' => 
      array (
        'label' => 'Keterangan',
        'type' => 'textarea',
        'list' => false,
        'req' => true,
      ),
    ),
  ),
  'jadwal_pemeliharaan' => 
  array (
    'model' => 'App\\Models\\PmJadwalPemeliharaan',
    'perm' => 'pemeliharaan_pengawasan',
    'modul' => 'Pemeliharaan & Pengawasan',
    'judul' => 'Jadwal Pemeliharaan Rutin',
    'maker_checker' => false,
    'fields' => 
    array (
      'no_jadwal' => 
      array (
        'label' => 'No. Jadwal',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'nama_aset' => 
      array (
        'label' => 'Nama Aset',
        'type' => 'text',
        'list' => true,
        'req' => true,
        'help' => 'Nama atau kode aset yang akan dipelihara',
      ),
      'jenis_pemeliharaan' => 
      array (
        'label' => 'Jenis Pemeliharaan',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Rutin',
          1 => 'Insidental',
          2 => 'Preventif',
          3 => 'Korektif',
        ),
      ),
      'tanggal_rencana' => 
      array (
        'label' => 'Tgl Rencana',
        'type' => 'date',
        'list' => true,
        'fmt' => 'date',
        'req' => true,
      ),
      'tanggal_realisasi' => 
      array (
        'label' => 'Tgl Realisasi',
        'type' => 'date',
        'list' => true,
        'fmt' => 'date',
        'req' => true,
      ),
      'pelaksana' => 
      array (
        'label' => 'Pelaksana',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'status' => 
      array (
        'label' => 'Status',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Direncanakan',
          1 => 'Dikerjakan',
          2 => 'Selesai',
          3 => 'Dibatalkan',
        ),
      ),
      'keterangan' => 
      array (
        'label' => 'Keterangan',
        'type' => 'textarea',
        'list' => false,
        'req' => true,
      ),
    ),
  ),
  'monitoring_kondisi' => 
  array (
    'model' => 'App\\Models\\PmMonitoringKondisi',
    'perm' => 'pemeliharaan_pengawasan',
    'modul' => 'Pemeliharaan & Pengawasan',
    'judul' => 'Monitoring Kondisi Fisik Aset',
    'maker_checker' => false,
    'fields' => 
    array (
      'nama_aset' => 
      array (
        'label' => 'Nama Aset',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'tanggal_inspeksi' => 
      array (
        'label' => 'Tgl Inspeksi',
        'type' => 'date',
        'list' => true,
        'fmt' => 'date',
        'req' => true,
        'default' => 'today',
      ),
      'kondisi' => 
      array (
        'label' => 'Kondisi',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Baik',
          1 => 'Rusak Ringan',
          2 => 'Rusak Berat',
        ),
      ),
      'temuan' => 
      array (
        'label' => 'Temuan',
        'type' => 'textarea',
        'list' => true,
        'req' => true,
      ),
      'rekomendasi' => 
      array (
        'label' => 'Rekomendasi',
        'type' => 'textarea',
        'list' => false,
        'req' => true,
      ),
      'petugas' => 
      array (
        'label' => 'Petugas Pemeriksa',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'status' => 
      array (
        'label' => 'Status',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Selesai',
          1 => 'Perlu Tindak Lanjut',
        ),
      ),
    ),
  ),
  'pengawasan_penggunaan' => 
  array (
    'model' => 'App\\Models\\PmPengawasanPenggunaan',
    'perm' => 'pemeliharaan_pengawasan',
    'modul' => 'Pemeliharaan & Pengawasan',
    'judul' => 'Pengawasan Penggunaan Aset',
    'maker_checker' => false,
    'fields' => 
    array (
      'nama_aset' => 
      array (
        'label' => 'Nama Aset',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'tanggal' => 
      array (
        'label' => 'Tanggal',
        'type' => 'date',
        'list' => true,
        'fmt' => 'date',
        'req' => true,
        'default' => 'today',
      ),
      'pengguna' => 
      array (
        'label' => 'Unit / Pengguna',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'uraian_penggunaan' => 
      array (
        'label' => 'Uraian Penggunaan',
        'type' => 'textarea',
        'list' => true,
        'req' => true,
      ),
      'kesesuaian' => 
      array (
        'label' => 'Kesesuaian Penggunaan',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Sesuai',
          1 => 'Tidak Sesuai',
          2 => 'Perlu Evaluasi',
        ),
      ),
      'catatan' => 
      array (
        'label' => 'Catatan',
        'type' => 'textarea',
        'list' => false,
        'req' => true,
      ),
      'petugas' => 
      array (
        'label' => 'Petugas Pengawas',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
    ),
  ),
  'tindak_lanjut_perbaikan' => 
  array (
    'model' => 'App\\Models\\PmTindakLanjutPerbaikan',
    'perm' => 'pemeliharaan_pengawasan',
    'modul' => 'Pemeliharaan & Pengawasan',
    'judul' => 'Tindak Lanjut Perbaikan',
    'maker_checker' => false,
    'fields' => 
    array (
      'no_tindak_lanjut' => 
      array (
        'label' => 'No. TL',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'nama_aset' => 
      array (
        'label' => 'Nama Aset',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'sumber' => 
      array (
        'label' => 'Sumber Laporan',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Tiket',
          1 => 'Inspeksi Mandiri',
          2 => 'Pengawasan',
          3 => 'Lainnya',
        ),
      ),
      'uraian_kerusakan' => 
      array (
        'label' => 'Uraian Kerusakan',
        'type' => 'textarea',
        'list' => true,
        'req' => true,
      ),
      'tanggal_laporan' => 
      array (
        'label' => 'Tgl Dilaporkan',
        'type' => 'date',
        'list' => true,
        'fmt' => 'date',
        'req' => true,
        'default' => 'today',
      ),
      'tanggal_perbaikan' => 
      array (
        'label' => 'Tgl Perbaikan',
        'type' => 'date',
        'list' => true,
        'fmt' => 'date',
        'req' => true,
      ),
      'teknisi' => 
      array (
        'label' => 'Teknisi / Pelaksana',
        'type' => 'text',
        'list' => true,
        'req' => true,
      ),
      'hasil_perbaikan' => 
      array (
        'label' => 'Hasil Perbaikan',
        'type' => 'textarea',
        'list' => false,
        'req' => true,
      ),
      'status' => 
      array (
        'label' => 'Status',
        'type' => 'select',
        'list' => true,
        'fmt' => 'badge',
        'req' => true,
        'opts' => 
        array (
          0 => 'Dilaporkan',
          1 => 'Dalam Proses',
          2 => 'Selesai',
          3 => 'Ditangguhkan',
        ),
      ),
    ),
  ),
);
