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
3. Jalankan server Laravel:
   ```bash
   php artisan serve
   ```
4. Buka di browser: `http://localhost:8000` (atau gunakan parameter meja `http://localhost:8000/?table=5`).
