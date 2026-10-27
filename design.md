# DESIGN SPECIFICATION DOCUMENT (`design.md`)
# PARJO - Self-Ordering System & Cashier Operations Hub

> **Proyek**: Tugas Kelompok Pengembangan Perangkat Lunak (PPL)  
> **Aplikasi**: PARJO (Pancong & Burjo) — Digital Ordering & Cashier Analytics Hub  
> **Versi**: 2.0.0  
> **Tanggal**: 27 Oktober 2026  
> **Status**: Selesai Diimplementasikan & Teruji  

---

## 1. Ikhtisar Proyek (Project Overview)

**PARJO (Pancong & Burjo)** adalah aplikasi web terintegrasi yang menggabungkan:
1. **Customer Self-Ordering System**: Antarmuka pemesanan mandiri ramah seluler (*mobile-first*) yang memungkinkan pelanggan memindai kode QR di meja, menelusuri menu dengan navigasi continuous scroll & scrollspy, memilih varian/catatan kustom, dan melakukan checkout digital (QRIS Instant atau Bayar di Kasir).
2. **Cashier Operations Hub & POS**: Dashboard kasir real-time dengan sinkronisasi otomatis (*auto-polling* setiap 3.5 detik), notifikasi suara audio chime bawaan, kontrol alur pesanan (*Pending ➔ Processing ➔ Completed*), validasi pembayaran kasir, input order walk-in, dan pencetakan struk thermal POS 58mm/80mm.
3. **Business Intelligence & Analytics**: Modul analitik pendapatan yang menyajikan grafik tren penjualan berkala (harian dan per jam), metrik AOV (*Average Order Value*), matriks menu terlaris (*Top 10 Best Sellers*), kontribusi kategori, serta fitur ekspor laporan penjualan ke format CSV/Excel.

---

## 2. Sistem Desain UI/UX (Design System)

### 2.1 Palet Warna (Color Palette)

Identitas visual PARJO memadukan estetika *modern neo-brutalist* hangat yang mencerminkan kultur kuliner Warmindo & Burjo dengan presisi visual modern:

| Nama Token | Kode HEX | Peran & Penggunaan |
| :--- | :--- | :--- |
| `parjo-orange` | `#FF4800` | **Brand Primary**: Tombol aksi utama (CTA), badge harga, sorotan promo, status aktif. |
| `parjo-darkorange` | `#E03E00` | **Primary Hover / Active**: State hover pada tombol dan aksen interaktif. |
| `parjo-green` | `#00583E` | **Brand Secondary**: Header live status meja, badge meja dine-in, status sukses. |
| `parjo-darkgreen`| `#003F2C` | **Secondary Shade**: Border aksen, kontras teks pada badge kuning. |
| `parjo-yellow` | `#EEFD37` | **Accent Highlight**: Badge penanda kategori "Pancong & Burjo", elemen animasi radar. |
| `parjo-ink` | `#1C1917` | **Base Dark / Typography**: Warna teks utama, header navigasi kasir, floating cart bar. |
| `parjo-surface` | `#FDFBF7` | **Canvas Background**: Warna latar belakang kertas gading hangat untuk kenyamanan mata. |
| `parjo-border` | `#E7E5E4` | **Subtle Borders**: Garis pemisah kartu menu dan input form. |

### 2.2 Tipografi (Typography)

* **Font Utama**: **`Plus Jakarta Sans`** (Google Fonts)  
  * Karakter: Bersih, geometris, modern, serta memiliki keterbacaan tinggi (*high legibility*) pada layar smartphone maupun desktop kasir.
  * Skala Bobot: `400 (Regular)`, `500 (Medium)`, `600 (Semi-Bold)`, `700 (Bold)`, `800 (Extra-Bold)`, `900 (Black)`.
* **Font Monospace**: **`JetBrains Mono`** / System Monospace  
  * Penggunaan: Kode pesanan unik (mis. `PRJ-1027-4A2B`), format struk thermal kasir, jam digital transaksi, dan nilai nominal tabel akuntansi.

### 2.3 Bayangan & Elevasi (Shadows & Elevation)

Menggunakan teknik bayangan tegas (*hard shadows*) untuk memberikan umpan balik taktil (*tactile feedback*) yang nyata:
* `shadow-hard`: `4px 4px 0px 0px #1C1917` (digunakan pada kartu hero, badge logo, modal)
* `shadow-hard-sm`: `2px 2px 0px 0px #1C1917` (digunakan pada tombol counter dan filter tab)
* `shadow-hard-orange`: `3px 3px 0px 0px #B83200` (digunakan pada tombol submit utama)
* `shadow-float`: `0 12px 32px -4px rgba(0,0,0,0.12)` (digunakan pada bottom floating cart dan toast alert)

---

## 3. Desain Arsitektur Perangkat Lunak

### 3.1 Pola Arsitektur: Model-View-Controller (MVC) Monolitik Modular

Sistem mengadopsi pola **MVC Monolitik Modular** berdasarkan pertimbangan:
1. **Skala Bisnis**: Sangat pas untuk kebutuhan resto/warmindo tanpa overhead infrastruktur terdistribusi (microservices).
2. **Integritas ACID**: Transaksi pemesanan pelanggan dan pembaruan kasir berada dalam satu basis data relasional terpadu (*single source of truth*).
3. **Pemisahan Layer**:
   * *View (Blade + Alpine.js)*: Menangani reaktivitas state UI dan interaksi instan sisi klien.
   * *Controller (`OrderController` & `CashierDashboardController`)*: Mengorkestrasi logika bisnis, validasi request, dan agregasi data.
   * *Model (`Order` & `OrderItem`)*: Mengenkapsulasi entitas domain dan relasi basis data menggunakan Eloquent ORM.

### 3.2 Diagram Arsitektur Perangkat Lunak

```mermaid
flowchart TD
    subgraph Presentation_Tier ["Presentation Layer (Client Side)"]
        UI_Customer["Mobile Self-Ordering UI<br/>(Tailwind CSS + Alpine.js)"]
        UI_Cashier["Cashier & Analytics Hub<br/>(Tailwind CSS + Alpine.js + Chart.js)"]
    end

    subgraph HTTP_Routing ["HTTP Transport & Middleware Layer"]
        Router["Laravel Route Dispatcher<br/>(routes/web.php)"]
        Middleware["CSRF & Exception Handler<br/>(bootstrap/app.php)"]
    end

    subgraph Controller_Tier ["Application Controller Layer"]
        OrderCtrl["OrderController<br/>(store, liveUpdates, updateStatus, analytics)"]
        DashboardCtrl["CashierDashboardController<br/>(index, view binding)"]
    end

    subgraph Model_Tier ["Domain / Model Layer (Eloquent ORM)"]
        Model_Order["Order Entity<br/>(Attributes, Casts, Business Rules)"]
        Model_Item["OrderItem Entity<br/>(Line items, pricing, notes)"]
    end

    subgraph Data_Tier ["Persistence / Database Layer"]
        DB[("Relational Database<br/>SQLite / MariaDB Engine")]
    end

    UI_Customer -->|"HTTP POST /orders (JSON Payload)"| Router
    UI_Cashier -->|"HTTP GET /orders/live (Polling 3.5s)"| Router
    UI_Cashier -->|"HTTP PATCH /orders/{id}/status"| Router
    UI_Cashier -->|"HTTP GET /orders-analytics"| Router

    Router --> Middleware
    Middleware --> OrderCtrl
    Middleware --> DashboardCtrl

    OrderCtrl --> Model_Order
    OrderCtrl --> Model_Item
    Model_Order -->|"hasMany"| Model_Item

    Model_Order -->|"Query / Insert / Update"| DB
    Model_Item -->|"Insert Line Items"| DB
```

---

## 4. Realisasi Proses Bisnis (Sequence Diagram Pola BCE)

### 4.1 Use Case 1: Pemesanan Mandiri oleh Pelanggan (Self-Ordering & Checkout)

```mermaid
sequenceDiagram
    autonumber
    actor Pelanggan as Pelanggan (Customer)
    participant B_Menu as <<boundary>><br/>UI_KatalogMenu
    participant B_Checkout as <<boundary>><br/>UI_ModalCheckout
    participant B_Receipt as <<boundary>><br/>UI_StrukSukses
    participant C_Order as <<control>><br/>OrderController
    participant E_Order as <<entity>><br/>Order
    participant E_Item as <<entity>><br/>OrderItem
    participant DB as <<entity>><br/>Database (Storage)

    Pelanggan->>B_Menu: Buka menu digital via QR Meja
    B_Menu-->>Pelanggan: Tampilkan daftar menu & keranjang belanja
    Pelanggan->>B_Menu: Pilih produk, tentukan qty & catatan khusus
    Pelanggan->>B_Checkout: Klik "Lihat Pesanan" / Buka Form Checkout
    B_Checkout-->>Pelanggan: Tampilkan ringkasan pesanan & form data
    Pelanggan->>B_Checkout: Isi nama, no WA, tipe order & klik "Kirim Pesanan"
    
    B_Checkout->>C_Order: POST /orders (JSON payload pemesanan)
    activate C_Order
    C_Order->>C_Order: Validasi payload (nama, WA, items min: 1)
    
    alt Data Valid
        C_Order->>DB: DB::beginTransaction()
        C_Order->>C_Order: Generate unique order_code ("PRJ-MMDD-XXXX")
        C_Order->>E_Order: create(customer_name, table, subtotal, total, status: 'pending')
        E_Order->>DB: INSERT INTO orders ...
        DB-->>E_Order: Kembalikan Order ID
        
        loop Untuk setiap item di keranjang
            C_Order->>E_Item: create(order_id, product_name, price, qty, notes)
            E_Item->>DB: INSERT INTO order_items ...
        end
        
        C_Order->>DB: DB::commit()
        C_Order-->>B_Checkout: 201 Created (JSON: success, order_code, data)
        deactivate C_Order
        
        B_Checkout->>B_Receipt: Render bukti transaksi dengan Order ID
        B_Receipt-->>Pelanggan: Tampilkan Struk Digital Berhasil
    else Data Tidak Valid
        C_Order-->>B_Checkout: 422 Unprocessable Entity (error validation)
        B_Checkout-->>Pelanggan: Tampilkan pesan error pada form
    end
```

### 4.2 Use Case 2: Pemrosesan & Pembaruan Status Pesanan oleh Kasir (Live Polling & Kitchen Workflow)

```mermaid
sequenceDiagram
    autonumber
    actor Kasir as Kasir / Operator
    participant B_Dash as <<boundary>><br/>UI_DashboardKasir
    participant B_Audio as <<boundary>><br/>UI_AudioChime
    participant B_Thermal as <<boundary>><br/>UI_ModalStrukThermal
    participant C_Order as <<control>><br/>OrderController
    participant E_Order as <<entity>><br/>Order
    participant DB as <<entity>><br/>Database (Storage)

    loop Setiap 3.5 Detik (Auto-Poll Background Task)
        B_Dash->>C_Order: GET /orders/live?status=all&date=today
        activate C_Order
        C_Order->>E_Order: query orders with items (where date = today)
        E_Order->>DB: SELECT * FROM orders ...
        DB-->>E_Order: ResultSet pesanan aktif & counts
        C_Order-->>B_Dash: 200 OK (orders, status_counts, latest_id)
        deactivate C_Order
        
        opt Jika latest_id > previous_latest_id
            B_Dash->>B_Audio: playNotificationSound() (Dual-tone Synth)
            B_Audio-->>Kasir: [Bunyi Notifikasi Bel Masuk]
            B_Dash-->>Kasir: Tampilkan Alert Toast "Pesanan Baru Masuk!"
        end
    end

    Kasir->>B_Dash: Tinjau kartu pesanan baru & klik "Proses Dapur"
    B_Dash->>C_Order: PATCH /orders/{id}/status (status: "processing")
    activate C_Order
    C_Order->>E_Order: find(id) & update(status: "processing")
    E_Order->>DB: UPDATE orders SET status='processing' ...
    DB-->>E_Order: Sukses
    C_Order-->>B_Dash: 200 OK (status updated)
    deactivate C_Order
    B_Dash-->>Kasir: Pindahkan kartu pesanan ke tab "Diproses"

    Kasir->>B_Dash: Terima pembayaran tunai & klik "Tandai Lunas"
    B_Dash->>C_Order: PATCH /orders/{id}/payment (payment_status: "paid")
    activate C_Order
    C_Order->>E_Order: update(payment_status: "paid")
    E_Order->>DB: UPDATE orders SET payment_status='paid' ...
    DB-->>E_Order: Sukses
    C_Order-->>B_Dash: 200 OK (payment updated)
    deactivate C_Order
    B_Dash-->>Kasir: Ubah badge pembayaran menjadi "LUNAS" (Hijau)

    Kasir->>B_Dash: Klik ikon Cetak Struk
    B_Dash->>B_Thermal: Buka Pratinjau Struk Thermal POS (58mm/80mm)
    Kasir->>B_Thermal: Klik "Cetak Sekarang"
    B_Thermal-->>Kasir: Trigger window.print() ke Printer Kasir
```

---

## 5. Desain Basis Data (Database Design)

### 5.1 Class Diagram

```mermaid
classDiagram
    direction TB

    class OrderController {
        +store(Request request) JsonResponse
        +liveUpdates(Request request) JsonResponse
        +updateStatus(Request request, Order order) JsonResponse
        +updatePayment(Request request, Order order) JsonResponse
        +show(Order order) JsonResponse
        +analytics(Request request) JsonResponse
        +exportCsv(Request request) StreamedResponse
        -detectCategoryFromName(string name) string
    }

    class CashierDashboardController {
        +index() View
    }

    class Order {
        +int id
        +string order_code
        +int table_number
        +string order_type
        +string customer_name
        +string customer_phone
        +string customer_email
        +float subtotal
        +float service_fee
        +float discount
        +string voucher_code
        +float total_price
        +string payment_method
        +string payment_status
        +string status
        +text notes
        +timestamp created_at
        +timestamp updated_at
        +items() HasMany
    }

    class OrderItem {
        +int id
        +int order_id
        +int product_id
        +string product_name
        +string category
        +float price
        +int quantity
        +float subtotal
        +string notes
        +timestamp created_at
        +timestamp updated_at
        +order() BelongsTo
    }

    class User {
        +int id
        +string name
        +string email
        +string password
        +timestamp email_verified_at
        +timestamp created_at
        +timestamp updated_at
    }

    class WebOrderView {
        <<boundary>>
        +cart: Array
        +customer: Object
        +tableNumber: Integer
        +orderType: String
        +processPayment()
        +formatRupiah(amount)
    }

    class CashierDashboardView {
        <<boundary>>
        +orders: Array
        +counts: Object
        +soundEnabled: Boolean
        +pollLiveOrders()
        +updateStatus(order, status)
        +togglePayment(order)
        +openPrintModal(order)
        +fetchAnalytics()
    }

    WebOrderView ..> OrderController : HTTP POST
    CashierDashboardView ..> OrderController : HTTP GET / PATCH
    CashierDashboardView ..> CashierDashboardController : HTTP GET /kasir

    OrderController ..> Order : Manipulates
    OrderController ..> OrderItem : Manipulates
    Order "1" *-- "1..*" OrderItem : contains
```

### 5.2 Entity-Relationship Diagram (ERD)

```mermaid
erDiagram
    USERS {
        bigint id PK "Auto Increment"
        string name "Nama Petugas / Kasir"
        string email "Email Akun (Unique)"
        string password "Hashed Password"
        timestamp email_verified_at "Nullable"
        timestamp created_at "Created timestamp"
        timestamp updated_at "Updated timestamp"
    }

    ORDERS {
        bigint id PK "Auto Increment"
        string order_code UK "Unique Code (e.g. PRJ-1027-ABCD)"
        integer table_number "Nullable (Nomor meja bila dine-in)"
        string order_type "Enum: dine-in, take-away"
        string customer_name "Nama Pelanggan"
        string customer_phone "Nomor WhatsApp Pemesan"
        string customer_email "Nullable Email"
        decimal subtotal "Total harga kotor produk"
        decimal service_fee "Biaya layanan (mis. Rp 2.000)"
        decimal discount "Nominal diskon promo"
        string voucher_code "Nullable Kode Voucher"
        decimal total_price "Total tagihan akhir"
        string payment_method "Metode: Bayar di Kasir, QRIS Instant"
        string payment_status "Enum: pending, paid"
        string status "Enum: pending, processing, completed, cancelled"
        text notes "Catatan umum pesanan"
        timestamp created_at "Waktu pembuatan pesanan"
        timestamp updated_at "Waktu modifikasi status"
    }

    ORDER_ITEMS {
        bigint id PK "Auto Increment"
        bigint order_id FK "References ORDERS(id) ON DELETE CASCADE"
        bigint product_id "Nullable ID katalog produk"
        string product_name "Nama item makanan / minuman"
        string category "Kategori menu"
        decimal price "Harga satuan saat transaksi terjadi"
        integer quantity "Jumlah porsi yang dipesan"
        decimal subtotal "price x quantity"
        string notes "Catatan khusus per item"
        timestamp created_at "Created timestamp"
        timestamp updated_at "Updated timestamp"
    }

    ORDERS ||--|{ ORDER_ITEMS : "has"
```

### 5.3 Kamus Data & Skema Tabel

#### Tabel: `orders`
* Primary Key: `id`
* Foreign Key: `-`

| Kolom | Tipe | Nullable | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | No | Auto-increment primary key |
| `order_code` | `VARCHAR(50)` | No | Kode unik transaksi pemesanan (Unique Index) |
| `table_number` | `INTEGER` | Yes | Nomor meja tamu (NULL bila pesanan Take-Away) |
| `order_type` | `VARCHAR(20)` | No | Tipe pesanan: `dine-in` atau `take-away` |
| `customer_name` | `VARCHAR(100)` | No | Nama pemesan |
| `customer_phone` | `VARCHAR(30)` | No | Nomor kontak WhatsApp pemesan |
| `customer_email` | `VARCHAR(100)` | Yes | Surel pemesan |
| `subtotal` | `DECIMAL(12,2)` | No | Total nominal item makanan/minuman |
| `service_fee` | `DECIMAL(12,2)` | No | Biaya layanan sistem |
| `discount` | `DECIMAL(12,2)` | No | Potongan harga voucher |
| `voucher_code` | `VARCHAR(50)` | Yes | Kode voucher yang dipakai |
| `total_price` | `DECIMAL(12,2)` | No | Total akhir tagihan transaksi |
| `payment_method` | `VARCHAR(50)` | No | Metode: `Bayar di Kasir`, `QRIS Instant` |
| `payment_status` | `VARCHAR(20)` | No | Status pembayaran: `pending`, `paid` |
| `status` | `VARCHAR(20)` | No | Status order: `pending`, `processing`, `completed`, `cancelled` |
| `notes` | `TEXT` | Yes | Catatan pesanan |
| `created_at` | `TIMESTAMP` | Yes | Timestamp pembuatan transaksi |
| `updated_at` | `TIMESTAMP` | Yes | Timestamp modifikasi data |

#### Tabel: `order_items`
* Primary Key: `id`
* Foreign Key: `order_id` (mereferensi ke `orders.id` dengan onDelete Cascade)

| Kolom | Tipe | Nullable | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | No | Auto-increment primary key |
| `order_id` | `BIGINT UNSIGNED` | No | Foreign Key yang terhubung ke tabel `orders` |
| `product_id` | `BIGINT UNSIGNED` | Yes | ID katalog referensi produk |
| `product_name` | `VARCHAR(150)` | No | Nama produk pada saat transaksi |
| `category` | `VARCHAR(50)` | Yes | Kategori menu (Signature, Burjo, Pancong, Kopi, Tea, dll.) |
| `price` | `DECIMAL(12,2)` | No | Harga satuan pada saat transaksi terjadi |
| `quantity` | `INTEGER` | No | Jumlah porsi yang dipesan |
| `subtotal` | `DECIMAL(12,2)` | No | Subtotal harga (`price` * `quantity`) |
| `notes` | `VARCHAR(255)` | Yes | Catatan khusus per menu |
| `created_at` | `TIMESTAMP` | Yes | Timestamp dibuat |
| `updated_at` | `TIMESTAMP` | Yes | Timestamp diubah |

### 5.4 Audit Normalisasi (1NF, 2NF, 3NF)

* **1NF**: Terpenuhi. Seluruh nilai sel bersifat atomik, tidak ada repeating group atau multi-value string array.
* **2NF**: Terpenuhi. Semua atribut non-kunci bergantung secara fungsional penuh pada primary key masing-masing tabel.
* **3NF & Justifikasi Denormalisasi Terkendali**:  
  Skema berada pada 3NF tanpa transitive dependency antar atribut non-key. Penyimpanan langsung kolom `price` dan `product_name` pada `order_items` merupakan **denormalisasi terkendali (*historical snapshot pattern*)** yang esensial pada aplikasi kasir POS: jika harga menu di katalog diubah di masa depan, nominal riwayat penjualan masa lampau tidak akan terdistorsi.

---

## 6. Desain Arsitektur Fisik (Deployment Diagram)

```mermaid
flowchart TD
    subgraph Client_Tier ["Client Devices Node (End Users)"]
        node_mobile["Smartphone Pelanggan<br/>(Chrome / Safari / Firefox Mobile)<br/>Protocol: HTTPS (TLS 1.3)"]
        node_pos["PC Kasir / Tablet Staf<br/>(Chrome Desktop / Edge)<br/>Protocol: HTTPS (TLS 1.3)"]
        node_printer["Thermal Receipt Printer<br/>(58mm / 80mm ESC/POS)<br/>Interface: USB / Bluetooth / Local Spooler"]
        node_pos -.->|"Print Spooler Event (window.print)"| node_printer
    end

    subgraph Server_Tier ["Application Server Node (Cloud VPS / Local Server)"]
        node_nginx["Web Server: Nginx / Apache<br/>- Reverse Proxy<br/>- Static Assets Server (CSS/JS)<br/>- SSL Termination (Let's Encrypt)"]
        node_php["PHP 8.2+ Runtime Engine<br/>(PHP-FPM / CLI Server)"]
        node_laravel["Laravel 11 Application Core<br/>- Routing Engine<br/>- Blade Template Engine<br/>- Eloquent ORM Engine"]
        
        node_nginx -->|"FastCGI Protocol"| node_php
        node_php --> node_laravel
    end

    subgraph Data_Tier ["Database Storage Node"]
        node_db[("Relational Database Engine<br/>- Development: SQLite 3 File Engine<br/>- Production: MariaDB 10.11 / MySQL 8.0 Engine")]
    end

    node_mobile -->|"HTTPS Port 443"| node_nginx
    node_pos -->|"HTTPS Port 443"| node_nginx
    node_laravel -->|"PDO SQL Connection"| node_db
```

---

## 7. Rujukan File & Prototipe Antarmuka

| Komponen Desain | Lokasi File di Proyek | Keterangan |
| :--- | :--- | :--- |
| **Katalog & Checkout Pelanggan** | `resources/views/welcome.blade.php` | Blade view self-ordering live |
| **Dashboard Kasir & Analitik** | `resources/views/cashier/dashboard.blade.php` | Blade view kasir real-time & Chart.js |
| **Figma HTML Template (Web Order)** | `figma_templates/web_order_ui.html` | 3 Frame Mobile statis siap import Figma |
| **Figma HTML Template (Kasir)** | `figma_templates/kasir_dashboard_ui.html` | Frame Desktop Kasir, grafik, & thermal struk |
| **Model Relasional Orders** | `app/Models/Order.php` & `OrderItem.php` | Eloquent ORM classes |
| **Controller Logika Bisnis** | `app/Http/Controllers/OrderController.php` | Pengendali transaksi, live poll & laporan |
