# PARJO - Self-Ordering System (Digital Menu Warmindo & Burjo)

Aplikasi web mobile-first Self-Ordering System untuk **PARJO (Pancong & Burjo)** yang terinspirasi dari antarmuka pemesanan modern bergaya ESB Order.

## 🚀 Fitur Utama
- **Mobile-first Design**: Tampilan responsive dan presisi optimal pada layar smartphone serta container maksimal 480px di desktop.
- **Continuous Scroll & Scrollspy Navigation**: Navigasi kategori (Signature, Burjo & Nasi, Ayam, Telor, Indomie, Kulit, Snack, Pancong, Coffee & Beer, Tea, Sachet & Blended) yang otomatis smooth-scroll ke menu pilihan dan mendeteksi posisi kategori secara real-time.
- **Katalog Menu Lengkap Sesuai Dokumen**: Termasuk menu andalan, harga asli, foto, serta fitur Topping / Add-on resmi.
- **Detail Produk & Modal**: Catatan khusus pemesanan (opsional), counter/stepper jumlah porsi, serta sticky bottom action bar.
- **Review Pesanan & Checkout**:
  - Pilihan Tipe Pemesanan (*Makan di Tempat / Dine-in* atau *Bawa Pulang / Take-away*).
  - Ringkasan subtotal, biaya layanan, dan kode voucher promo.
  - Pilihan pembayaran (QRIS Instant & Bayar di Kasir).
  - Modal bukti sukses pemesanan beserta Order ID.
- **Preview Scan Buku Menu Asli**: Terdapat modal untuk melihat dokumen scan buku menu PARJO langsung di aplikasi.
- **Dashboard Kasir Real-time & Operations Hub** (`/kasir` atau `/admin`):
  - **Live Auto-Sync (3 detik)**: Pesanan yang dikirim oleh pelanggan dari halaman self-ordering otomatis masuk ke dashboard kasir secara real-time tanpa reload halaman.
  - **Notifikasi Suara Interaktif (Dual-Tone Chime)**: Bunyi bel audio otomatis berbunyi ketika ada pesanan baru masuk (dapat diaktifkan/dimatikan).
  - **Manajemen Status Pesanan**: Kasir dapat mengubah alur pesanan dari *Menunggu Konfirmasi* ➔ *Diproses (Dapur)* ➔ *Selesai* atau *Dibatalkan*.
  - **Status Pembayaran Kasir**: Tombol toggle instan untuk menandai status pesanan *Bayar di Kasir* menjadi *LUNAS*.
  - **Filter & Pencarian Lengkap**: Filter tab status pesanan, filter tipe pesanan (Dine-in / Take-away), filter tanggal (Hari ini / Kemarin / Semua), serta kolom pencarian berdasarkan Order ID, nama pelanggan, nomor meja, atau no WhatsApp.
  - **Pratinjau & Cetak Struk Thermal POS**: Format struk kasir thermal 58mm/80mm siap cetak (`window.print()`) dengan detail menu, diskon, biaya layanan, dan identitas PARJO.
  - **Input Pesanan Manual (POS Kasir Walk-In)**: Fitur kasir untuk mencatat pesanan tamu yang memesan langsung di meja kasir.
- **Analisis Bisnis & Laporan Keuangan (Business Intelligence)**:
  - **Metrik Utama (KPI Cards)**: Total Omzet Bersih, Total Transaksi, Rata-rata Nilai Transaksi (Average Order Value / AOV), dan Success Rate.
  - **Filter Periode Analisis**: Hari Ini, 7 Hari Terakhir, Bulan Ini, dan Semua Waktu.
  - **Grafik Interaktif (Chart.js)**:
    - *Grafik Tren Penjualan & Transaksi*: Grafik kombinasi bar dan line chart omzet serta volume order.
    - *Distribusi Metode Pembayaran & Tipe Pesanan*: Doughnut chart perbandingan QRIS vs Bayar Kasir serta Dine-in vs Takeaway.
  - **Tabel Menu Terlaris (Top 10 Best Sellers)**: Peringkat menu terfavorit dengan total porsi terjual dan kontribusi pendapatan.
  - **Kontribusi Penjualan per Kategori**: Progress bar omzet per kategori (Signature, Burjo, Indomie, Pancong, Kopi, Tea, Snack).
  - **Ekspor Laporan Penjualan ke CSV**: Download file CSV laporan penjualan yang kompatibel dibuka di Microsoft Excel.

## 🛠️ Tech Stack
- **Framework**: [Laravel](https://laravel.com) (Blade Templates)
- **Styling**: [Tailwind CSS](https://tailwindcss.com)
- **Reactivity & State Management**: [Alpine.js](https://alpinejs.dev)
- **Icons**: [Font Awesome](https://fontawesome.com)

## 💻 Cara Menjalankan Project Lokal
1. Clone repository:
   ```bash
   git clone https://github.com/Fadhilah05-afk/PPL.git
   cd PPL
   ```
2. Salin environment & atur konfigurasi:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
3. Jalankan migrasi dan seeder database (SQLite bawaan):
   ```bash
   php artisan migrate --seed
   ```
4. Jalankan server Laravel:
   ```bash
   php artisan serve
   ```
5. Buka di browser:
   - **Menu Pelanggan (Self-Order)**: `http://localhost:8000` (atau `http://localhost:8000/?table=5`)
   - **Dashboard Kasir & Analisis**: `http://localhost:8000/kasir` (atau `http://localhost:8000/admin`)
