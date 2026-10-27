# DOKUMENTASI DESAIN PERANCANGAN PERANGKAT LUNAK (PPL)
## SISTEM SELF-ORDERING & DASHBOARD KASIR "PARJO" (PANCONG & BURJO)

Dokumen ini memuat seluruh perancangan arsitektur, realisasi proses bisnis (Sequence Diagram BCE), desain basis data (Class Diagram & ERD), verifikasi normalisasi, desain UI/UX, serta arsitektur fisik (Deployment Diagram) untuk sistem web pemesanan dan kasir **PARJO**.

---

## A.1 Desain Arsitektur Perangkat Lunak

### Pola Arsitektur yang Dipilih:
**Model-View-Controller (MVC) Monolitik Modular (Modular Monolith / Layered Architecture)**

### Alasan Pemilihan:
1. **Karakteristik & Skala Proyek Capstone (Rujukan Modul 5 Bagian 3.4)**:  
   Sistem PARJO dirancang untuk operasional restoran/warmindo dengan skala *single-store* hingga beberapa cabang mandiri. Pada skala ini, arsitektur monolitik modular merupakan pilihan paling optimal karena menghindari kompleksitas berlebih (*overhead*) dari *Microservices*, seperti latensi jaringan antar-layanan (*network latency*), konsistensi terdistribusi (*distributed transactions*), dan kebutuhan orkestrasi kontainer rumit (Kubernetes/Service Mesh).
2. **Integritas Transaksi & Data Tunggal (*Single Source of Truth*)**:  
   Pemesanan dari meja pelanggan (*self-ordering*) dan pengelolaan pesanan oleh kasir mengakses basis data relasional yang sama secara terpusat. Hal ini menjamin konsistensi data instan (*ACID compliant*), mencegah duplikasi nomor meja, serta memastikan pembaruan status pesanan langsung tersinkronisasi tanpa dependensi *event broker* terdistribusi.
3. **Kohesi Tinggi dan Kopling Rendah (*High Cohesion, Low Coupling*)**:  
   Arsitektur MVC Laravel memisahkan tanggung jawab sistem secara tegas:
   * **Presentation Layer (View)**: Menggunakan Blade template yang dipadukan dengan Alpine.js untuk reaktivitas sisi klien dan Tailwind CSS untuk styling modular.
   * **Application / Logic Layer (Controller)**: Menangani validasi request, alur bisnis pemesanan, agregasi statistik analitik, dan format stream data CSV.
   * **Domain / Persistence Layer (Model)**: Menggunakan Eloquent ORM untuk memodelkan entitas bisnis (`Order`, `OrderItem`, `User`) dan relasinya.
4. **Efisiensi Pengujian dan Pemeliharaan**:  
   Seluruh siklus aplikasi dapat diuji secara menyeluruh (*end-to-end*) di lingkungan lokal maupun staging dengan konfigurasi dependensi tunggal.

### Diagram Arsitektur Perangkat Lunak:

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

## A.2 Realisasi Proses Bisnis: Sequence Diagram Kunci (Pola BCE)

Setiap objek dalam Sequence Diagram diklasifikasikan menggunakan pola **Boundary / Control / Entity (BCE)** sesuai panduan Modul 5 Bagian 4:
* **Boundary (`<<boundary>>`)**: Komponen antarmuka pengguna (halaman web, modal, popup notifikasi).
* **Control (`<<control>>`)**: Komponen pengontrol logika bisnis (`OrderController`).
* **Entity (`<<entity>>`)**: Komponen data bisnis (`Order`, `OrderItem`, Database).

---

### Sequence Diagram 1: Use Case Pemesanan Mandiri oleh Pelanggan (Customer Self-Ordering & Checkout)

**Deskripsi Alur**: Pelanggan memilih menu makanan/minuman dari antarmuka digital di smartphone, memasukkan data diri dan nomor meja, kemudian sistem memvalidasi dan mencatat pesanan ke dalam basis data melalui transaksi atomik.

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

---

### Sequence Diagram 2: Use Case Pemrosesan & Pembaruan Status Pesanan oleh Kasir (Live Order Polling, Kitchen Processing & Settlement)

**Deskripsi Alur**: Dashboard kasir melakukan pemantauan berkala (*auto-polling*), menerima pesanan baru secara real-time disertai audio chime, mengubah status pesanan ke tahap dapur, mencatat status pembayaran lunas, dan mencetak struk thermal POS.

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

### Sequence Diagram 3: Use Case Analisis Bisnis & Ekspor Laporan Penjualan (Business Analytics)

**Deskripsi Alur**: Kasir/manajer membuka tab analitik, sistem mengagregasi data penjualan berdasarkan periode terpilih, merender grafik visual tren dan distribusi pembayaran, serta mengunduh berkas laporan CSV.

```mermaid
sequenceDiagram
    autonumber
    actor Manajer as Manajer / Kasir
    participant B_Analytics as <<boundary>><br/>UI_TabAnalisis
    participant B_Chart as <<boundary>><br/>ChartJS_Engine
    participant C_Order as <<control>><br/>OrderController
    participant E_Item as <<entity>><br/>OrderItem
    participant E_Order as <<entity>><br/>Order
    participant DB as <<entity>><br/>Database (Storage)

    Manajer->>B_Analytics: Buka tab "Analisis & Laporan" & pilih periode ("Hari Ini")
    B_Analytics->>C_Order: GET /orders-analytics?period=today
    activate C_Order
    
    C_Order->>E_Order: calculate metrics (SUM total_price, COUNT orders, AOV)
    E_Order->>DB: SELECT SUM(total_price), COUNT(id) FROM orders ...
    DB-->>E_Order: Metrik ringkasan
    
    C_Order->>E_Item: getTopSellingProducts(limit: 10)
    E_Item->>DB: SELECT product_name, SUM(qty) GROUP BY product_name ORDER BY SUM(qty) DESC
    DB-->>E_Item: Daftar 10 menu terlaris
    
    C_Order->>E_Order: getHourlySalesTrend()
    E_Order->>DB: Agregasi omzet per slot 2 jam
    DB-->>E_Order: Dataset tren waktu
    
    C_Order-->>B_Analytics: 200 OK (summary, top_products, category_share, sales_trend)
    deactivate C_Order
    
    B_Analytics->>B_Chart: Inisialisasi bar/line chart & doughnut chart
    B_Chart-->>B_Analytics: Render canvas grafik analitik interaktif
    B_Analytics-->>Manajer: Tampilkan metrik KPI, grafik omzet, & tabel menu terlaris

    Manajer->>B_Analytics: Klik tombol "Unduh CSV"
    B_Analytics->>C_Order: GET /orders-export
    activate C_Order
    C_Order->>E_Order: query all orders with relations
    E_Order->>DB: SELECT * FROM orders JOIN order_items ...
    DB-->>E_Order: Seluruh riwayat transaksi
    C_Order-->>Manajer: StreamedResponse (file Laporan-Penjualan-PARJO.csv)
    deactivate C_Order
```

---

## A.3 Desain Basis Data (Class Diagram dan ERD)

### Class Diagram

Class diagram menggambarkan struktur kelas logika pengendali (*Controller*), representasi entitas data (*Entity Eloquent Models*), serta antarmuka (*Boundary Views*).

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

---

### Entity-Relationship Diagram (ERD)

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
        string category "Kategori (Signature, Burjo, Pancong, Kopi, dll.)"
        decimal price "Harga satuan saat transaksi terjadi"
        integer quantity "Jumlah porsi yang dipesan"
        decimal subtotal "price x quantity"
        string notes "Catatan khusus (mis. Pedas sedang, less ice)"
        timestamp created_at "Created timestamp"
        timestamp updated_at "Updated timestamp"
    }

    ORDERS ||--|{ ORDER_ITEMS : "has"
```

---

### Spesifikasi Skema Tabel

#### 1. Tabel: `orders`
Menyimpan data induk setiap transaksi pemesanan yang dilakukan pelanggan atau diinput langsung oleh kasir.

| Nama Kolom | Tipe Data | Constraint | Keterangan Singkat |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | Identifikator unik pesanan (Internal ID) |
| `order_code` | `VARCHAR(50)` | `UNIQUE, NOT NULL` | Kode referensi pesanan ramah pengguna (mis. `PRJ-1027-4A2B`) |
| `table_number` | `INTEGER` | `NULLABLE` | Nomor meja pelanggan (bernilai NULL jika Take-Away) |
| `order_type` | `VARCHAR(20)` | `DEFAULT 'dine-in'` | Tipe pesanan: `dine-in` (Makan di Meja) atau `take-away` |
| `customer_name` | `VARCHAR(100)` | `NOT NULL` | Nama lengkap atau panggilan pemesan |
| `customer_phone` | `VARCHAR(30)` | `NOT NULL` | Nomor kontak WhatsApp pemesan untuk konfirmasi pesanan |
| `customer_email` | `VARCHAR(100)` | `NULLABLE` | Alamat surel pemesan (opsional untuk struk digital) |
| `subtotal` | `DECIMAL(12,2)` | `DEFAULT 0.00` | Akumulasi harga seluruh produk sebelum biaya dan diskon |
| `service_fee` | `DECIMAL(12,2)` | `DEFAULT 0.00` | Biaya layanan sistem restoran (mis. Rp 2.000) |
| `discount` | `DECIMAL(12,2)` | `DEFAULT 0.00` | Potongan harga dari voucher promo |
| `voucher_code` | `VARCHAR(50)` | `NULLABLE` | Kode voucher promo yang digunakan |
| `total_price` | `DECIMAL(12,2)` | `DEFAULT 0.00` | Total nominal final yang wajib dibayarkan pelanggan |
| `payment_method` | `VARCHAR(50)` | `NOT NULL` | Metode bayar: `Bayar di Kasir` atau `QRIS Instant` |
| `payment_status` | `VARCHAR(20)` | `DEFAULT 'pending'` | Status pembayaran: `pending` (Belum Lunas) atau `paid` (Lunas) |
| `status` | `VARCHAR(20)` | `DEFAULT 'pending'` | Alur operasional: `pending`, `processing`, `completed`, `cancelled` |
| `notes` | `TEXT` | `NULLABLE` | Catatan keseluruhan pesanan dari tamu |
| `created_at` | `TIMESTAMP` | `NULLABLE` | Waktu transaksi dibuat |
| `updated_at` | `TIMESTAMP` | `NULLABLE` | Waktu status terakhir diperbarui |

---

#### 2. Tabel: `order_items`
Menyimpan rincian item (menu makanan/minuman) dari setiap pesanan.

| Nama Kolom | Tipe Data | Constraint | Keterangan Singkat |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | Identifikator unik baris item |
| `order_id` | `BIGINT UNSIGNED` | `FK, NOT NULL` | Relasi ke `orders(id)` dengan opsi *ON DELETE CASCADE* |
| `product_id` | `BIGINT UNSIGNED` | `NULLABLE` | ID referensi katalog menu produk |
| `product_name` | `VARCHAR(150)` | `NOT NULL` | Nama menu saat dibeli (mis. *Nasi Ayam Kuah Laos*) |
| `category` | `VARCHAR(50)` | `NULLABLE` | Kategori menu (Signature, Burjo, Pancong, Kopi, Tea, dll.) |
| `price` | `DECIMAL(12,2)` | `NOT NULL` | Harga satuan produk pada saat transaksi terjadi |
| `quantity` | `INTEGER` | `NOT NULL, DEFAULT 1`| Kuantitas/jumlah porsi yang dipesan |
| `subtotal` | `DECIMAL(12,2)` | `NOT NULL` | Nilai subtotal item (`price` dikali `quantity`) |
| `notes` | `VARCHAR(255)` | `NULLABLE` | Catatan khusus per menu (mis. *Pedas sedang, less ice*) |
| `created_at` | `TIMESTAMP` | `NULLABLE` | Waktu baris data dibuat |
| `updated_at` | `TIMESTAMP` | `NULLABLE` | Waktu baris data dimodifikasi |

---

#### 3. Tabel: `users`
Menyimpan kredensial otentikasi kasir/staf pengelola sistem.

| Nama Kolom | Tipe Data | Constraint | Keterangan Singkat |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | Identifikator unik pengguna/kasir |
| `name` | `VARCHAR(255)` | `NOT NULL` | Nama operator kasir / admin |
| `email` | `VARCHAR(255)` | `UNIQUE, NOT NULL` | Alamat email untuk login sistem |
| `password` | `VARCHAR(255)` | `NOT NULL` | Kata sandi terenkripsi (Bcrypt hash) |
| `email_verified_at` | `TIMESTAMP` | `NULLABLE` | Waktu verifikasi email |
| `created_at` | `TIMESTAMP` | `NULLABLE` | Waktu akun didaftarkan |
| `updated_at` | `TIMESTAMP` | `NULLABLE` | Waktu akun diperbarui |

---

### Verifikasi Normalisasi (1NF, 2NF, 3NF)

Tim telah melakukan verifikasi normalisasi terhadap skema basis data di atas:

1. **Bentuk Normal Pertama (1NF)**:
   * **Terpenuhi**. Setiap sel data pada tabel `orders`, `order_items`, dan `users` bernilai tunggal (*atomic value*). Tidak terdapat atribut bernilai ganda (*multi-valued attributes*) maupun kelompok berulang (*repeating groups*). Item menu pesanan yang berulang telah dipisahkan ke tabel tersendiri (`order_items`). Setiap baris diidentifikasi oleh Primary Key yang unik.
2. **Bentuk Normal Kedua (2NF)**:
   * **Terpenuhi**. Skema berada dalam 1NF dan seluruh atribut non-key bergantung penuh secara fungsional (*fully functional dependency*) pada Primary Key masing-masing tabel. Pada tabel `order_items`, seluruh atribut seperti `product_name`, `price`, `quantity`, dan `subtotal` bergantung pada `id` (PK tunggal), bukan pada sebagian kunci (*tidak ada partial dependency*).
3. **Bentuk Normal Ketiga (3NF) & Alasan Denormalisasi Terkendali**:
   * **Terpenuhi secara terukur**. Skema berada dalam 2NF dan tidak mengandung ketergantungan transitif (*transitive dependency*) antar atribut non-key.
   * **Penjelasan Denormalisasi Terkendali (*Controlled Denormalization*)**:
     Pada tabel `order_items`, kolom `product_name`, `category`, dan `price` sengaja disimpan secara langsung (bukan hanya mereferensikan `product_id`). Hal ini merupakan praktik standar dalam rekayasa sistem POS (*Point of Sale*) dan *e-commerce*:
     > **Alasan Bisnis & Integritas Keuangan**: Harga menu restoran bersifat dinamis (dapat naik atau turun di masa mendatang). Jika harga hanya diambil melalui join ke tabel produk, maka perubahan harga di masa depan akan secara keliru mengubah histori pendapatan dan struk transaksi lama. Menyimpan *historical snapshot* harga dan nama menu di `order_items` menjamin akurasi laporan keuangan dan audit legalitas struk kasir sepanjang waktu.

---

## A.4 Desain Antarmuka Pengguna (UI/UX)

### Ringkasan Pendekatan Desain:
* **Mobile-First Responsive**: Sisi pemesanan mandiri pelanggan dirancang khusus berorientasi perangkat smartphone (*viewport container* maksimal 480px di desktop) agar identik dengan pengalaman aplikasi *native self-ordering* bergaya ESB Order.
* **Continuous Scroll & Real-Time Scrollspy**: Navigasi kategori dinamis (*Signature, Burjo & Nasi, Indomie, Pancong, Kopi, Tea*) yang otomatis menggeser layar secara halus (*smooth scroll*) dan menyorot tab aktif berdasarkan posisi scroll pengguna.
* **Tactile Feedback & Visual Hierarchy**: Mengusung estetika visual modern brutalist hangat khas kultur Warmindo (palet warna Oranye `#FF4800`, Hijau Botol `#00583E`, dan Kuning `#EEFD37`) dengan bayangan tegas (*hard shadows*) dan tipografi *Plus Jakarta Sans* yang mudah dibaca.
* **Desktop Operations Hub (Kasir)**: Antarmuka kasir dirancang melebar untuk layar desktop/tablet kasir, memadukan kartu antrean pesanan real-time, tombol aksi status cepat, pratinjau struk thermal 58mm/80mm, serta visualisasi grafik analitik bisnis interaktif.

### Contoh Layar Kunci (Key Screens):

1. **Layar Kunci 1: Mobile Self-Ordering Catalog & Floating Basket**  
   Menampilkan identitas meja (`MEJA #05`), banner promo produk andalan, scroll bar kategori, kartu produk dengan foto asli, badge status (*Favorite/Lumer*), stepper porsi, serta *sticky bottom action bar* yang memperlihatkan akumulasi belanja pelanggan secara real-time.
2. **Layar Kunci 2: Review Pesanan, Pilihan Metode Bayar & Struk Sukses**  
   Menampilkan pemilih tipe pesanan (*Makan di Meja vs Take Away*), isian nama dan nomor kontak WhatsApp, perincian subtotal dan biaya layanan, opsi metode bayar (*QRIS Instant vs Kasir*), serta modal sukses pemesanan berformat struk digital ESB dengan kode pesanan unik.
3. **Layar Kunci 3: Dashboard Kasir Real-Time & Business Analytics Hub**  
   Menampilkan kartu metrik KPI harian (*Menunggu, Diproses, Selesai, Omzet*), grid pesanan aktif dengan indikator live-polling 3.5 detik, tombol pembaruan status dapur dan pembayaran lunas, cetak struk thermal POS, serta panel visual grafik tren omzet dan tabel *Top 10 Menu Terlaris*.

> **Tautan Berkas Prototipe Interaktif & File Figma Ready**:  
> Seluruh template HTML statis yang siap di-import langsung menjadi komponen vektor dan frame Figma dapat diakses di repositori GitHub proyek:  
> 🔗 `https://github.com/Fadhilah05-afk/PPL/tree/main/figma_templates`  
> * [web_order_ui.html](https://github.com/Fadhilah05-afk/PPL/blob/main/figma_templates/web_order_ui.html) *(3 Frame Mobile: Katalog, Checkout, Struk Digital)*  
> * [kasir_dashboard_ui.html](https://github.com/Fadhilah05-afk/PPL/blob/main/figma_templates/kasir_dashboard_ui.html) *(Desktop Frame: Dashboard Kasir, Analitik Bisnis & Struk Thermal POS)*

---

## A.5 Desain Arsitektur Fisik (Deployment Diagram)

### Diagram Fisik Arsitektur Sistem:

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

### Rencana Teknologi Hosting & Deployment Implementasi:
* **Infrastruktur Server**: Cloud VPS (Virtual Private Server) berbasis Linux Ubuntu 22.04 / 24.04 LTS (misalnya DigitalOcean, Linode, atau penyedia lokal seperti IDCloudHost).
* **Web Server & Reverse Proxy**: **Nginx** dikonfigurasi dengan HTTP/2 dan Gzip/Brotli compression untuk mempercepat *loading time* aset mobile.
* **Sertifikat Keamanan**: SSL/TLS gratis otomatis menggunakan **Certbot (Let's Encrypt)** untuk mengamankan seluruh transmisi data transaksi dan enkripsi kata sandi.
* **Aplikasi & Runtime**: **PHP 8.2-FPM** yang dioptimalkan dengan OPcache aktif, Composer autoloader teroptimasi (`composer install --optimize-autoloader --no-dev`), dan cache konfigurasi Laravel (`php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache`).
* **Basis Data Produksi**: **MariaDB 10.11+** atau **SQLite 3** dengan mode WAL (*Write-Ahead Logging*) untuk performa konkurensi baca/tulis yang tinggi pada jam-jam sibuk restoran.
