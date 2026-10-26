<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>PARJO (Pancong & Burjo) - Self-Ordering</title>
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            DEFAULT: '#F24E1E',
                            hover: '#D84315',
                            light: '#FFF0EB',
                            border: '#F24E1E'
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <style>
        body {
            background-color: #E2E8F0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .app-container {
            max-width: 480px;
            min-height: 100vh;
            margin: 0 auto;
            background-color: #FFFFFF;
            position: relative;
            box-shadow: 0 0 30px rgba(0,0,0,0.08);
            display: flex;
            flex-direction: column;
        }
        html {
            scroll-behavior: smooth;
        }
    </style>
</head>
<body class="text-gray-900 antialiased selection:bg-brand selection:text-white">

<div class="app-container" x-data="esbApp()" x-init="initApp()">

    <!-- ======================================================== -->
    <!-- VIEW 1: CATALOG / MENU (ESB STYLE LIST) -->
    <!-- ======================================================== -->
    <div x-show="currentView === 'catalog'" class="flex-1 flex flex-col pb-28">
        
        <!-- Top Bar: Meja X (Soft Pink / Cream bar seperti ESB) -->
        <div class="bg-[#FFF1EB] text-gray-800 text-center py-2 text-xs font-semibold border-b border-orange-100 flex items-center justify-center space-x-2">
            <span>Meja <span x-text="tableNumber"></span></span>
            <button @click="editTablePrompt()" class="text-[10px] text-brand underline font-bold">Ubah</button>
        </div>

        <!-- Header Restaurant -->
        <header class="bg-white px-4 py-3.5 flex items-center justify-between border-b border-gray-100 sticky top-0 z-30">
            <div>
                <h1 class="text-base font-extrabold text-gray-900 tracking-tight">PARJO (Pancong & Burjo)</h1>
                <p class="text-[11px] text-gray-500 font-medium">Buka 24 Jam • Freshly Cooked</p>
            </div>
            <div class="flex items-center space-x-3">
                <button @click="showSearch = !showSearch" class="text-gray-700 hover:text-brand transition text-base p-1">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
                <button @click="openOriginalMenuModal()" class="text-xs bg-orange-50 text-brand font-bold px-2 py-1 rounded-md border border-orange-200">
                    Buku Menu
                </button>
            </div>
        </header>

        <!-- Search Input Bar (Drawer) -->
        <div x-show="showSearch" x-transition class="px-4 py-2 bg-gray-50 border-b border-gray-200">
            <div class="relative">
                <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" x-model="searchQuery" placeholder="Cari menu Parjo..." 
                       class="w-full pl-8 pr-8 py-2 bg-white border border-gray-300 rounded-lg text-xs font-medium focus:outline-none focus:border-brand">
                <button x-show="searchQuery.length > 0" @click="searchQuery = ''" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs">
                    <i class="fa-solid fa-circle-xmark"></i>
                </button>
            </div>
        </div>

        <!-- Sticky Categories Horizontal Tabs (Sesuai Screenshot ESB) -->
        <div class="sticky top-[57px] z-20 bg-white border-b border-gray-200 shadow-xs">
            <div class="flex items-center space-x-5 px-4 overflow-x-auto no-scrollbar scroll-smooth" id="categoryTabContainer">
                <template x-for="cat in categories" :key="cat.id">
                    <button @click="scrollToCategory(cat.id)"
                            :class="activeCategoryId === cat.id ? 'text-brand border-b-2 border-brand font-extrabold' : 'text-gray-700 border-b-2 border-transparent font-bold hover:text-gray-900'"
                            class="whitespace-nowrap py-3 text-xs tracking-wider uppercase transition flex-shrink-0 cursor-pointer">
                        <span x-text="cat.name"></span>
                    </button>
                </template>
            </div>
        </div>

        <!-- Menu Section List (Continuous Scroll per Category) -->
        <div class="px-4 py-2 flex-1 space-y-6">

            <template x-for="cat in categories" :key="cat.id">
                <div :id="'cat-section-' + cat.id" class="pt-4 scroll-mt-[115px]">
                    <!-- Category Header Banner (Sesuai Screenshot: Subtitle kecil abu + Title Besar Bold) -->
                    <div class="mb-3 border-b border-gray-100 pb-2 flex items-baseline justify-between">
                        <div>
                            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider" x-text="cat.sublabel"></div>
                            <h2 class="text-sm font-black text-gray-900 uppercase tracking-tight" x-text="cat.name"></h2>
                        </div>
                        <span class="text-[10px] text-gray-400 font-semibold" x-text="getCategoryProducts(cat.id).length + ' items'"></span>
                    </div>

                    <!-- List Items (Row Layout sesuai Screenshot ESB) -->
                    <div class="divide-y divide-gray-100">
                        <template x-for="item in getCategoryProducts(cat.id)" :key="item.id">
                            <div class="py-3.5 flex items-center justify-between space-x-3 group">
                                
                                <!-- Left: Thumbnail (Kotak Rounded dengan Placeholder / Gambar Asli) -->
                                <div @click="openProductModal(item)" class="w-16 h-16 rounded-xl bg-gray-50 flex-shrink-0 overflow-hidden border border-gray-100 relative cursor-pointer">
                                    <template x-if="item.image">
                                        <img :src="item.image" :alt="item.name" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                    </template>
                                    <template x-if="!item.image">
                                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                                            <i class="fa-solid fa-bowl-food text-xl"></i>
                                        </div>
                                    </template>
                                </div>

                                <!-- Middle: Nama Menu & Harga & Deskripsi Singkat -->
                                <div @click="openProductModal(item)" class="flex-1 min-w-0 cursor-pointer pr-1">
                                    <h3 class="font-extrabold text-xs text-gray-900 leading-tight group-hover:text-brand transition" x-text="item.name"></h3>
                                    
                                    <!-- Deskripsi Subtext jika ada -->
                                    <p x-show="item.desc" class="text-[11px] text-gray-400 line-clamp-1 mt-0.5" x-text="item.desc"></p>
                                    
                                    <div class="text-xs font-black text-gray-900 mt-1" x-text="formatRupiah(item.price)"></div>
                                </div>

                                <!-- Right: Tombol Tambah (Sesuai Screenshot: Border Oranye, Background Putih, Text Oranye) -->
                                <div class="flex-shrink-0">
                                    <template x-if="getItemQty(item.id) === 0">
                                        <button @click="openProductModal(item)" 
                                                class="px-4 py-1 rounded-lg border border-[#F24E1E] text-[#F24E1E] text-xs font-bold hover:bg-orange-50 active:scale-95 transition shadow-xs">
                                            Tambah
                                        </button>
                                    </template>
                                    <!-- Jika sudah ada di cart, tampilkan stepper mini -->
                                    <template x-if="getItemQty(item.id) > 0">
                                        <div class="flex items-center space-x-2 bg-orange-50 border border-orange-200 px-2 py-0.5 rounded-lg">
                                            <button @click="decreaseItemQuick(item.id)" class="text-brand font-black text-xs px-1 hover:text-red-600">-</button>
                                            <span class="text-xs font-black text-brand" x-text="getItemQty(item.id)"></span>
                                            <button @click="increaseItemQuick(item.id)" class="text-brand font-black text-xs px-1 hover:text-brand-hover">+</button>
                                        </div>
                                    </template>
                                </div>

                            </div>
                        </template>
                    </div>

                </div>
            </template>

            <!-- Search empty state -->
            <div x-show="searchQuery.length > 0 && totalFilteredCount === 0" class="py-12 text-center text-gray-400">
                <i class="fa-solid fa-magnifying-glass text-3xl mb-2 text-gray-300"></i>
                <p class="text-xs font-bold">Menu tidak ditemukan</p>
            </div>

        </div>

        <!-- Sticky Floating Cart Bar (Persis Screenshot ESB: Keranjang Putih di Kiri, Total & Tombol CHECK OUT Oranye) -->
        <div x-show="cartTotalItems > 0" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-8"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-8"
             class="fixed bottom-4 left-0 right-0 max-w-[480px] mx-auto px-4 z-40">
            
            <div class="bg-[#F24E1E] text-white rounded-xl shadow-2xl flex items-stretch overflow-hidden border border-orange-600">
                <!-- White Basket Box di Kiri -->
                <div @click="goToView('cart')" class="bg-white px-3.5 py-3 flex items-center justify-center cursor-pointer relative flex-shrink-0">
                    <i class="fa-solid fa-basket-shopping text-xl text-[#F24E1E]"></i>
                    <!-- Badge Counter Merah Oranye -->
                    <span class="absolute top-1.5 right-1.5 bg-[#F24E1E] text-white text-[9px] font-black w-4 h-4 rounded-full flex items-center justify-center shadow" x-text="cartTotalItems"></span>
                </div>

                <!-- Info Total di Tengah -->
                <div @click="goToView('cart')" class="flex-1 px-3 py-2 flex flex-col justify-center cursor-pointer">
                    <span class="text-[10px] text-white/90 font-medium leading-none">Total</span>
                    <span class="text-sm font-black text-white mt-0.5 leading-none" x-text="formatRupiah(cartSubtotal)"></span>
                </div>

                <!-- Tombol CHECK OUT (X) di Kanan -->
                <button @click="goToView('cart')" class="px-5 py-2 flex items-center justify-center font-extrabold text-xs uppercase tracking-wider text-white hover:bg-orange-600 transition active:scale-95">
                    <span>CHECK OUT (<span x-text="cartTotalItems"></span>)</span>
                </button>
            </div>

        </div>

    </div>

    <!-- ======================================================== -->
    <!-- VIEW 2: CART / REVIEW PESANAN -->
    <!-- ======================================================== -->
    <div x-show="currentView === 'cart'" class="flex-1 flex flex-col pb-28">
        
        <!-- Header -->
        <header class="sticky top-0 z-30 bg-white border-b border-gray-100 px-4 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <button @click="goToView('catalog')" class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-700 hover:text-brand transition">
                    <i class="fa-solid fa-arrow-left text-sm"></i>
                </button>
                <div>
                    <h1 class="text-sm font-extrabold text-gray-900">Pesanan Meja <span x-text="tableNumber"></span></h1>
                    <p class="text-[10px] text-gray-400">PARJO (Pancong & Burjo)</p>
                </div>
            </div>
            <button @click="clearCartConfirm()" class="text-xs text-red-500 font-bold hover:underline">
                Kosongkan
            </button>
        </header>

        <div class="p-4 space-y-4 flex-1">
            <!-- Dropdown Dine-in / Take-away -->
            <div class="bg-gray-50 p-3 rounded-xl border border-gray-200">
                <label class="block text-[11px] font-bold text-gray-500 mb-1 uppercase">Tipe Pemesanan</label>
                <select x-model="orderType" class="w-full bg-white border border-gray-300 text-xs font-bold rounded-lg px-3 py-2 text-gray-800">
                    <option value="dine-in">🍽️ Makan di tempat (Dine-in)</option>
                    <option value="take-away">🛍️ Bungkus (Take-away)</option>
                </select>
            </div>

            <!-- Add-on Topping Resmi Parjo -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-xs font-extrabold text-gray-700 uppercase tracking-wide">Topping Tambahan?</h3>
                    <span class="text-[10px] text-brand font-bold">Add-on Resmi</span>
                </div>
                <div class="flex space-x-2 overflow-x-auto no-scrollbar pb-1">
                    <template x-for="addon in addOnList" :key="addon.name">
                        <div class="bg-white border border-gray-200 rounded-lg p-2 flex-shrink-0 w-32 flex flex-col justify-between shadow-xs">
                            <span class="text-xs font-bold text-gray-800 truncate" x-text="addon.name"></span>
                            <span class="text-[11px] font-black text-brand" x-text="formatRupiah(addon.price)"></span>
                            <button @click="quickAddAddon(addon)" class="mt-1 bg-orange-50 text-brand text-[10px] font-bold py-1 rounded hover:bg-brand hover:text-white transition">
                                + Tambah
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- List Item di Cart -->
            <div class="bg-white border border-gray-200 rounded-xl p-3 shadow-xs">
                <h3 class="text-xs font-extrabold text-gray-400 uppercase tracking-wide mb-3">Item Dipesan (<span x-text="cartTotalItems"></span>)</h3>
                <div class="divide-y divide-gray-100">
                    <template x-for="item in cart" :key="item.cartId">
                        <div class="py-3 first:pt-0 last:pb-0 flex items-start justify-between">
                            <div class="flex-1 pr-2">
                                <h4 class="text-xs font-extrabold text-gray-900" x-text="item.name"></h4>
                                <div class="text-xs font-black text-brand mt-0.5" x-text="formatRupiah(item.price)"></div>
                                <div x-show="item.notes" class="text-[10px] text-gray-500 bg-gray-50 p-1 rounded mt-1">
                                    <i class="fa-solid fa-pencil text-brand mr-1"></i><span x-text="item.notes"></span>
                                </div>
                                <button @click="editItemNotes(item)" class="text-[10px] text-brand font-bold mt-1 inline-block">
                                    <span x-text="item.notes ? 'Ubah Catatan' : '+ Catatan Khusus'"></span>
                                </button>
                            </div>
                            <!-- Stepper -->
                            <div class="flex items-center space-x-2 bg-gray-50 border border-gray-200 px-2 py-1 rounded-lg">
                                <button @click="updateCartQty(item, -1)" class="text-xs text-gray-600 hover:text-red-500 font-bold px-1">
                                    <i class="fa-solid" :class="item.qty === 1 ? 'fa-trash-can text-red-500' : 'fa-minus'"></i>
                                </button>
                                <span class="text-xs font-black w-4 text-center" x-text="item.qty"></span>
                                <button @click="updateCartQty(item, 1)" class="text-xs text-brand font-bold px-1 hover:text-brand-hover">+</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Rincian Biaya -->
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-3.5 space-y-2 text-xs">
                <div class="flex justify-between text-gray-600">
                    <span>Subtotal</span>
                    <span class="font-bold text-gray-900" x-text="formatRupiah(cartSubtotal)"></span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>Biaya Layanan</span>
                    <span class="font-bold text-gray-900" x-text="formatRupiah(serviceFee)"></span>
                </div>
                <div x-show="discountAmount > 0" class="flex justify-between text-emerald-600 font-bold">
                    <span>Diskon (<span x-text="appliedVoucher.code"></span>)</span>
                    <span x-text="'-' + formatRupiah(discountAmount)"></span>
                </div>
                <div class="pt-2 border-t border-gray-200 flex justify-between font-black text-sm text-gray-900">
                    <span>Total Tagihan</span>
                    <span class="text-brand text-base" x-text="formatRupiah(finalTotal)"></span>
                </div>
            </div>
        </div>

        <!-- Sticky Bottom -->
        <div class="fixed bottom-0 left-0 right-0 max-w-[480px] mx-auto bg-white border-t border-gray-200 p-3 shadow-lg z-30">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[10px] text-gray-400 font-bold">Total Pembayaran</span>
                    <div class="text-base font-black text-brand" x-text="formatRupiah(finalTotal)"></div>
                </div>
                <button @click="goToView('checkout')" class="bg-[#F24E1E] hover:bg-orange-600 text-white font-extrabold text-xs px-6 py-2.5 rounded-xl transition shadow-md flex items-center space-x-1.5">
                    <span>Lanjut Pembayaran</span>
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                </button>
            </div>
        </div>

    </div>

    <!-- ======================================================== -->
    <!-- VIEW 3: FORM PEMBAYARAN (CHECKOUT) -->
    <!-- ======================================================== -->
    <div x-show="currentView === 'checkout'" class="flex-1 flex flex-col pb-28">
        
        <!-- Header -->
        <header class="sticky top-0 z-30 bg-white border-b border-gray-100 px-4 py-3 flex items-center space-x-3">
            <button @click="goToView('cart')" class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-700 hover:text-brand transition">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </button>
            <div>
                <h1 class="text-sm font-extrabold text-gray-900">Pembayaran</h1>
                <p class="text-[10px] text-gray-400">Konfirmasi Pesanan</p>
            </div>
        </header>

        <div class="p-4 space-y-4 flex-1">
            <!-- Informasi Pemesan -->
            <div class="bg-white border border-gray-200 rounded-xl p-3.5 space-y-3">
                <h3 class="text-xs font-black text-gray-700 uppercase tracking-wide">Informasi Pemesan</h3>
                <div>
                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Nama Lengkap *</label>
                    <input type="text" x-model="customer.name" placeholder="Misal: Dimas"
                           class="w-full bg-gray-50 border border-gray-300 text-xs font-semibold rounded-lg px-3 py-2 focus:outline-none focus:border-brand">
                    <span x-show="formErrors.name" class="text-[10px] text-red-500 font-bold mt-0.5 block" x-text="formErrors.name"></span>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Nomor WhatsApp *</label>
                    <input type="tel" x-model="customer.phone" placeholder="08123456789"
                           class="w-full bg-gray-50 border border-gray-300 text-xs font-semibold rounded-lg px-3 py-2 focus:outline-none focus:border-brand">
                    <span x-show="formErrors.phone" class="text-[10px] text-red-500 font-bold mt-0.5 block" x-text="formErrors.phone"></span>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Nomor Meja</label>
                    <input type="number" x-model="tableNumber"
                           class="w-20 bg-gray-50 border border-gray-300 text-xs font-black text-center rounded-lg px-2 py-1.5">
                </div>
            </div>

            <!-- Voucher -->
            <div class="bg-white border border-gray-200 rounded-xl p-3 flex items-center justify-between">
                <div>
                    <div class="text-xs font-black text-gray-800">Promo & Voucher</div>
                    <div class="text-[10px] text-gray-400" x-text="appliedVoucher ? appliedVoucher.code + ' terpakai!' : 'Gunakan voucher hemat'"></div>
                </div>
                <button @click="openVoucherModal()" class="text-xs font-bold px-3 py-1 rounded-lg border border-brand text-brand hover:bg-brand hover:text-white transition">
                    <span x-text="appliedVoucher ? 'Hapus' : 'Pilih'"></span>
                </button>
            </div>

            <!-- Metode Pembayaran -->
            <div class="bg-white border border-gray-200 rounded-xl p-3 space-y-2">
                <h3 class="text-xs font-black text-gray-700 uppercase tracking-wide">Metode Pembayaran</h3>
                <label class="flex items-center justify-between p-2.5 rounded-lg border border-gray-200 cursor-pointer" :class="paymentMethod === 'qris' ? 'border-brand bg-orange-50/50' : ''">
                    <div class="flex items-center space-x-2">
                        <input type="radio" name="pay" value="qris" x-model="paymentMethod" class="text-brand">
                        <span class="text-xs font-bold text-gray-800">QRIS Instant (GoPay/Shopee/BCA/Dana)</span>
                    </div>
                </label>
                <label class="flex items-center justify-between p-2.5 rounded-lg border border-gray-200 cursor-pointer" :class="paymentMethod === 'cashier' ? 'border-brand bg-orange-50/50' : ''">
                    <div class="flex items-center space-x-2">
                        <input type="radio" name="pay" value="cashier" x-model="paymentMethod" class="text-brand">
                        <span class="text-xs font-bold text-gray-800">Bayar di Kasir (Tunai)</span>
                    </div>
                </label>
            </div>

            <!-- Ringkasan Total -->
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-3 text-xs space-y-1.5">
                <div class="flex justify-between text-gray-600">
                    <span>Subtotal</span>
                    <span x-text="formatRupiah(cartSubtotal)"></span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>Layanan</span>
                    <span x-text="formatRupiah(serviceFee)"></span>
                </div>
                <div x-show="discountAmount > 0" class="flex justify-between text-emerald-600 font-bold">
                    <span>Diskon</span>
                    <span x-text="'-' + formatRupiah(discountAmount)"></span>
                </div>
                <div class="pt-2 border-t border-gray-200 flex justify-between font-black text-sm text-gray-900">
                    <span>Total Tagihan Akhir</span>
                    <span class="text-brand" x-text="formatRupiah(finalTotal)"></span>
                </div>
            </div>
        </div>

        <!-- Sticky Bottom -->
        <div class="fixed bottom-0 left-0 right-0 max-w-[480px] mx-auto bg-white border-t border-gray-200 p-3 shadow-lg z-30">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[10px] text-gray-400 font-bold">Total Tagihan</span>
                    <div class="text-base font-black text-brand" x-text="formatRupiah(finalTotal)"></div>
                </div>
                <button @click="processPayment()" class="bg-[#F24E1E] hover:bg-orange-600 text-white font-extrabold text-xs px-8 py-2.5 rounded-xl transition shadow-md">
                    Bayar
                </button>
            </div>
        </div>

    </div>

    <!-- ======================================================== -->
    <!-- MODAL DETAIL PRODUK -->
    <!-- ======================================================== -->
    <div x-show="activeProductModal" 
         x-transition class="fixed inset-0 z-50 flex items-end justify-center bg-black/60 backdrop-blur-xs">
        
        <div @click.away="closeProductModal()"
             class="w-full max-w-[480px] bg-white rounded-t-3xl overflow-hidden max-h-[90vh] flex flex-col shadow-2xl relative">
            
            <button @click="closeProductModal()" class="absolute top-3 right-3 z-20 w-8 h-8 rounded-full bg-black/50 text-white flex items-center justify-center hover:bg-black/70 transition">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>

            <div class="overflow-y-auto flex-1 pb-24">
                <div class="w-full h-52 bg-gray-100 relative">
                    <img :src="selectedProduct?.image" :alt="selectedProduct?.name" class="w-full h-full object-cover">
                </div>

                <div class="p-4 space-y-3">
                    <div>
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider" x-text="selectedProduct?.catName"></div>
                        <h2 class="text-sm font-black text-gray-900 leading-snug mt-0.5" x-text="selectedProduct?.name"></h2>
                        <div class="text-base font-black text-brand mt-1" x-text="formatRupiah(selectedProduct?.price || 0)"></div>
                        <p class="text-xs text-gray-500 mt-1.5 leading-relaxed" x-text="selectedProduct?.desc"></p>
                    </div>

                    <div class="pt-2 border-t border-gray-100">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Catatan Pesanan (Opsional)</label>
                        <input type="text" x-model="modalNotes" placeholder="Contoh: Pedas sedang, tanpa sawi, es dikit..."
                               class="w-full bg-gray-50 border border-gray-300 text-xs rounded-lg px-3 py-2 focus:outline-none focus:border-brand">
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                        <span class="text-xs font-black text-gray-700">Jumlah</span>
                        <div class="flex items-center space-x-3 bg-gray-50 border border-gray-200 px-3 py-1 rounded-xl">
                            <button @click="if(modalQty > 1) modalQty--" class="text-sm font-bold text-gray-600 hover:text-brand px-1">-</button>
                            <span class="text-sm font-black w-5 text-center" x-text="modalQty"></span>
                            <button @click="modalQty++" class="text-sm font-bold text-brand px-1">+</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sticky Bottom Modal Button -->
            <div class="absolute bottom-0 left-0 right-0 p-3 bg-white border-t border-gray-200 z-20">
                <button @click="addModalToCart()" class="w-full bg-[#F24E1E] hover:bg-orange-600 text-white font-extrabold text-xs py-3 rounded-xl transition shadow-md flex items-center justify-between px-4">
                    <span>Tambah Pesanan</span>
                    <span x-text="formatRupiah((selectedProduct?.price || 0) * modalQty)"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL BUKU MENU ASLI -->
    <!-- ======================================================== -->
    <div x-show="activeMenuBookModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-3 bg-black/80 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-md w-full max-h-[90vh] flex flex-col overflow-hidden shadow-2xl">
            <div class="p-3 border-b border-gray-200 flex items-center justify-between bg-white">
                <h3 class="font-extrabold text-xs text-gray-900">Buku Menu Asli Dokumen Parjo (Hal <span x-text="currentMenuPage"></span>/3)</h3>
                <button @click="activeMenuBookModal = false" class="text-gray-500 hover:text-gray-800 text-sm">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="overflow-y-auto flex-1 bg-black p-1 flex items-center justify-center">
                <img :src="'/images/menu/menu_page_' + currentMenuPage + '.jpg'" class="w-full h-auto object-contain rounded">
            </div>
            <div class="p-2.5 bg-white border-t border-gray-200 flex justify-between items-center text-xs">
                <div class="flex space-x-1.5">
                    <button @click="currentMenuPage = 1" :class="currentMenuPage === 1 ? 'bg-brand text-white' : 'bg-gray-100'" class="px-2.5 py-1 rounded font-bold text-[11px]">Hal 1</button>
                    <button @click="currentMenuPage = 2" :class="currentMenuPage === 2 ? 'bg-brand text-white' : 'bg-gray-100'" class="px-2.5 py-1 rounded font-bold text-[11px]">Hal 2</button>
                    <button @click="currentMenuPage = 3" :class="currentMenuPage === 3 ? 'bg-brand text-white' : 'bg-gray-100'" class="px-2.5 py-1 rounded font-bold text-[11px]">Hal 3</button>
                </div>
                <button @click="activeMenuBookModal = false" class="text-xs font-bold text-gray-500">Tutup</button>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL VOUCHER -->
    <!-- ======================================================== -->
    <div x-show="activeVoucherModal" x-transition class="fixed inset-0 z-50 flex items-end justify-center bg-black/60 backdrop-blur-xs">
        <div @click.away="activeVoucherModal = false" class="w-full max-w-[480px] bg-white rounded-t-3xl overflow-hidden max-h-[80vh] flex flex-col p-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-100 pb-2.5">
                <h3 class="font-black text-xs text-gray-900 uppercase">Pilih Kupon Hemat</h3>
                <button @click="activeVoucherModal = false" class="text-gray-400"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="py-3 space-y-2 overflow-y-auto flex-1">
                <template x-for="v in availableVouchers" :key="v.code">
                    <div @click="applyVoucher(v)" class="border rounded-xl p-3 cursor-pointer flex items-center justify-between"
                         :class="appliedVoucher?.code === v.code ? 'border-brand bg-orange-50/50' : 'border-gray-200'">
                        <div>
                            <div class="text-xs font-black text-brand" x-text="v.code"></div>
                            <div class="text-[11px] font-bold text-gray-800" x-text="v.title"></div>
                            <div class="text-[10px] text-gray-500" x-text="v.desc"></div>
                        </div>
                        <i :class="appliedVoucher?.code === v.code ? 'fa-solid fa-circle-check text-brand' : 'fa-regular fa-circle text-gray-300'"></i>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL SUCCESS -->
    <!-- ======================================================== -->
    <div x-show="showSuccessModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <div class="bg-white rounded-2xl p-5 w-full max-w-sm text-center shadow-2xl space-y-3">
            <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto text-2xl">
                <i class="fa-solid fa-check"></i>
            </div>
            <h3 class="text-base font-black text-gray-900">Pesanan Diterima!</h3>
            <p class="text-xs text-gray-500">Pesanan Meja <span class="font-bold text-gray-800" x-text="tableNumber"></span> sedang diproses dapur.</p>
            <div class="bg-gray-50 p-3 rounded-lg text-left text-xs space-y-1">
                <div class="flex justify-between text-gray-500"><span>Order ID:</span><span class="font-mono font-bold text-gray-800" x-text="lastOrderId"></span></div>
                <div class="flex justify-between text-gray-500"><span>Nama:</span><span class="font-bold text-gray-800" x-text="customer.name"></span></div>
                <div class="flex justify-between text-gray-500"><span>Total:</span><span class="font-black text-brand" x-text="formatRupiah(finalTotal)"></span></div>
            </div>
            <button @click="resetAndBackToHome()" class="w-full bg-[#F24E1E] text-white font-extrabold py-2.5 rounded-xl text-xs uppercase tracking-wide">
                Pesan Menu Lain
            </button>
        </div>
    </div>

</div>

<!-- Script Logika -->
<script>
function esbApp() {
    return {
        currentView: 'catalog',
        showSearch: false,
        searchQuery: '',
        tableNumber: 3,
        activeCategoryId: 'signature',
        orderType: 'dine-in',
        paymentMethod: 'qris',
        serviceFee: 1000,

        customer: { name: '', phone: '', email: '' },
        formErrors: {},

        activeProductModal: false,
        selectedProduct: null,
        modalQty: 1,
        modalNotes: '',

        activeMenuBookModal: false,
        currentMenuPage: 1,
        activeVoucherModal: false,
        appliedVoucher: null,
        showSuccessModal: false,
        lastOrderId: '',

        // Kategori Tabs Sesuai Dokumen Menu Parjo
        categories: [
            { id: 'signature', name: 'SIGNATURE', sublabel: 'MENU ANDALAN' },
            { id: 'burjo', name: 'BURJO & NASI', sublabel: 'MAKANAN' },
            { id: 'ayam', name: 'AYAM', sublabel: 'MAKANAN' },
            { id: 'telor', name: 'TELOR', sublabel: 'MAKANAN' },
            { id: 'indomie', name: 'INDOMIE', sublabel: 'MAKANAN' },
            { id: 'kulit', name: 'KULIT', sublabel: 'MAKANAN' },
            { id: 'snack', name: 'SNACK', sublabel: 'CAMILAN' },
            { id: 'pancong', name: 'PANCONG', sublabel: 'DESSERT' },
            { id: 'coffee', name: 'COFFEE & BEER', sublabel: 'MINUMAN' },
            { id: 'tea', name: 'TEA', sublabel: 'MINUMAN' },
            { id: 'sachet', name: 'SACHET & BLENDED', sublabel: 'MINUMAN' }
        ],

        // Add-On List Resmi PARJO
        addOnList: [
            { name: 'Kol Goreng', price: 3000 },
            { name: 'Kornet', price: 7000 },
            { name: 'Sosis', price: 5000 },
            { name: 'Bakso', price: 5000 },
            { name: 'Telor', price: 5000 },
            { name: 'Nasi', price: 5000 },
            { name: 'Ayam', price: 6000 }
        ],

        // Seluruh Menu PARJO (Halaman 1, 2, 3)
        products: [
            // SIGNATURE
            { id: 101, category: 'signature', catName: 'SIGNATURE', name: 'Nasi Ayam Kuah Laos', price: 16000, desc: 'Ayam bumbu khas kuah laos rempah gurih disajikan dengan nasi hangat dan lalapan.', image: 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=400&q=80' },
            { id: 102, category: 'signature', catName: 'SIGNATURE', name: 'Nasi Ayam Parjo Signature', price: 20000, desc: 'Menu andalan Parjo dengan racikan bumbu rahasia premium lengkap kerupuk & serundeng.', image: 'https://images.unsplash.com/photo-1541832676-9b763b0239ab?auto=format&fit=crop&w=400&q=80' },
            { id: 103, category: 'signature', catName: 'SIGNATURE', name: 'Butter Rice Smoked Beef', price: 16000, desc: 'Nasi mentega wangi dipadu potongan daging asap gurih empuk bercitarasa creamy.', image: 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?auto=format&fit=crop&w=400&q=80' },
            { id: 104, category: 'signature', catName: 'SIGNATURE', name: 'Nasi Ayam Saus Karamel', price: 16000, desc: 'Ayam goreng crispy berselimut glaze saus karamel manis gurih menggoda selera.', image: 'https://images.unsplash.com/photo-1569058242253-92a9c755a0ec?auto=format&fit=crop&w=400&q=80' },
            { id: 105, category: 'signature', catName: 'SIGNATURE', name: 'Nasi Ayam Gongso Kuah', price: 18000, desc: 'Ayam suwir bumbu gongso kecap gurih dengan kuah pekat kaya rempah dan sayuran segar.', image: 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=400&q=80' },

            // BURJO & NASI
            { id: 201, category: 'burjo', catName: 'BURJO', name: 'Nasi Gila', price: 15000, desc: 'Nasi putih dengan topping tumisan sosis, bakso, dan telur orak-arik bumbu gurih.', image: 'https://images.unsplash.com/photo-1512058564366-18510be2db19?auto=format&fit=crop&w=400&q=80' },
            { id: 202, category: 'burjo', catName: 'BURJO', name: 'Nasi Goreng Gila', price: 20000, desc: 'Nasi goreng racikan Parjo disiram topping sosis bakso telur melimpah.', image: 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?auto=format&fit=crop&w=400&q=80' },
            { id: 203, category: 'burjo', catName: 'BURJO', name: 'Mie Tek-Tek', price: 16000, desc: 'Mie goreng tek-tek bumbu tradisional dengan sayuran kubis, sawi, dan telur gurih.', image: 'https://images.unsplash.com/photo-1585032226651-759b368d7246?auto=format&fit=crop&w=400&q=80' },
            { id: 204, category: 'burjo', catName: 'BURJO', name: 'Magelangan', price: 16000, desc: 'Perpaduan legendaris nasi dan mie dimasak bersama bumbu racikan khas Burjo.', image: 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?auto=format&fit=crop&w=400&q=80' },
            { id: 205, category: 'burjo', catName: 'BURJO', name: 'Mie Dok-Dok', price: 15000, desc: 'Mie kuah nyemek bumbu pedas manis gurih khas Jogja dengan serabut telur nikmat.', image: 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=400&q=80' },
            { id: 206, category: 'burjo', catName: 'BURJO', name: 'Nasi Orak Arik Telor', price: 12000, desc: 'Nasi hangat disajikan dengan orak-arik telur gurih, kol segar, dan kecap manis.', image: 'https://images.unsplash.com/photo-1525351484163-7529414344d8?auto=format&fit=crop&w=400&q=80' },
            { id: 207, category: 'burjo', catName: 'BURJO', name: 'Nasi Omelet', price: 13000, desc: 'Omelet telur tebal lembut berpadu nasi putih hangat dan saus sambal.', image: 'https://images.unsplash.com/photo-1510693206972-df098062cb71?auto=format&fit=crop&w=400&q=80' },
            { id: 208, category: 'burjo', catName: 'BURJO', name: 'Nasi Goreng', price: 13000, desc: 'Nasi goreng klasik bumbu bawang khas warung burjo yang harum dan sedap.', image: 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?auto=format&fit=crop&w=400&q=80' },
            { id: 209, category: 'burjo', catName: 'BURJO', name: 'Nasi Goreng Hongkong', price: 14000, desc: 'Nasi goreng oriental warna putih gurih tanpa kecap hitam dengan aroma smokey.', image: 'https://images.unsplash.com/photo-1512058564366-18510be2db19?auto=format&fit=crop&w=400&q=80' },

            // AYAM
            { id: 301, category: 'ayam', catName: 'AYAM', name: 'Nasi Ayam Bali', price: 15000, desc: 'Nasi dengan olahan ayam bumbu base genep Bali pedas gurih aromatik.', image: 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=400&q=80' },
            { id: 302, category: 'ayam', catName: 'AYAM', name: 'Nasi Ayam Balap', price: 15000, desc: 'Ayam suwir gurih pedas khas Lombok dengan taburan kedelai goreng renyah.', image: 'https://images.unsplash.com/photo-1541832676-9b763b0239ab?auto=format&fit=crop&w=400&q=80' },
            { id: 303, category: 'ayam', catName: 'AYAM', name: 'Nasi Ayam Balap Crispy', price: 16000, desc: 'Versi ayam fillet crispy renyah disiram racikan bumbu ayam balap pedas.', image: 'https://images.unsplash.com/photo-1569058242253-92a9c755a0ec?auto=format&fit=crop&w=400&q=80' },
            { id: 304, category: 'ayam', catName: 'AYAM', name: 'Nasi Ayam Blackpepper', price: 16000, desc: 'Potongan ayam empuk dengan saus lada hitam kental pedas hangat.', image: 'https://images.unsplash.com/photo-1588166524941-3bf61a9c41db?auto=format&fit=crop&w=400&q=80' },
            { id: 305, category: 'ayam', catName: 'AYAM', name: 'Nasi Ayam Salted Egg', price: 18000, desc: 'Ayam goreng crispy dibalut saus telur asin creamy gurih harum daun kari.', image: 'https://images.unsplash.com/photo-1527477378698-4c8d50f83733?auto=format&fit=crop&w=400&q=80' },
            { id: 306, category: 'ayam', catName: 'AYAM', name: 'Nasi Ayam Barbecue', price: 16000, desc: 'Ayam panggang bumbu BBQ manis gurih dengan aroma asap khas.', image: 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=400&q=80' },
            { id: 307, category: 'ayam', catName: 'AYAM', name: 'Nasi Ayam Mentega', price: 16000, desc: 'Ayam tumis saus mentega gurih manis dengan irisan bawang bombay.', image: 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=400&q=80' },
            { id: 308, category: 'ayam', catName: 'AYAM', name: 'Nasi Ayam Cabe Garam', price: 17000, desc: 'Ayam fillet crispy renyah ditumis irisan cabai rawit merah dan bawang putih gurih.', image: 'https://images.unsplash.com/photo-1569058242253-92a9c755a0ec?auto=format&fit=crop&w=400&q=80' },
            { id: 309, category: 'ayam', catName: 'AYAM', name: 'Nasi Ayam Bali Crispy', price: 16000, desc: 'Ayam krispi renyah disajikan dengan sambal matah & bumbu bali gurih fresh.', image: 'https://images.unsplash.com/photo-1541832676-9b763b0239ab?auto=format&fit=crop&w=400&q=80' },
            { id: 310, category: 'ayam', catName: 'AYAM', name: 'Nasi Ayam Asam Manis', price: 15000, desc: 'Ayam goreng tepung renyah berpadu saus asam manis segar.', image: 'https://images.unsplash.com/photo-1525351484163-7529414344d8?auto=format&fit=crop&w=400&q=80' },
            { id: 311, category: 'ayam', catName: 'AYAM', name: 'Nasi Ayam Maranggi', price: 16000, desc: 'Ayam bumbu maranggi manis gurih ketumbar rempah dengan sambal tomat segar.', image: 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?auto=format&fit=crop&w=400&q=80' },

            // TELOR
            { id: 401, category: 'telor', catName: 'TELOR', name: 'Nasi Telor Pontianak', price: 15000, desc: 'Telur ceplok mata sapi disiram saus kecap bawang putih khas Pontianak.', image: 'https://images.unsplash.com/photo-1525351484163-7529414344d8?auto=format&fit=crop&w=400&q=80' },
            { id: 402, category: 'telor', catName: 'TELOR', name: 'Nasi Telor Kribo', price: 15000, desc: 'Telur dadar bersarang super mekar krispi garing gurih di setiap gigitan.', image: 'https://images.unsplash.com/photo-1510693206972-df098062cb71?auto=format&fit=crop&w=400&q=80' },

            // INDOMIE
            { id: 501, category: 'indomie', catName: 'INDOMIE', name: 'Indomie Bangladesh', price: 15000, desc: 'Indomie bumbu rempah kari pekat khas Aceh Medan yang creamy dan kaya rempah.', image: 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=400&q=80' },
            { id: 502, category: 'indomie', catName: 'INDOMIE', name: 'Indomie Goreng', price: 8000, desc: 'Indomie goreng legendaris dimasak pas kenyal dengan bawang goreng renyah.', image: 'https://images.unsplash.com/photo-1585032226651-759b368d7246?auto=format&fit=crop&w=400&q=80' },
            { id: 503, category: 'indomie', catName: 'INDOMIE', name: 'Indomie Rebus', price: 8000, desc: 'Indomie kuah hangat rasa ayam bawang klasik dengan sawi hijau segar.', image: 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=400&q=80' },
            { id: 504, category: 'indomie', catName: 'INDOMIE', name: 'Indomie Chili Oil', price: 10000, desc: 'Indomie dipadu racikan chili oil pedas gurih wangi bawang aromatik.', image: 'https://images.unsplash.com/photo-1585032226651-759b368d7246?auto=format&fit=crop&w=400&q=80' },
            { id: 505, category: 'indomie', catName: 'INDOMIE', name: 'Indomie Soto', price: 8000, desc: 'Indomie kuah rasa soto dengan aroma koya dan perasan jeruk nipis gurih.', image: 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=400&q=80' },
            { id: 506, category: 'indomie', catName: 'INDOMIE', name: 'Mie Sedaap Kari Spesial', price: 8000, desc: 'Mie kuah kari kental gurih nikmat dengan bumbu kari pekat.', image: 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=400&q=80' },
            { id: 507, category: 'indomie', catName: 'INDOMIE', name: 'Indomie Rendang', price: 8000, desc: 'Indomie goreng bumbu rendang Padang kaya rempah yang nikmat.', image: 'https://images.unsplash.com/photo-1585032226651-759b368d7246?auto=format&fit=crop&w=400&q=80' },

            // KULIT
            { id: 601, category: 'kulit', catName: 'KULIT', name: 'Nasi Kulit Sambal Matah', price: 14000, desc: 'Kulit ayam goreng super crispy disajikan sambal matah kecombrang fresh & nasi.', image: 'https://images.unsplash.com/photo-1562967914-608f82629710?auto=format&fit=crop&w=400&q=80' },
            { id: 602, category: 'kulit', catName: 'KULIT', name: 'Nasi Kulit Cabe Garam', price: 15000, desc: 'Kulit ayam renyah ditumis bumbu cabai garam gurih pedas harum bawang.', image: 'https://images.unsplash.com/photo-1562967914-608f82629710?auto=format&fit=crop&w=400&q=80' },
            { id: 603, category: 'kulit', catName: 'KULIT', name: 'Nasi Kulit Gongso', price: 16000, desc: 'Kulit ayam dimasak bumbu gongso pedas manis dengan aroma kecap karamel.', image: 'https://images.unsplash.com/photo-1569058242253-92a9c755a0ec?auto=format&fit=crop&w=400&q=80' },

            // SNACK
            { id: 701, category: 'snack', catName: 'SNACK', name: 'Mendoan', price: 12000, desc: 'Tempe mendoan lembut digoreng tepung daun bawang dicocol kecap pedas.', image: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=400&q=80' },
            { id: 702, category: 'snack', catName: 'SNACK', name: 'Chicken Wings', price: 18000, desc: 'Sayap ayam crispy berbumbu gurih garing di luar juicy di dalam.', image: 'https://images.unsplash.com/photo-1527477378698-4c8d50f83733?auto=format&fit=crop&w=400&q=80' },
            { id: 703, category: 'snack', catName: 'SNACK', name: 'French Fries', price: 14000, desc: 'Kentang goreng garing bertabur seasoning gurih dengan saus cocolan.', image: 'https://images.unsplash.com/photo-1576107232684-1279f3908594?auto=format&fit=crop&w=400&q=80' },
            { id: 704, category: 'snack', catName: 'SNACK', name: 'Mix Platter', price: 16000, desc: 'Kombinasi sosis, nugget, dan kentang goreng cocok untuk camilan bareng.', image: 'https://images.unsplash.com/photo-1541592106381-b31e9677c0e5?auto=format&fit=crop&w=400&q=80' },
            { id: 705, category: 'snack', catName: 'SNACK', name: 'Cheesy Fries', price: 17000, desc: 'Kentang goreng renyah disiram saus keju cheddar lumer yang gurih creamy.', image: 'https://images.unsplash.com/photo-1586190848861-99aa4a171e90?auto=format&fit=crop&w=400&q=80' },
            { id: 706, category: 'snack', catName: 'SNACK', name: 'Tahu Cabe Garam', price: 15000, desc: 'Tahu sutra crispy dipadu tumisan cabe rawit merah dan bawang putih renyah.', image: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=400&q=80' },

            // PANCONG
            { id: 801, category: 'pancong', catName: 'PANCONG', name: 'Pancong Original', price: 7000, desc: 'Kue pancong lumer adonan wangi mentega khas Parjo bisa ditambah topping pilihan.', image: 'https://images.unsplash.com/photo-1587314168485-3236d6710814?auto=format&fit=crop&w=400&q=80' },
            { id: 802, category: 'pancong', catName: 'PANCONG', name: 'Pancong Chocomaltine', price: 13000, desc: 'Kue pancong lumer hangat dengan selai chocomaltine crunchy cokelat premium.', image: 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=400&q=80' },
            { id: 803, category: 'pancong', catName: 'PANCONG', name: 'Pancong Green Tea', price: 12000, desc: 'Pancong lembut dengan lelehan selai green tea matcha manis aromatik.', image: 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=400&q=80' },
            { id: 804, category: 'pancong', catName: 'PANCONG', name: 'Pancong Biscoff', price: 14000, desc: 'Pancong lumer dengan lumuran selai lotus biscoff karamel rempah.', image: 'https://images.unsplash.com/photo-1587314168485-3236d6710814?auto=format&fit=crop&w=400&q=80' },
            { id: 805, category: 'pancong', catName: 'PANCONG', name: 'Pancong Tiramisu', price: 13000, desc: 'Kue pancong lumer dipadu saus tiramisu khas kopi lembut Italia.', image: 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=400&q=80' },
            { id: 806, category: 'pancong', catName: 'PANCONG', name: 'Pancong Nutella', price: 14000, desc: 'Kue pancong lumer dengan selai hazelnut cokelat Nutella melimpah.', image: 'https://images.unsplash.com/photo-1587314168485-3236d6710814?auto=format&fit=crop&w=400&q=80' },
            { id: 807, category: 'pancong', catName: 'PANCONG', name: 'Pancong Ice Cream', price: 14000, desc: 'Pancong hangat lumer disajikan dengan satu scoop besar es krim vanilla.', image: 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=400&q=80' },

            // COFFEE & BEER
            { id: 901, category: 'coffee', catName: 'COFFEE & BEER', name: 'Butter Beer Parjo', price: 13000, desc: 'Minuman andalan Parjo! Paduan soda sarsaparila segar dengan foam butter cream lembut.', image: 'https://images.unsplash.com/photo-1544145945-f90425340c7e?auto=format&fit=crop&w=400&q=80' },
            { id: 902, category: 'coffee', catName: 'COFFEE & BEER', name: 'Coffee Beer', price: 16000, desc: 'Minuman kopi bersoda legendaris Jawa Timur yang segar tanpa alkohol (halal).', image: 'https://images.unsplash.com/photo-1544145945-f90425340c7e?auto=format&fit=crop&w=400&q=80' },
            { id: 903, category: 'coffee', catName: 'COFFEE & BEER', name: 'Sarsaparila', price: 16000, desc: 'Soda sarsaparila klasik dingin yang menyegarkan dahaga saat nugas.', image: 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?auto=format&fit=crop&w=400&q=80' },
            { id: 904, category: 'coffee', catName: 'COFFEE & BEER', name: 'Kopi Susu Parjo', price: 13000, desc: 'Espresso blend dipadu susu kental manis dan susu segar pas tidak bikin eneg.', image: 'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=400&q=80' },
            { id: 905, category: 'coffee', catName: 'COFFEE & BEER', name: 'Kopi Susu Sea Salted', price: 14000, desc: 'Kopi susu creamy berpadu krim sea salt gurih lembut di lapisan atas.', image: 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=400&q=80' },
            { id: 906, category: 'coffee', catName: 'COFFEE & BEER', name: 'Kopi Susu Alpenliebe', price: 15000, desc: 'Kopi susu dengan sirup karamel Alpenliebe manis legit dan harum.', image: 'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=400&q=80' },
            { id: 907, category: 'coffee', catName: 'COFFEE & BEER', name: 'Coffee Milo', price: 14000, desc: 'Kombinasi mantap bubuk cokelat malt Milo berpadu shot kopi espresso segar.', image: 'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=400&q=80' },
            { id: 908, category: 'coffee', catName: 'COFFEE & BEER', name: 'Kopi Susu Butterscotch', price: 14000, desc: 'Kopi susu dengan sentuhan aroma manis gurih sirup butterscotch.', image: 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=400&q=80' },

            // TEA
            { id: 1001, category: 'tea', catName: 'TEA', name: 'Lychee Tea', price: 10000, desc: 'Teh melati seduh segar dengan rasa buah leci manis menyegarkan.', image: 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=400&q=80' },
            { id: 1002, category: 'tea', catName: 'TEA', name: 'Strawberry Tea', price: 9000, desc: 'Es teh dengan sirup stroberi segar dan rasa asam manis pas.', image: 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=400&q=80' },
            { id: 1003, category: 'tea', catName: 'TEA', name: 'Teh Gentong', price: 6000, desc: 'Teh seduh gentong tradisional wangi melati pekat ginastel manis alami.', image: 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=400&q=80' },
            { id: 1004, category: 'tea', catName: 'TEA', name: 'Lemon Tea', price: 8000, desc: 'Teh segar dengan perasan jeruk lemon asli kaya vitamin C.', image: 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=400&q=80' },
            { id: 1005, category: 'tea', catName: 'TEA', name: 'Wintermelon Tea', price: 8000, desc: 'Teh labu kundur manis dingin pereda panas dalam.', image: 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=400&q=80' },
            { id: 1006, category: 'tea', catName: 'TEA', name: 'Wintermelon Milk Tea', price: 10000, desc: 'Milk tea berpadu aroma manis harum teh kundur wintermelon.', image: 'https://images.unsplash.com/photo-1558857563-b37fe878c764?auto=format&fit=crop&w=400&q=80' },
            { id: 1007, category: 'tea', catName: 'TEA', name: 'Honey Lemon Tea', price: 8000, desc: 'Seduhan teh lemon dipadu madu murni alami segar melegakan tenggorokan.', image: 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=400&q=80' },
            { id: 1008, category: 'tea', catName: 'TEA', name: 'Butterscotch Milk Tea', price: 13000, desc: 'Susu teh berpadu sirup butterscotch creamy gurih manis.', image: 'https://images.unsplash.com/photo-1558857563-b37fe878c764?auto=format&fit=crop&w=400&q=80' },
            { id: 1009, category: 'tea', catName: 'TEA', name: 'Strawberry Milk Tea', price: 10000, desc: 'Minuman stroberi segar berpadu susu lembut creamy yang nagih.', image: 'https://images.unsplash.com/photo-1558857563-b37fe878c764?auto=format&fit=crop&w=400&q=80' },

            // SACHET & BLENDED
            { id: 1101, category: 'sachet', catName: 'SACHET', name: 'Milo Dino', price: 13000, desc: 'Es susu Milo dingin dengan taburan bubuk cokelat Milo pekat menggunung.', image: 'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=400&q=80' },
            { id: 1102, category: 'sachet', catName: 'SACHET', name: 'Chocolatos Coklat / Matcha', price: 8000, desc: 'Minuman cokelat kental Chocolatos nikmat disajikan dingin atau hangat.', image: 'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=400&q=80' },
            { id: 1103, category: 'sachet', catName: 'SACHET', name: 'Nutrisari All Variant', price: 6000, desc: 'Es Nutrisari segar aneka varian rasa jeruk peras, mangga, atau blewah.', image: 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?auto=format&fit=crop&w=400&q=80' },
            { id: 1104, category: 'sachet', catName: 'SACHET', name: 'Teh Tarik', price: 7000, desc: 'Teh tarik khas melayu dengan busa lembut dan rasa manis creamy.', image: 'https://images.unsplash.com/photo-1558857563-b37fe878c764?auto=format&fit=crop&w=400&q=80' },
            { id: 1105, category: 'sachet', catName: 'SACHET', name: 'Dancow Putih / Coklat', price: 8000, desc: 'Susu bubuk Dancow full cream diseduh manis kental hangat atau es dingin.', image: 'https://images.unsplash.com/photo-1558857563-b37fe878c764?auto=format&fit=crop&w=400&q=80' },
            { id: 1106, category: 'sachet', catName: 'SACHET', name: 'Josua (Extra Joss Susu)', price: 9000, desc: 'Minuman stamina Extra Joss disatukan dengan susu kental manis creamy.', image: 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?auto=format&fit=crop&w=400&q=80' },
            { id: 1107, category: 'sachet', catName: 'SACHET', name: 'Soda Gembira', price: 12000, desc: 'Perpaduan soda air mineral, sirup merah manis, dan susu kental manis.', image: 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?auto=format&fit=crop&w=400&q=80' },
            { id: 1108, category: 'sachet', catName: 'SACHET', name: 'Air Mineral', price: 5000, desc: 'Air mineral kemasan botol dingin atau suhu normal.', image: 'https://images.unsplash.com/photo-1548839140-29a749e1bc4e?auto=format&fit=crop&w=400&q=80' },
            { id: 1109, category: 'sachet', catName: 'SACHET', name: 'Kopi Kapal Api', price: 5000, desc: 'Kopi tubruk Kapal Api hitam mantap khas teman nongkrong.', image: 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=400&q=80' },
            { id: 1110, category: 'sachet', catName: 'SACHET', name: 'Xtra Joss / Kuku Bima', price: 5000, desc: 'Minuman serbuk berenergi dingin es menyegarkan stamina.', image: 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?auto=format&fit=crop&w=400&q=80' },
            { id: 1111, category: 'sachet', catName: 'SACHET', name: 'Good Day Cappucino', price: 8000, desc: 'Kopi Good Day cappuccino dingin bertabur choco granule renyah.', image: 'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=400&q=80' },
            { id: 1112, category: 'sachet', catName: 'SACHET', name: 'Kuku Bima Susu', price: 9000, desc: 'Kuku bima rasa anggur dipadu susu kental manis creamy dingin.', image: 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?auto=format&fit=crop&w=400&q=80' },
            { id: 1113, category: 'sachet', catName: 'SACHET', name: 'Hilosaurus', price: 12000, desc: 'Susu cokelat Hilo kalsium tinggi dengan limpahan bubuk cokelat di atasnya.', image: 'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=400&q=80' },
            { id: 1114, category: 'sachet', catName: 'BLENDED', name: 'Mango Yakult', price: 13000, desc: 'Minuman mangga manis berpadu Yakult asam segar probiotic.', image: 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=400&q=80' },
            { id: 1115, category: 'sachet', catName: 'BLENDED', name: 'Lychee Yakult', price: 13000, desc: 'Perpaduan sirup leci dan Yakult segar yang creamy manis asam.', image: 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=400&q=80' },
            { id: 1116, category: 'sachet', catName: 'BLENDED', name: 'Strawberry Yakult', price: 13000, desc: 'Stroberi segar berpadu segarnya asam Yakult dingin.', image: 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=400&q=80' },
            { id: 1117, category: 'sachet', catName: 'DESSERT', name: 'Ice Cream Dessert', price: 12000, desc: 'Dessert manis 2 scoop es krim lembut dengan aneka topping renyah.', image: 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=400&q=80' }
        ],

        availableVouchers: [
            { code: 'PARJOHERMAT', title: 'Diskon Rp 5.000', discount: 5000, desc: 'Min. belanja Rp 35.000' },
            { code: 'NUGASPARJO', title: 'Potongan Rp 3.000', discount: 3000, desc: 'Tanpa minimum transaksi' },
            { code: 'BUTTERBEER', title: 'Spesial Minuman Rp 2.000', discount: 2000, desc: 'Khusus pesanan Butter Beer / Kopi' }
        ],

        cart: [],

        initApp() {
            const urlParams = new URLSearchParams(window.location.search);
            const tableParam = urlParams.get('table');
            if (tableParam) {
                this.tableNumber = parseInt(tableParam) || 3;
            }

            // Setup IntersectionObserver agar saat user scroll manual, tab kategori otomatis berpindah
            this.$nextTick(() => {
                this.setupScrollSpy();
            });
        },

        setupScrollSpy() {
            const sections = document.querySelectorAll('[id^="cat-section-"]');
            window.addEventListener('scroll', () => {
                let current = '';
                sections.forEach(section => {
                    const rect = section.getBoundingClientRect();
                    if (rect.top <= 140 && rect.bottom >= 140) {
                        current = section.id.replace('cat-section-', '');
                    }
                });
                if (current && current !== this.activeCategoryId) {
                    this.activeCategoryId = current;
                    // Auto scroll the category tabs to keep active button in view
                    const activeTabBtn = document.querySelector(`button[data-cat="${current}"]`);
                    if (activeTabBtn) {
                        activeTabBtn.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                    }
                }
            }, { passive: true });
        },

        // Action klik tab kategori: Scroll halus ke bagian kategori yang dituju
        scrollToCategory(catId) {
            this.activeCategoryId = catId;
            const targetEl = document.getElementById('cat-section-' + catId);
            if (targetEl) {
                const headerOffset = 110;
                const elementPosition = targetEl.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        },

        getCategoryProducts(catId) {
            return this.products.filter(item => {
                const matchCat = item.category === catId;
                const matchSearch = this.searchQuery === '' || 
                                    item.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                                    item.desc.toLowerCase().includes(this.searchQuery.toLowerCase());
                return matchCat && matchSearch;
            });
        },

        get totalFilteredCount() {
            return this.products.filter(item => {
                return this.searchQuery === '' || 
                       item.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                       item.desc.toLowerCase().includes(this.searchQuery.toLowerCase());
            }).length;
        },

        getItemQty(productId) {
            const items = this.cart.filter(c => c.id === productId);
            return items.reduce((total, i) => total + i.qty, 0);
        },

        increaseItemQuick(productId) {
            const existing = this.cart.find(c => c.id === productId);
            if (existing) {
                existing.qty += 1;
            }
        },

        decreaseItemQuick(productId) {
            const existing = this.cart.find(c => c.id === productId);
            if (existing) {
                existing.qty -= 1;
                if (existing.qty <= 0) {
                    this.cart = this.cart.filter(c => c.cartId !== existing.cartId);
                }
            }
        },

        get cartTotalItems() {
            return this.cart.reduce((total, item) => total + item.qty, 0);
        },

        get cartSubtotal() {
            return this.cart.reduce((total, item) => total + (item.price * item.qty), 0);
        },

        get discountAmount() {
            if (!this.appliedVoucher) return 0;
            return Math.min(this.appliedVoucher.discount, this.cartSubtotal);
        },

        get finalTotal() {
            const total = this.cartSubtotal + this.serviceFee - this.discountAmount;
            return total > 0 ? total : 0;
        },

        openProductModal(product) {
            this.selectedProduct = product;
            this.modalQty = 1;
            this.modalNotes = '';
            this.activeProductModal = true;
        },

        closeProductModal() {
            this.activeProductModal = false;
        },

        addModalToCart() {
            const cartItem = {
                cartId: Date.now() + Math.random(),
                id: this.selectedProduct.id,
                name: this.selectedProduct.name,
                price: this.selectedProduct.price,
                qty: this.modalQty,
                notes: this.modalNotes.trim()
            };

            const existing = this.cart.find(c => c.id === cartItem.id && c.notes === cartItem.notes);
            if (existing) {
                existing.qty += cartItem.qty;
            } else {
                this.cart.push(cartItem);
            }

            this.closeProductModal();
        },

        quickAddAddon(addon) {
            const existing = this.cart.find(c => c.name === 'Add-on: ' + addon.name);
            if (existing) {
                existing.qty += 1;
            } else {
                this.cart.push({
                    cartId: Date.now() + Math.random(),
                    id: 99000 + Math.floor(Math.random() * 1000),
                    name: 'Add-on: ' + addon.name,
                    price: addon.price,
                    qty: 1,
                    notes: ''
                });
            }
        },

        updateCartQty(item, delta) {
            item.qty += delta;
            if (item.qty <= 0) {
                this.cart = this.cart.filter(c => c.cartId !== item.cartId);
                if (this.cart.length === 0 && this.currentView === 'cart') {
                    this.goToView('catalog');
                }
            }
        },

        editItemNotes(item) {
            const newNote = prompt('Catatan untuk ' + item.name + ':', item.notes || '');
            if (newNote !== null) {
                item.notes = newNote.trim();
            }
        },

        clearCartConfirm() {
            if (confirm('Yakin ingin mengosongkan keranjang?')) {
                this.cart = [];
                this.goToView('catalog');
            }
        },

        goToView(view) {
            this.currentView = view;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        editTablePrompt() {
            const table = prompt('Masukkan nomor meja Anda:', this.tableNumber);
            if (table && !isNaN(table)) {
                this.tableNumber = parseInt(table);
            }
        },

        openOriginalMenuModal() {
            this.activeMenuBookModal = true;
            this.currentMenuPage = 1;
        },

        openVoucherModal() {
            if (this.appliedVoucher) {
                this.appliedVoucher = null;
            } else {
                this.activeVoucherModal = true;
            }
        },

        applyVoucher(voucher) {
            this.appliedVoucher = voucher;
            this.activeVoucherModal = false;
        },

        processPayment() {
            this.formErrors = {};
            if (!this.customer.name.trim()) {
                this.formErrors.name = 'Nama pemesan wajib diisi!';
            }
            if (!this.customer.phone.trim()) {
                this.formErrors.phone = 'Nomor WhatsApp wajib diisi!';
            }

            if (Object.keys(this.formErrors).length > 0) {
                return;
            }

            this.lastOrderId = 'PRJ-' + Math.floor(100000 + Math.random() * 900000);
            this.showSuccessModal = true;
        },

        resetAndBackToHome() {
            this.showSuccessModal = false;
            this.cart = [];
            this.appliedVoucher = null;
            this.customer = { name: '', phone: '', email: '' };
            this.goToView('catalog');
        },

        formatRupiah(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(amount);
        }
    }
}
</script>

</body>
</html>
