<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>PARJO (Pancong & Burjo) — Digital Ordering</title>
    
    <!-- Typography: Plus Jakarta Sans / Inter / Poppins (Clean, Crisp, Highly Readable) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        parjo: {
                            orange: '#FF4800',
                            darkorange: '#E03E00',
                            green: '#00583E',
                            darkgreen: '#003F2C',
                            yellow: '#EEFD37',
                            cream: '#FFFFFF',
                            sand: '#F8F9FA',
                            ink: '#1F2937',
                            muted: '#6B7280'
                        }
                    },
                    fontFamily: {
                        display: ['"Plus Jakarta Sans"', 'sans-serif'],
                        sans: ['"Plus Jakarta Sans"', 'sans-serif']
                    },
                    boxShadow: {
                        'subtle': '0 1px 3px rgba(0,0,0,0.04), 0 4px 12px rgba(0,0,0,0.03)',
                        'lift': '0 8px 24px -4px rgba(0,0,0,0.08)',
                        'hard': '3px 3px 0px #1F2937',
                        'hard-orange': '3px 3px 0px #FF4800',
                        'float': '0 12px 36px -4px rgba(255, 72, 0, 0.35)'
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    
    <style>
        body {
            background-color: #E7ECEF;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #111315;
            -webkit-tap-highlight-color: transparent;
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .app-shell {
            max-width: 480px;
            min-height: 100vh;
            margin: 0 auto;
            background-color: #FDFBF7;
            position: relative;
            box-shadow: 0 0 50px rgba(17, 19, 21, 0.08);
            display: flex;
            flex-direction: column;
        }
        /* Custom textured noise overlay for organic warmth */
        .noise-bg {
            background-image: radial-gradient(rgba(0, 88, 62, 0.04) 1px, transparent 0);
            background-size: 16px 16px;
        }
        /* Stamped typography badge */
        .stamp-badge {
            font-family: 'Syne', sans-serif;
            letter-spacing: -0.02em;
        }
    </style>
</head>
<body class="selection:bg-parjo-orange selection:text-white">

<div class="app-shell noise-bg" x-data="tasteParjoApp()" x-init="initApp()">

    <!-- ======================================================== -->
    <!-- VIEW 1: CATALOG / TASTE-ENGINEERED MENU -->
    <!-- ======================================================== -->
    <div x-show="currentView === 'catalog'" class="flex-1 flex flex-col pb-32">
        
        <!-- Live Table Strip (Editorial Ticket Style) -->
        <div class="bg-parjo-green text-white px-4 py-2 flex items-center justify-between text-xs border-b border-parjo-darkgreen">
            <div class="flex items-center space-x-2">
                <span class="w-2 h-2 rounded-full bg-parjo-yellow animate-pulse"></span>
                <span class="text-[11px] font-bold uppercase tracking-wider text-parjo-yellow">TABLE TICKET:</span>
                <span class="font-bold text-xs tracking-wide">MEJA #<span x-text="tableNumber"></span></span>
            </div>
            <div class="flex items-center space-x-2">
                <span class="text-[10px] text-emerald-200/90 font-medium">Warmindo 24/7</span>
                <button @click="editTablePrompt()" class="bg-white/10 hover:bg-white/20 text-white font-bold text-[10px] px-2 py-0.5 rounded transition">
                    Ubah
                </button>
            </div>
        </div>

        <!-- Master Header with Authentic Branding Character -->
        <header class="sticky top-0 z-30 bg-[#FDFBF7]/95 backdrop-blur-md border-b border-stone-200/80 px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2.5">
                    <!-- Brand Logomark: Sharp brutalist badge -->
                    <div class="w-10 h-10 rounded-xl bg-parjo-orange text-white flex items-center justify-center font-black text-xl shadow-hard border-2 border-parjo-ink flex-shrink-0">
                        <span>P</span>
                    </div>
                    <div>
                        <div class="flex items-center space-x-1.5">
                            <h1 class="font-black text-base tracking-tight text-parjo-ink leading-none">PARJO</h1>
                            <span class="bg-parjo-yellow text-parjo-darkgreen text-[9px] font-black uppercase px-1.5 py-0.5 rounded border border-parjo-darkgreen/30 tracking-wider">PANCONG & BURJO</span>
                        </div>
                        <p class="text-[11px] text-parjo-muted mt-0.5 font-medium flex items-center gap-1">
                            <span>Bahan Segar</span>
                            <span>•</span>
                            <span class="text-parjo-orange font-bold">Harga Mahasiswa</span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center space-x-1.5">
                    <button @click="showSearch = !showSearch" class="w-9 h-9 rounded-xl bg-white border border-stone-200 flex items-center justify-center text-stone-700 hover:text-parjo-orange hover:border-parjo-orange transition">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </button>
                    <button @click="openOriginalMenuModal()" class="h-9 px-2.5 rounded-xl bg-white border border-stone-200 text-stone-800 font-bold text-xs hover:text-parjo-orange hover:border-parjo-orange transition flex items-center space-x-1">
                        <i class="fa-solid fa-book-open text-xs text-parjo-orange"></i>
                        <span class="hidden sm:inline">Dokumen</span>
                    </button>
                </div>
            </div>

            <!-- Search Drawer with Minimalist Outline -->
            <div x-show="showSearch" x-transition class="mt-2.5 pt-2 border-t border-stone-200">
                <div class="relative">
                    <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-stone-400 text-xs"></i>
                    <input type="text" x-model="searchQuery" placeholder="Cari Nasi Ayam, Bangladesh, Butter Beer..." 
                           class="w-full pl-8 pr-8 py-2 bg-white border border-stone-300 rounded-xl text-xs font-semibold focus:outline-none focus:border-parjo-orange focus:ring-1 focus:ring-parjo-orange">
                    <button x-show="searchQuery.length > 0" @click="searchQuery = ''" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-600 text-xs">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </button>
                </div>
            </div>
        </header>

        <!-- Editorial Hero Callout: Signature Special Banner -->
        <div class="px-4 pt-3 pb-1">
            <div class="relative rounded-2xl bg-gradient-to-r from-parjo-green to-parjo-darkgreen text-white p-3.5 border-2 border-parjo-ink shadow-hard overflow-hidden">
                <div class="relative z-10 flex items-center justify-between">
                    <div>
                        <span class="text-[9px] font-black uppercase tracking-widest text-parjo-yellow bg-white/10 px-2 py-0.5 rounded-full inline-block mb-1">
                            ★ REKOMENDASI HARI INI
                        </span>
                        <div class="font-extrabold text-sm tracking-tight text-white">
                            Butter Beer 13K & Nasi Ayam Kuah Laos
                        </div>
                        <p class="text-[11px] text-emerald-200/90 mt-0.5">Segar berbusa, gurih rempah asli Parjo.</p>
                    </div>
                    <button @click="scrollToCategory('signature')" class="bg-parjo-yellow text-parjo-ink hover:bg-white text-[11px] font-black px-3 py-1.5 rounded-xl transition shadow-sm flex-shrink-0">
                        Lihat Menu
                    </button>
                </div>
            </div>
        </div>

        <!-- Sticky Categorical Tabs: Clean Underline + Tactile Pill System -->
        <div class="sticky top-[59px] z-20 bg-[#FDFBF7]/95 backdrop-blur-md border-b border-stone-200/80">
            <div class="flex items-center space-x-2 px-4 py-2.5 overflow-x-auto no-scrollbar scroll-smooth" id="categoryTabContainer">
                <template x-for="cat in categories" :key="cat.id">
                    <button @click="scrollToCategory(cat.id)"
                            :id="'tab-btn-' + cat.id"
                            :class="activeCategoryId === cat.id 
                                ? 'bg-black text-white font-black shadow-sm' 
                                : 'bg-white text-gray-700 border border-gray-200 hover:border-gray-400 font-bold'"
                            class="whitespace-nowrap px-4 py-2 rounded-full text-xs uppercase tracking-wide transition duration-150 flex-shrink-0 cursor-pointer">
                        <span x-text="cat.name"></span>
                    </button>
                </template>
            </div>
        </div>

        <!-- Continuous Scroll Menu Sections (Taste-skill Inspired) -->
        <div class="px-4 py-3 flex-1 space-y-7">
            <template x-for="cat in categories" :key="cat.id">
                <div :id="'cat-section-' + cat.id" class="scroll-mt-[120px]">
                    
                    <!-- Section Header with Editorial Numbering & Badge -->
                    <div class="flex items-baseline justify-between border-b-2 border-stone-300 pb-1.5 mb-3">
                        <div class="flex items-baseline space-x-2">
                            <span class="font-black text-sm text-parjo-orange uppercase tracking-wider" x-text="cat.name"></span>
                            <span class="text-[10px] font-bold text-stone-400 uppercase tracking-widest font-semibold">/ <span x-text="cat.sublabel"></span></span>
                        </div>
                        <span class="text-[10px] font-bold text-stone-500 font-semibold" x-text="getCategoryProducts(cat.id).length + ' MENU'"></span>
                    </div>

                    <!-- Items Grid / Rows -->
                    <div class="space-y-2.5">
                        <template x-for="item in getCategoryProducts(cat.id)" :key="item.id">
                            <div class="bg-white rounded-2xl p-3 border border-stone-200 hover:border-stone-400 transition-all shadow-subtle hover:shadow-lift flex items-center justify-between space-x-3 group">
                                
                                <!-- Product Visual Thumbnail -->
                                <div @click="openProductModal(item)" class="w-16 h-16 rounded-xl bg-stone-100 flex-shrink-0 overflow-hidden relative cursor-pointer border border-stone-200">
                                    <template x-if="item.image">
                                        <img :src="item.image" :alt="item.name" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                    </template>
                                    <template x-if="!item.image">
                                        <div class="w-full h-full flex items-center justify-center text-stone-300">
                                            <i class="fa-solid fa-utensils text-base"></i>
                                        </div>
                                    </template>
                                </div>

                                <!-- Textual Information -->
                                <div @click="openProductModal(item)" class="flex-1 min-w-0 cursor-pointer pr-1">
                                    <div class="flex items-center space-x-1.5">
                                        <h3 class="font-bold text-xs text-parjo-ink leading-snug group-hover:text-parjo-orange transition truncate" x-text="item.name"></h3>
                                        <span x-show="item.badge" class="bg-orange-100 text-parjo-orange text-[9px] font-black px-1.5 py-0.2 rounded font-semibold" x-text="item.badge"></span>
                                    </div>
                                    <p class="text-[11px] text-stone-500 line-clamp-1 mt-0.5 leading-tight" x-text="item.desc"></p>
                                    <div class="font-extrabold text-xs text-stone-900 mt-1" x-text="formatRupiah(item.price)"></div>
                                </div>

                                <!-- High-Contrast Action Button -->
                                <div class="flex-shrink-0">
                                    <template x-if="getItemQty(item.id) === 0">
                                        <button @click="openProductModal(item)" 
                                                class="h-8 px-3 rounded-xl border border-parjo-orange bg-white text-parjo-orange font-bold text-xs hover:bg-parjo-orange hover:text-white transition active:scale-95 shadow-sm">
                                            Tambah
                                        </button>
                                    </template>
                                    <template x-if="getItemQty(item.id) > 0">
                                        <div class="flex items-center space-x-1.5 bg-orange-50 border border-parjo-orange/30 px-2 py-1 rounded-xl">
                                            <button @click="decreaseItemQuick(item.id)" class="w-5 h-5 rounded-lg bg-white text-parjo-orange font-bold text-xs flex items-center justify-center hover:bg-red-50 hover:text-red-600 transition shadow-xs">-</button>
                                            <span class="text-xs font-black text-parjo-orange w-4 text-center" x-text="getItemQty(item.id)"></span>
                                            <button @click="increaseItemQuick(item.id)" class="w-5 h-5 rounded-lg bg-parjo-orange text-white font-bold text-xs flex items-center justify-center hover:bg-parjo-darkorange transition shadow-xs">+</button>
                                        </div>
                                    </template>
                                </div>

                            </div>
                        </template>
                    </div>

                </div>
            </template>
        </div>

        <!-- Sticky Floating Cart Pill (Bold Taste-Engineered Split Bar) -->
        <div x-show="cartTotalItems > 0" 
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-y-8"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-8"
             class="fixed bottom-4 left-0 right-0 max-w-[480px] mx-auto px-4 z-40">
            
            <div class="bg-parjo-ink text-white rounded-2xl shadow-float flex items-stretch overflow-hidden border-2 border-stone-800">
                <!-- Left: White Basket Stamp with Animated Item Counter -->
                <div @click="goToView('cart')" class="bg-white px-3.5 py-3 flex items-center justify-center cursor-pointer relative flex-shrink-0">
                    <i class="fa-solid fa-basket-shopping text-xl text-parjo-orange"></i>
                    <span class="absolute top-1.5 right-1.5 bg-parjo-orange text-white text-[9px] font-black w-4 h-4 rounded-full flex items-center justify-center shadow" x-text="cartTotalItems"></span>
                </div>

                <!-- Middle: Total Price -->
                <div @click="goToView('cart')" class="flex-1 px-3 py-2 flex flex-col justify-center cursor-pointer">
                    <span class="text-[9px] text-stone-400 font-semibold uppercase tracking-wider">TOTAL TAGIHAN</span>
                    <span class="font-black text-sm text-white leading-none mt-0.5" x-text="formatRupiah(cartSubtotal)"></span>
                </div>

                <!-- Right: Check Out Action -->
                <button @click="goToView('cart')" class="bg-parjo-orange hover:bg-parjo-darkorange px-5 py-2 flex items-center justify-center font-black text-xs uppercase tracking-wider text-white transition active:scale-95 space-x-1.5">
                    <span>CHECK OUT</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </button>
            </div>
        </div>

    </div>

    <!-- ======================================================== -->
    <!-- VIEW 2: CART / REVIEW ORDER -->
    <!-- ======================================================== -->
    <div x-show="currentView === 'cart'" class="flex-1 flex flex-col pb-32">
        
        <header class="sticky top-0 z-30 bg-[#FDFBF7] border-b border-stone-200 px-4 py-3.5 flex items-center justify-between">
            <div class="flex items-center space-x-2.5">
                <button @click="goToView('catalog')" class="w-8 h-8 rounded-xl bg-white border border-stone-200 flex items-center justify-center text-stone-700 hover:text-parjo-orange transition">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                </button>
                <div>
                    <h1 class="font-black text-sm text-parjo-ink">Ringkasan Pesanan</h1>
                    <p class="text-[10px] text-stone-400 font-semibold">MEJA #<span x-text="tableNumber"></span> • PARJO ORDER</p>
                </div>
            </div>
            <button @click="clearCartConfirm()" class="text-xs text-red-500 font-bold hover:underline">
                Kosongkan
            </button>
        </header>

        <div class="p-4 space-y-4 flex-1">
            <!-- Dine-in vs Take-away Selector -->
            <div class="bg-white p-3 rounded-2xl border border-stone-200 shadow-subtle">
                <label class="block text-[10px] font-semibold font-bold text-stone-400 uppercase tracking-wider mb-1.5">TIPE PEMESANAN</label>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" @click="orderType = 'dine-in'"
                            :class="orderType === 'dine-in' ? 'bg-parjo-ink text-white font-bold' : 'bg-stone-50 text-stone-600 border border-stone-200 font-medium'"
                            class="py-2 px-3 rounded-xl text-xs flex items-center justify-center space-x-1.5 transition">
                        <span>🍽️ Makan di Meja</span>
                    </button>
                    <button type="button" @click="orderType = 'take-away'"
                            :class="orderType === 'take-away' ? 'bg-parjo-ink text-white font-bold' : 'bg-stone-50 text-stone-600 border border-stone-200 font-medium'"
                            class="py-2 px-3 rounded-xl text-xs flex items-center justify-center space-x-1.5 transition">
                        <span>🛍️ Bawa Pulang</span>
                    </button>
                </div>
            </div>

            <!-- Ordered Items List -->
            <div class="bg-white rounded-2xl border border-stone-200 p-3.5 shadow-subtle">
                <span class="text-[10px] font-semibold font-bold text-stone-400 uppercase tracking-wider block mb-3">ITEM DALAM TIKET (<span x-text="cartTotalItems"></span>)</span>
                
                <div class="divide-y divide-stone-100">
                    <template x-for="item in cart" :key="item.cartId">
                        <div class="py-3 first:pt-0 last:pb-0 flex items-start justify-between">
                            <div class="flex-1 pr-2">
                                <h4 class="font-bold text-xs text-parjo-ink" x-text="item.name"></h4>
                                <div class="font-black text-xs text-parjo-orange mt-0.5" x-text="formatRupiah(item.price)"></div>
                                
                                <div x-show="item.notes" class="text-[10px] text-stone-500 bg-stone-50 p-1.5 rounded-lg mt-1 border border-stone-100">
                                    <i class="fa-solid fa-pen-fancy text-parjo-orange mr-1 text-[8px]"></i>
                                    <span x-text="item.notes"></span>
                                </div>
                                
                                <button @click="editItemNotes(item)" class="text-[10px] text-stone-500 hover:text-parjo-orange font-bold mt-1 inline-block">
                                    <span x-text="item.notes ? 'Ubah Catatan' : '+ Catatan Koki'"></span>
                                </button>
                            </div>

                            <!-- Stepper -->
                            <div class="flex items-center space-x-1.5 bg-stone-50 border border-stone-200 px-2 py-1 rounded-xl">
                                <button @click="updateCartQty(item, -1)" class="w-5 h-5 rounded-lg bg-white text-stone-600 hover:text-red-600 font-bold text-xs flex items-center justify-center shadow-xs">
                                    <i class="fa-solid" :class="item.qty === 1 ? 'fa-trash-can text-red-500 text-[10px]' : 'fa-minus text-[10px]'"></i>
                                </button>
                                <span class="text-xs font-black text-parjo-ink w-4 text-center font-bold" x-text="item.qty"></span>
                                <button @click="updateCartQty(item, 1)" class="w-5 h-5 rounded-lg bg-parjo-orange text-white font-bold text-xs flex items-center justify-center hover:bg-parjo-darkorange shadow-xs">+</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Receipt Breakdown -->
            <div class="bg-white rounded-2xl border border-stone-200 p-3.5 space-y-2 text-xs shadow-subtle font-semibold">
                <div class="flex justify-between text-stone-600">
                    <span>Subtotal Menu</span>
                    <span class="font-bold text-stone-900" x-text="formatRupiah(cartSubtotal)"></span>
                </div>
                <div class="flex justify-between text-stone-600">
                    <span>Biaya Kebersihan & Layanan</span>
                    <span class="font-bold text-stone-900" x-text="formatRupiah(serviceFee)"></span>
                </div>
                <div x-show="discountAmount > 0" class="flex justify-between text-emerald-600 font-bold">
                    <span>Potongan Kupon (<span x-text="appliedVoucher.code"></span>)</span>
                    <span x-text="'-' + formatRupiah(discountAmount)"></span>
                </div>
                <div class="pt-2 border-t border-stone-200 flex justify-between font-black text-sm text-parjo-ink font-bold">
                    <span>TOTAL PEMBAYARAN</span>
                    <span class="text-parjo-orange text-base" x-text="formatRupiah(finalTotal)"></span>
                </div>
            </div>
        </div>

        <!-- Sticky Bottom -->
        <div class="fixed bottom-0 left-0 right-0 max-w-[480px] mx-auto bg-[#FDFBF7] border-t border-stone-200 p-3.5 shadow-lg z-30">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[9px] text-stone-400 font-semibold uppercase">TOTAL AKHIR</span>
                    <div class="font-black text-lg text-parjo-orange leading-none mt-0.5" x-text="formatRupiah(finalTotal)"></div>
                </div>
                <button @click="goToView('checkout')" class="bg-parjo-orange hover:bg-parjo-darkorange text-white font-black text-xs px-7 py-3 rounded-xl transition shadow-hard-orange active:scale-95 flex items-center space-x-1.5">
                    <span>Lanjut Bayar</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
            </div>
        </div>

    </div>

    <!-- ======================================================== -->
    <!-- VIEW 3: CHECKOUT / PAYMENT FORM -->
    <!-- ======================================================== -->
    <div x-show="currentView === 'checkout'" class="flex-1 flex flex-col pb-32">
        
        <header class="sticky top-0 z-30 bg-[#FDFBF7] border-b border-stone-200 px-4 py-3.5 flex items-center space-x-3">
            <button @click="goToView('cart')" class="w-8 h-8 rounded-xl bg-white border border-stone-200 flex items-center justify-center text-stone-700 hover:text-parjo-orange transition">
                <i class="fa-solid fa-arrow-left text-xs"></i>
            </button>
            <div>
                <h1 class="font-black text-sm text-parjo-ink">Konfirmasi Tiket & Bayar</h1>
                <p class="text-[10px] text-stone-400 font-semibold">PARJO ORDER SYSTEM</p>
            </div>
        </header>

        <div class="p-4 space-y-4 flex-1">
            <!-- Customer Information Form -->
            <div class="bg-white rounded-2xl border border-stone-200 p-3.5 space-y-3 shadow-subtle">
                <span class="text-[10px] font-semibold font-bold text-stone-400 uppercase tracking-wider block">IDENTITAS PEMESAN</span>
                
                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">Nama Panggilan / Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" x-model="customer.name" placeholder="Contoh: Dimas"
                           class="w-full bg-stone-50 border border-stone-200 text-xs font-semibold rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-parjo-orange">
                    <span x-show="formErrors.name" class="text-[10px] text-red-500 font-bold mt-0.5 block" x-text="formErrors.name"></span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">Nomor WhatsApp <span class="text-red-500">*</span></label>
                    <input type="tel" x-model="customer.phone" placeholder="08123456789"
                           class="w-full bg-stone-50 border border-stone-200 text-xs font-semibold rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-parjo-orange">
                    <span x-show="formErrors.phone" class="text-[10px] text-red-500 font-bold mt-0.5 block" x-text="formErrors.phone"></span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">Nomor Meja</label>
                    <input type="number" x-model="tableNumber"
                           class="w-24 bg-stone-50 border border-stone-200 text-xs font-black text-center rounded-xl px-3 py-2">
                </div>
            </div>

            <!-- Voucher Box -->
            <div class="bg-white rounded-2xl border border-stone-200 p-3.5 flex items-center justify-between shadow-subtle">
                <div>
                    <span class="text-xs font-bold text-stone-800 block">Kupon Hemat Anak Kos</span>
                    <span class="text-[10px] text-stone-400 font-semibold" x-text="appliedVoucher ? appliedVoucher.code + ' AKTIF' : 'Klaim potongan harga'"></span>
                </div>
                <button @click="openVoucherModal()" class="text-xs font-bold px-3 py-1.5 rounded-xl border border-parjo-orange text-parjo-orange hover:bg-parjo-orange hover:text-white transition">
                    <span x-text="appliedVoucher ? 'Hapus' : 'Pilih Kupon'"></span>
                </button>
            </div>

            <!-- Payment Methods -->
            <div class="bg-white rounded-2xl border border-stone-200 p-3.5 space-y-2.5 shadow-subtle">
                <span class="text-[10px] font-semibold font-bold text-stone-400 uppercase tracking-wider block">METODE PEMBAYARAN</span>
                
                <label class="flex items-center justify-between p-3 rounded-xl border border-stone-200 cursor-pointer transition" :class="paymentMethod === 'qris' ? 'border-parjo-orange bg-orange-50/50' : ''">
                    <div class="flex items-center space-x-2.5">
                        <input type="radio" name="paymethod" value="qris" x-model="paymentMethod" class="text-parjo-orange">
                        <div>
                            <span class="text-xs font-bold text-stone-900 block">QRIS Otomatis</span>
                            <span class="text-[10px] text-stone-500">GoPay, Shopee, BCA, Dana</span>
                        </div>
                    </div>
                    <span class="text-[9px] bg-emerald-100 text-emerald-800 font-black px-1.5 py-0.5 rounded font-semibold">INSTAN</span>
                </label>

                <label class="flex items-center justify-between p-3 rounded-xl border border-stone-200 cursor-pointer transition" :class="paymentMethod === 'cashier' ? 'border-parjo-orange bg-orange-50/50' : ''">
                    <div class="flex items-center space-x-2.5">
                        <input type="radio" name="paymethod" value="cashier" x-model="paymentMethod" class="text-parjo-orange">
                        <div>
                            <span class="text-xs font-bold text-stone-900 block">Bayar di Kasir (Tunai)</span>
                            <span class="text-[10px] text-stone-500">Bayar ke AA saat makanan selesai</span>
                        </div>
                    </div>
                    <span class="text-[9px] bg-stone-100 text-stone-600 font-black px-1.5 py-0.5 rounded font-semibold">TUNAI</span>
                </label>
            </div>
        </div>

        <!-- Sticky Bottom -->
        <div class="fixed bottom-0 left-0 right-0 max-w-[480px] mx-auto bg-[#FDFBF7] border-t border-stone-200 p-3.5 shadow-lg z-30">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[9px] text-stone-400 font-semibold uppercase">TOTAL AKHIR</span>
                    <div class="font-black text-lg text-parjo-orange leading-none mt-0.5" x-text="formatRupiah(finalTotal)"></div>
                </div>
                <button @click="processPayment()" class="bg-parjo-orange hover:bg-parjo-darkorange text-white font-black text-xs px-8 py-3 rounded-xl transition shadow-hard-orange active:scale-95">
                    Kirim Pesanan
                </button>
            </div>
        </div>

    </div>

    <!-- ======================================================== -->
    <!-- BOTTOM SHEET: DETAIL PRODUK (TASTE POLISHED) -->
    <!-- ======================================================== -->
    <div x-show="activeProductModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-end justify-center bg-black/70 backdrop-blur-xs">
        
        <div @click.away="closeProductModal()"
             x-show="activeProductModal"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full"
             class="w-full max-w-[480px] bg-white rounded-t-3xl overflow-hidden max-h-[90vh] flex flex-col shadow-2xl relative">
            
            <button @click="closeProductModal()" class="absolute top-3.5 right-3.5 z-20 w-8 h-8 rounded-full bg-black/50 text-white flex items-center justify-center hover:bg-black/70 transition">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>

            <div class="overflow-y-auto flex-1 pb-28">
                <!-- Large Image -->
                <div class="w-full h-56 bg-stone-100 relative">
                    <img :src="selectedProduct?.image" :alt="selectedProduct?.name" class="w-full h-full object-cover">
                </div>

                <div class="p-4 space-y-4">
                    <div>
                        <span class="text-[10px] font-semibold font-bold text-parjo-orange uppercase tracking-wider" x-text="selectedProduct?.catName"></span>
                        <h2 class="font-black text-base text-parjo-ink leading-snug mt-0.5" x-text="selectedProduct?.name"></h2>
                        <div class="font-black text-lg text-parjo-ink mt-1" x-text="formatRupiah(selectedProduct?.price || 0)"></div>
                        <p class="text-xs text-stone-500 mt-2 leading-relaxed" x-text="selectedProduct?.desc"></p>
                    </div>

                    <!-- Preset Chips Pilihan Rasa Parjo -->
                    <div class="pt-2 border-t border-stone-100">
                        <label class="block text-xs font-bold text-stone-700 mb-1">Instruksi Khusus (Opsional)</label>
                        <input type="text" x-model="modalNotes" placeholder="Contoh: Kuah sedikit, telur setengah matang, es pisah..."
                               class="w-full bg-stone-50 border border-stone-200 text-xs rounded-xl px-3 py-2.5 focus:outline-none focus:border-parjo-orange font-medium">
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-stone-100">
                        <span class="text-xs font-black text-parjo-ink">Jumlah Porsi</span>
                        <div class="flex items-center space-x-3 bg-stone-50 border border-stone-200 px-3 py-1.5 rounded-xl">
                            <button @click="if(modalQty > 1) modalQty--" class="text-xs font-bold text-stone-600 hover:text-parjo-orange px-1">-</button>
                            <span class="text-sm font-black w-6 text-center text-parjo-ink" x-text="modalQty"></span>
                            <button @click="modalQty++" class="text-xs font-bold text-parjo-orange px-1">+</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sticky Bottom Add -->
            <div class="absolute bottom-0 left-0 right-0 p-3.5 bg-white border-t border-stone-200 z-20">
                <button @click="addModalToCart()" class="w-full bg-parjo-orange hover:bg-parjo-darkorange text-white font-black text-xs py-3.5 rounded-xl transition shadow-hard-orange flex items-center justify-between px-5 active:scale-98">
                    <span>Tambahkan ke Pesanan</span>
                    <span x-text="formatRupiah((selectedProduct?.price || 0) * modalQty)"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL DOKUMEN BUKU MENU ASLI -->
    <!-- ======================================================== -->
    <div x-show="activeMenuBookModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-3 bg-black/80 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-md w-full max-h-[90vh] flex flex-col overflow-hidden shadow-2xl">
            <div class="p-3 border-b border-stone-200 flex items-center justify-between bg-white">
                <h3 class="font-black text-xs text-parjo-ink">Buku Menu Cetak Asli (Hal <span x-text="currentMenuPage"></span>/3)</h3>
                <button @click="activeMenuBookModal = false" class="text-stone-500 hover:text-stone-800 text-sm">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="overflow-y-auto flex-1 bg-stone-900 p-1 flex items-center justify-center">
                <img :src="'/images/menu/menu_page_' + currentMenuPage + '.jpg'" class="w-full h-auto object-contain rounded">
            </div>
            <div class="p-2.5 bg-white border-t border-stone-200 flex justify-between items-center text-xs">
                <div class="flex space-x-1.5">
                    <button @click="currentMenuPage = 1" :class="currentMenuPage === 1 ? 'bg-parjo-ink text-white font-bold' : 'bg-stone-100'" class="px-2.5 py-1 rounded text-[11px]">Hal 1</button>
                    <button @click="currentMenuPage = 2" :class="currentMenuPage === 2 ? 'bg-parjo-ink text-white font-bold' : 'bg-stone-100'" class="px-2.5 py-1 rounded text-[11px]">Hal 2</button>
                    <button @click="currentMenuPage = 3" :class="currentMenuPage === 3 ? 'bg-parjo-ink text-white font-bold' : 'bg-stone-100'" class="px-2.5 py-1 rounded text-[11px]">Hal 3</button>
                </div>
                <button @click="activeMenuBookModal = false" class="text-xs font-bold text-stone-500">Tutup</button>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL VOUCHER -->
    <!-- ======================================================== -->
    <div x-show="activeVoucherModal" x-transition class="fixed inset-0 z-50 flex items-end justify-center bg-black/60 backdrop-blur-xs">
        <div @click.away="activeVoucherModal = false" class="w-full max-w-[480px] bg-white rounded-t-3xl overflow-hidden max-h-[80vh] flex flex-col p-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-stone-100 pb-2.5">
                <h3 class="font-black text-xs text-parjo-ink uppercase">VOUCHER DISKON MAHASISWA</h3>
                <button @click="activeVoucherModal = false" class="text-stone-400"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="py-3 space-y-2 overflow-y-auto flex-1 font-semibold">
                <template x-for="v in availableVouchers" :key="v.code">
                    <div @click="applyVoucher(v)" class="border rounded-xl p-3 cursor-pointer flex items-center justify-between"
                         :class="appliedVoucher?.code === v.code ? 'border-parjo-orange bg-orange-50/50' : 'border-stone-200'">
                        <div>
                            <div class="text-xs font-black text-parjo-orange" x-text="v.code"></div>
                            <div class="text-[11px] font-bold text-stone-800" x-text="v.title"></div>
                            <div class="text-[10px] text-stone-500" x-text="v.desc"></div>
                        </div>
                        <i :class="appliedVoucher?.code === v.code ? 'fa-solid fa-circle-check text-parjo-orange' : 'fa-regular fa-circle text-stone-300'"></i>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- CUSTOM UI MODAL: UBAH NOMOR MEJA -->
    <!-- ======================================================== -->
    <div x-show="showTableModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.away="showTableModal = false" 
             class="bg-white rounded-3xl p-5 w-full max-w-xs shadow-2xl border border-stone-200 space-y-4 animate-in fade-in zoom-in-95 duration-200">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-orange-100 text-parjo-orange flex items-center justify-center text-lg font-black">
                    <i class="fa-solid fa-chair"></i>
                </div>
                <div>
                    <h3 class="font-black text-sm text-stone-900 leading-tight">Ganti Nomor Meja</h3>
                    <p class="text-[11px] text-stone-400">Sesuaikan posisi duduk Anda</p>
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-stone-600 mb-1.5">Nomor Meja Baru</label>
                <div class="relative flex items-center">
                    <span class="absolute left-3.5 text-stone-400 text-xs font-bold font-mono">NO.</span>
                    <input type="number" x-model="tempTableNumber" min="1" max="99" autofocus
                           @keydown.enter="saveTableNumber()"
                           class="w-full pl-12 pr-4 py-2.5 bg-stone-50 border-2 border-stone-200 focus:border-parjo-orange focus:bg-white text-base font-black text-stone-900 rounded-xl outline-none transition text-center">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 pt-1">
                <button type="button" @click="showTableModal = false" 
                        class="w-full py-2.5 rounded-xl border border-stone-200 text-xs font-bold text-stone-600 hover:bg-stone-100 transition">
                    Batal
                </button>
                <button type="button" @click="saveTableNumber()" 
                        class="w-full py-2.5 rounded-xl bg-parjo-orange hover:bg-parjo-darkorange text-white text-xs font-black shadow-md transition active:scale-95">
                    Simpan
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- CUSTOM UI MODAL: CATATAN ITEM KERANJANG -->
    <!-- ======================================================== -->
    <div x-show="showNoteModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.away="showNoteModal = false" 
             class="bg-white rounded-3xl p-5 w-full max-w-sm shadow-2xl border border-stone-200 space-y-4 animate-in fade-in zoom-in-95 duration-200">
            <div class="flex items-center justify-between border-b border-stone-100 pb-2">
                <div>
                    <h3 class="font-black text-sm text-stone-900">Catatan Khusus Koki</h3>
                    <p class="text-[11px] text-stone-400 truncate max-w-[220px]" x-text="activeNoteItem?.name"></p>
                </div>
                <button @click="showNoteModal = false" class="text-stone-400 hover:text-stone-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div>
                <textarea x-model="tempItemNote" rows="3" placeholder="Contoh: Kuah dipisah, pedas sedang, jangan pakai bawang goreng..."
                          class="w-full bg-stone-50 border-2 border-stone-200 focus:border-parjo-orange focus:bg-white text-xs font-semibold text-stone-900 rounded-xl p-3 outline-none transition resize-none"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <button type="button" @click="showNoteModal = false" 
                        class="w-full py-2.5 rounded-xl border border-stone-200 text-xs font-bold text-stone-600 hover:bg-stone-100 transition">
                    Batal
                </button>
                <button type="button" @click="saveItemNote()" 
                        class="w-full py-2.5 rounded-xl bg-parjo-orange hover:bg-parjo-darkorange text-white text-xs font-black shadow-md transition active:scale-95">
                    Terapkan
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- CUSTOM UI MODAL: KONFIRMASI KOSONGKAN KERANJANG -->
    <!-- ======================================================== -->
    <div x-show="showConfirmClearModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.away="showConfirmClearModal = false" 
             class="bg-white rounded-3xl p-5 w-full max-w-xs shadow-2xl border border-stone-200 text-center space-y-3 animate-in fade-in zoom-in-95 duration-200">
            <div class="w-12 h-12 rounded-2xl bg-red-100 text-red-600 flex items-center justify-center mx-auto text-xl">
                <i class="fa-solid fa-trash-can"></i>
            </div>
            <div>
                <h3 class="font-black text-sm text-stone-900">Kosongkan Pesanan?</h3>
                <p class="text-xs text-stone-500 mt-0.5">Semua menu yang sudah Anda pilih akan dihapus dari keranjang.</p>
            </div>
            <div class="grid grid-cols-2 gap-2 pt-2">
                <button type="button" @click="showConfirmClearModal = false" 
                        class="w-full py-2.5 rounded-xl border border-stone-200 text-xs font-bold text-stone-600 hover:bg-stone-100 transition">
                    Batal
                </button>
                <button type="button" @click="executeClearCart()" 
                        class="w-full py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-black shadow-md transition active:scale-95">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL / NOTA DIGITAL (ESB DIGITAL RECEIPT STYLE) -->
    <!-- ======================================================== -->
    <div x-show="showSuccessModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        
        <div @click.away="showSuccessModal = false"
             class="bg-white rounded-3xl w-full max-w-[420px] max-h-[90vh] overflow-y-auto shadow-2xl border border-stone-200 text-stone-900 animate-in fade-in zoom-in-95 duration-200 p-6 flex flex-col justify-between">
            
            <div class="space-y-5">
                <!-- Top Header: Date & Order Number -->
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-xs text-stone-500 font-medium">Date</div>
                        <div class="text-sm font-bold text-stone-900 mt-0.5" x-text="orderDateStr || '26 Oct 2026, 19:33'"></div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-stone-500 font-medium">Order Number</div>
                        <div class="flex items-center justify-end space-x-1.5 mt-0.5 cursor-pointer text-stone-900 hover:text-parjo-orange" 
                             @click="navigator.clipboard.writeText(lastOrderId); alert('Order Number disalin!')">
                            <i class="fa-regular fa-copy text-parjo-orange text-xs"></i>
                            <span class="text-sm font-black tracking-wide" x-text="lastOrderId"></span>
                        </div>
                    </div>
                </div>

                <!-- Outlet Location & Table Number -->
                <div class="space-y-3.5 pt-1">
                    <!-- Outlet Location -->
                    <div class="flex items-start space-x-3">
                        <div class="w-7 h-7 flex items-center justify-center text-parjo-orange text-lg flex-shrink-0 mt-0.5">
                            <i class="fa-solid fa-utensils"></i>
                        </div>
                        <div>
                            <div class="text-xs text-stone-500 font-medium leading-none">Outlet Location</div>
                            <div class="text-sm font-black text-stone-900 mt-1">PARJO (Pancong & Burjo)</div>
                        </div>
                    </div>

                    <!-- Table Number -->
                    <div class="flex items-start space-x-3">
                        <div class="w-7 h-7 flex items-center justify-center text-parjo-orange text-lg flex-shrink-0 mt-0.5">
                            <i class="fa-solid fa-chair"></i>
                        </div>
                        <div>
                            <div class="text-xs text-stone-500 font-medium leading-none">Table Number</div>
                            <div class="text-sm font-black text-stone-900 mt-1" x-text="tableNumber"></div>
                        </div>
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-stone-200"></div>

                <!-- Ordered Items Section -->
                <div>
                    <div class="flex items-center justify-between mb-3.5">
                        <h3 class="text-sm font-extrabold text-stone-900">Ordered Items</h3>
                        <button type="button" @click="resetAndBackToHome()" 
                                class="border border-parjo-orange text-parjo-orange hover:bg-orange-50 px-3 py-1 rounded-full text-xs font-bold transition">
                            Reorder
                        </button>
                    </div>

                    <!-- Items List -->
                    <div class="space-y-3">
                        <template x-for="item in confirmedCart" :key="item.cartId">
                            <div class="flex items-start justify-between text-xs">
                                <div class="flex-1 pr-2">
                                    <span class="font-black text-stone-900" x-text="item.qty + 'x'"></span>
                                    <span class="font-medium text-stone-800 ml-1.5" x-text="item.name"></span>
                                    <div x-show="item.notes" class="text-[10px] text-stone-400 mt-0.5 italic ml-5" x-text="'Catatan: ' + item.notes"></div>
                                </div>
                                <div class="font-bold text-stone-900 flex-shrink-0" x-text="formatRupiah(item.price * item.qty)"></div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-stone-200"></div>

                <!-- Fees & Payment Details -->
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between text-stone-700">
                        <span class="font-medium">Platform Fee</span>
                        <span class="font-bold text-stone-900" x-text="formatRupiah(serviceFee)"></span>
                    </div>
                    <div class="flex justify-between text-stone-700">
                        <span class="font-medium">Payment Method</span>
                        <span class="font-extrabold text-stone-900 uppercase" x-text="paymentMethod === 'qris' ? 'QRIS' : 'KASIR'"></span>
                    </div>
                    <div x-show="discountAmount > 0" class="flex justify-between text-emerald-600">
                        <span class="font-medium">Discount Promo</span>
                        <span class="font-bold" x-text="'-' + formatRupiah(discountAmount)"></span>
                    </div>
                </div>

                <!-- Final Total -->
                <div class="pt-2 border-t border-stone-200 flex justify-between items-baseline">
                    <span class="text-sm font-extrabold text-stone-900">Total</span>
                    <span class="text-lg font-black text-parjo-orange" x-text="formatRupiah(confirmedTotal)"></span>
                </div>
            </div>

            <!-- Bottom Actions -->
            <div class="mt-6 pt-3 border-t border-stone-100 flex items-center space-x-2">
                <button @click="showSuccessModal = false" class="w-1/2 py-2.5 rounded-xl border border-stone-200 font-bold text-xs text-stone-600 hover:bg-stone-50 transition">
                    Tutup
                </button>
                <button @click="resetAndBackToHome()" class="w-1/2 py-2.5 rounded-xl bg-parjo-orange hover:bg-parjo-darkorange text-white font-extrabold text-xs transition shadow-md">
                    Selesai
                </button>
            </div>

        </div>
    </div>

<!-- Application Logic -->
<script>
function tasteParjoApp() {
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
        orderDateStr: '',
        confirmedCart: [],
        confirmedTotal: 0,

        // Custom UI Modal Dialogs
        showTableModal: false,
        tempTableNumber: 3,
        showNoteModal: false,
        activeNoteItem: null,
        tempItemNote: '',
        showConfirmClearModal: false,

        quickNotesList: [
            'Pedas Banget',
            'Pedas Sedang',
            'Sedikit Kuah',
            'Telur 1/2 Matang',
            'Es Sedikit',
            'Tanpa Sayur'
        ],

        // Taste Categorical Sections
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

        addOnList: [
            { name: 'Kol Goreng', price: 3000 },
            { name: 'Kornet', price: 7000 },
            { name: 'Sosis', price: 5000 },
            { name: 'Bakso', price: 5000 },
            { name: 'Telor', price: 5000 },
            { name: 'Nasi', price: 5000 },
            { name: 'Ayam', price: 6000 }
        ],

        products: [
            // SIGNATURE
            { id: 101, category: 'signature', catName: 'SIGNATURE', name: 'Nasi Ayam Kuah Laos', price: 16000, badge: 'Favorite', desc: 'Ayam bumbu khas kuah laos rempah gurih disajikan dengan nasi hangat dan lalapan.', image: 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=400&q=80' },
            { id: 102, category: 'signature', catName: 'SIGNATURE', name: 'Nasi Ayam Parjo Signature', price: 20000, badge: 'Signature', desc: 'Menu andalan Parjo dengan racikan bumbu rahasia premium lengkap kerupuk & serundeng.', image: 'https://images.unsplash.com/photo-1541832676-9b763b0239ab?auto=format&fit=crop&w=400&q=80' },
            { id: 103, category: 'signature', catName: 'SIGNATURE', name: 'Butter Rice Smoked Beef', price: 16000, badge: null, desc: 'Nasi mentega wangi dipadu potongan daging asap gurih empuk bercitarasa creamy.', image: 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?auto=format&fit=crop&w=400&q=80' },
            { id: 104, category: 'signature', catName: 'SIGNATURE', name: 'Nasi Ayam Saus Karamel', price: 16000, badge: null, desc: 'Ayam goreng crispy berselimut glaze saus karamel manis gurih menggoda selera.', image: 'https://images.unsplash.com/photo-1569058242253-92a9c755a0ec?auto=format&fit=crop&w=400&q=80' },
            { id: 105, category: 'signature', catName: 'SIGNATURE', name: 'Nasi Ayam Gongso Kuah', price: 18000, badge: 'Baru', desc: 'Ayam suwir bumbu gongso kecap gurih dengan kuah pekat kaya rempah dan sayuran segar.', image: 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=400&q=80' },

            // BURJO & NASI
            { id: 201, category: 'burjo', catName: 'BURJO', name: 'Nasi Gila', price: 15000, desc: 'Nasi putih dengan topping tumisan sosis, bakso, dan telur orak-arik bumbu gurih.', image: 'https://images.unsplash.com/photo-1512058564366-18510be2db19?auto=format&fit=crop&w=400&q=80' },
            { id: 202, category: 'burjo', catName: 'BURJO', name: 'Nasi Goreng Gila', price: 20000, badge: 'Favorit', desc: 'Nasi goreng racikan Parjo disiram topping sosis bakso telur melimpah.', image: 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?auto=format&fit=crop&w=400&q=80' },
            { id: 203, category: 'burjo', catName: 'BURJO', name: 'Mie Tek-Tek', price: 16000, desc: 'Mie goreng tek-tek bumbu tradisional dengan sayuran kubis, sawi, dan telur gurih.', image: 'https://images.unsplash.com/photo-1585032226651-759b368d7246?auto=format&fit=crop&w=400&q=80' },
            { id: 204, category: 'burjo', catName: 'BURJO', name: 'Magelangan', price: 16000, badge: 'Best Seller', desc: 'Perpaduan legendaris nasi dan mie dimasak bersama bumbu racikan khas Burjo.', image: 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?auto=format&fit=crop&w=400&q=80' },
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
            { id: 305, category: 'ayam', catName: 'AYAM', name: 'Nasi Ayam Salted Egg', price: 18000, badge: 'Favorit', desc: 'Ayam goreng crispy dibalut saus telur asin creamy gurih harum daun kari.', image: 'https://images.unsplash.com/photo-1527477378698-4c8d50f83733?auto=format&fit=crop&w=400&q=80' },
            { id: 306, category: 'ayam', catName: 'AYAM', name: 'Nasi Ayam Barbecue', price: 16000, desc: 'Ayam panggang bumbu BBQ manis gurih dengan aroma asap khas.', image: 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=400&q=80' },
            { id: 307, category: 'ayam', catName: 'AYAM', name: 'Nasi Ayam Mentega', price: 16000, desc: 'Ayam tumis saus mentega gurih manis dengan irisan bawang bombay.', image: 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=400&q=80' },
            { id: 308, category: 'ayam', catName: 'AYAM', name: 'Nasi Ayam Cabe Garam', price: 17000, badge: 'Best Seller', desc: 'Ayam fillet crispy renyah ditumis irisan cabai rawit merah dan bawang putih.', image: 'https://images.unsplash.com/photo-1569058242253-92a9c755a0ec?auto=format&fit=crop&w=400&q=80' },
            { id: 309, category: 'ayam', catName: 'AYAM', name: 'Nasi Ayam Bali Crispy', price: 16000, desc: 'Ayam krispi renyah disajikan dengan sambal matah & bumbu bali gurih.', image: 'https://images.unsplash.com/photo-1541832676-9b763b0239ab?auto=format&fit=crop&w=400&q=80' },
            { id: 310, category: 'ayam', catName: 'AYAM', name: 'Nasi Ayam Asam Manis', price: 15000, desc: 'Ayam goreng tepung renyah berpadu saus asam manis segar.', image: 'https://images.unsplash.com/photo-1525351484163-7529414344d8?auto=format&fit=crop&w=400&q=80' },
            { id: 311, category: 'ayam', catName: 'AYAM', name: 'Nasi Ayam Maranggi', price: 16000, desc: 'Ayam bumbu maranggi manis gurih ketumbar rempah dengan sambal tomat.', image: 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?auto=format&fit=crop&w=400&q=80' },

            // TELOR
            { id: 401, category: 'telor', catName: 'TELOR', name: 'Nasi Telor Pontianak', price: 15000, badge: 'Favorit', desc: 'Telur ceplok mata sapi disiram saus kecap bawang putih khas Pontianak.', image: 'https://images.unsplash.com/photo-1525351484163-7529414344d8?auto=format&fit=crop&w=400&q=80' },
            { id: 402, category: 'telor', catName: 'TELOR', name: 'Nasi Telor Kribo', price: 15000, desc: 'Telur dadar bersarang super mekar krispi garing gurih di setiap gigitan.', image: 'https://images.unsplash.com/photo-1510693206972-df098062cb71?auto=format&fit=crop&w=400&q=80' },

            // INDOMIE
            { id: 501, category: 'indomie', catName: 'INDOMIE', name: 'Indomie Bangladesh', price: 15000, badge: 'Viral', desc: 'Indomie bumbu rempah kari pekat khas Aceh Medan yang creamy.', image: 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=400&q=80' },
            { id: 502, category: 'indomie', catName: 'INDOMIE', name: 'Indomie Goreng', price: 8000, desc: 'Indomie goreng legendaris dimasak pas kenyal dengan bawang goreng.', image: 'https://images.unsplash.com/photo-1585032226651-759b368d7246?auto=format&fit=crop&w=400&q=80' },
            { id: 503, category: 'indomie', catName: 'INDOMIE', name: 'Indomie Rebus', price: 8000, desc: 'Indomie kuah hangat rasa ayam bawang klasik dengan sawi hijau.', image: 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=400&q=80' },
            { id: 504, category: 'indomie', catName: 'INDOMIE', name: 'Indomie Chili Oil', price: 10000, desc: 'Indomie dipadu racikan chili oil pedas gurih wangi bawang aromatik.', image: 'https://images.unsplash.com/photo-1585032226651-759b368d7246?auto=format&fit=crop&w=400&q=80' },
            { id: 505, category: 'indomie', catName: 'INDOMIE', name: 'Indomie Soto', price: 8000, desc: 'Indomie kuah rasa soto dengan aroma koya dan perasan jeruk nipis gurih.', image: 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=400&q=80' },
            { id: 506, category: 'indomie', catName: 'INDOMIE', name: 'Mie Sedaap Kari Spesial', price: 8000, desc: 'Mie kuah kari kental gurih nikmat dengan bumbu kari pekat.', image: 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=400&q=80' },
            { id: 507, category: 'indomie', catName: 'INDOMIE', name: 'Indomie Rendang', price: 8000, desc: 'Indomie goreng bumbu rendang Padang kaya rempah yang nikmat.', image: 'https://images.unsplash.com/photo-1585032226651-759b368d7246?auto=format&fit=crop&w=400&q=80' },

            // KULIT
            { id: 601, category: 'kulit', catName: 'KULIT', name: 'Nasi Kulit Sambal Matah', price: 14000, desc: 'Kulit ayam goreng super crispy disajikan sambal matah kecombrang fresh.', image: 'https://images.unsplash.com/photo-1562967914-608f82629710?auto=format&fit=crop&w=400&q=80' },
            { id: 602, category: 'kulit', catName: 'KULIT', name: 'Nasi Kulit Cabe Garam', price: 15000, badge: 'Favorit', desc: 'Kulit ayam renyah ditumis bumbu cabai garam gurih pedas harum bawang.', image: 'https://images.unsplash.com/photo-1562967914-608f82629710?auto=format&fit=crop&w=400&q=80' },
            { id: 603, category: 'kulit', catName: 'KULIT', name: 'Nasi Kulit Gongso', price: 16000, desc: 'Kulit ayam dimasak bumbu gongso pedas manis dengan aroma kecap karamel.', image: 'https://images.unsplash.com/photo-1569058242253-92a9c755a0ec?auto=format&fit=crop&w=400&q=80' },

            // SNACK
            { id: 701, category: 'snack', catName: 'SNACK', name: 'Mendoan', price: 12000, desc: 'Tempe mendoan lembut digoreng tepung daun bawang dicocol kecap pedas.', image: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=400&q=80' },
            { id: 702, category: 'snack', catName: 'SNACK', name: 'Chicken Wings', price: 18000, desc: 'Sayap ayam crispy berbumbu gurih garing di luar juicy di dalam.', image: 'https://images.unsplash.com/photo-1527477378698-4c8d50f83733?auto=format&fit=crop&w=400&q=80' },
            { id: 703, category: 'snack', catName: 'SNACK', name: 'French Fries', price: 14000, desc: 'Kentang goreng garing bertabur seasoning gurih dengan saus cocolan.', image: 'https://images.unsplash.com/photo-1576107232684-1279f3908594?auto=format&fit=crop&w=400&q=80' },
            { id: 704, category: 'snack', catName: 'SNACK', name: 'Mix Platter', price: 16000, desc: 'Kombinasi sosis, nugget, dan kentang goreng cocok untuk camilan bareng.', image: 'https://images.unsplash.com/photo-1541592106381-b31e9677c0e5?auto=format&fit=crop&w=400&q=80' },
            { id: 705, category: 'snack', catName: 'SNACK', name: 'Cheesy Fries', price: 17000, desc: 'Kentang goreng renyah disiram saus keju cheddar lumer yang gurih creamy.', image: 'https://images.unsplash.com/photo-1586190848861-99aa4a171e90?auto=format&fit=crop&w=400&q=80' },
            { id: 706, category: 'snack', catName: 'SNACK', name: 'Tahu Cabe Garam', price: 15000, badge: 'Favorit', desc: 'Tahu sutra crispy dipadu tumisan cabe rawit merah dan bawang putih renyah.', image: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=400&q=80' },

            // PANCONG
            { id: 801, category: 'pancong', catName: 'PANCONG', name: 'Pancong Original', price: 7000, desc: 'Kue pancong lumer adonan wangi mentega khas Parjo bisa ditambah topping.', image: 'https://images.unsplash.com/photo-1587314168485-3236d6710814?auto=format&fit=crop&w=400&q=80' },
            { id: 802, category: 'pancong', catName: 'PANCONG', name: 'Pancong Chocomaltine', price: 13000, badge: 'Best Seller', desc: 'Kue pancong lumer hangat dengan selai chocomaltine crunchy cokelat.', image: 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=400&q=80' },
            { id: 803, category: 'pancong', catName: 'PANCONG', name: 'Pancong Green Tea', price: 12000, desc: 'Pancong lembut dengan lelehan selai green tea matcha manis aromatik.', image: 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=400&q=80' },
            { id: 804, category: 'pancong', catName: 'PANCONG', name: 'Pancong Biscoff', price: 14000, desc: 'Pancong lumer dengan lumuran selai lotus biscoff karamel rempah.', image: 'https://images.unsplash.com/photo-1587314168485-3236d6710814?auto=format&fit=crop&w=400&q=80' },
            { id: 805, category: 'pancong', catName: 'PANCONG', name: 'Pancong Tiramisu', price: 13000, desc: 'Kue pancong lumer dipadu saus tiramisu khas kopi lembut Italia.', image: 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=400&q=80' },
            { id: 806, category: 'pancong', catName: 'PANCONG', name: 'Pancong Nutella', price: 14000, badge: 'Favorit', desc: 'Kue pancong lumer dengan selai hazelnut cokelat Nutella melimpah.', image: 'https://images.unsplash.com/photo-1587314168485-3236d6710814?auto=format&fit=crop&w=400&q=80' },
            { id: 807, category: 'pancong', catName: 'PANCONG', name: 'Pancong Ice Cream', price: 14000, desc: 'Pancong hangat lumer disajikan dengan satu scoop es krim vanilla.', image: 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=400&q=80' },

            // COFFEE & BEER
            { id: 901, category: 'coffee', catName: 'COFFEE & BEER', name: 'Butter Beer Parjo', price: 13000, badge: 'Viral 13K', desc: 'Minuman andalan Parjo! Soda sarsaparila segar dengan busa foam butter cream manis lembut.', image: 'https://images.unsplash.com/photo-1544145945-f90425340c7e?auto=format&fit=crop&w=400&q=80' },
            { id: 902, category: 'coffee', catName: 'COFFEE & BEER', name: 'Coffee Beer', price: 16000, desc: 'Minuman kopi bersoda legendaris Jawa Timur yang segar tanpa alkohol (halal).', image: 'https://images.unsplash.com/photo-1544145945-f90425340c7e?auto=format&fit=crop&w=400&q=80' },
            { id: 903, category: 'coffee', catName: 'COFFEE & BEER', name: 'Sarsaparila', price: 16000, desc: 'Soda sarsaparila klasik dingin yang menyegarkan dahaga saat nugas.', image: 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?auto=format&fit=crop&w=400&q=80' },
            { id: 904, category: 'coffee', catName: 'COFFEE & BEER', name: 'Kopi Susu Parjo', price: 13000, desc: 'Espresso blend dipadu susu kental manis dan susu segar pas.', image: 'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=400&q=80' },
            { id: 905, category: 'coffee', catName: 'COFFEE & BEER', name: 'Kopi Susu Sea Salted', price: 14000, badge: 'Best Seller', desc: 'Kopi susu creamy berpadu krim sea salt gurih lembut di lapisan atas.', image: 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=400&q=80' },
            { id: 906, category: 'coffee', catName: 'COFFEE & BEER', name: 'Kopi Susu Alpenliebe', price: 15000, desc: 'Kopi susu dengan sirup karamel Alpenliebe manis legit dan harum.', image: 'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=400&q=80' },
            { id: 907, category: 'coffee', catName: 'COFFEE & BEER', name: 'Coffee Milo', price: 14000, desc: 'Kombinasi mantap bubuk cokelat malt Milo berpadu shot kopi espresso segar.', image: 'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=400&q=80' },
            { id: 908, category: 'coffee', catName: 'COFFEE & BEER', name: 'Kopi Susu Butterscotch', price: 14000, desc: 'Kopi susu dengan sentuhan aroma manis gurih sirup butterscotch.', image: 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=400&q=80' },

            // TEA
            { id: 1001, category: 'tea', catName: 'TEA', name: 'Lychee Tea', price: 10000, desc: 'Teh melati seduh segar dengan rasa buah leci manis menyegarkan.', image: 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=400&q=80' },
            { id: 1002, category: 'tea', catName: 'TEA', name: 'Strawberry Tea', price: 9000, desc: 'Es teh dengan sirup stroberi segar dan rasa asam manis pas.', image: 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=400&q=80' },
            { id: 1003, category: 'tea', catName: 'TEA', name: 'Teh Gentong', price: 6000, badge: 'Ginastel', desc: 'Teh seduh gentong tradisional wangi melati pekat ginastel manis alami.', image: 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=400&q=80' },
            { id: 1004, category: 'tea', catName: 'TEA', name: 'Lemon Tea', price: 8000, desc: 'Teh segar dengan perasan jeruk lemon asli kaya vitamin C.', image: 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=400&q=80' },
            { id: 1005, category: 'tea', catName: 'TEA', name: 'Wintermelon Tea', price: 8000, desc: 'Teh labu kundur manis dingin pereda panas dalam.', image: 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=400&q=80' },
            { id: 1006, category: 'tea', catName: 'TEA', name: 'Wintermelon Milk Tea', price: 10000, desc: 'Milk tea berpadu aroma manis harum teh kundur wintermelon.', image: 'https://images.unsplash.com/photo-1558857563-b37fe878c764?auto=format&fit=crop&w=400&q=80' },
            { id: 1007, category: 'tea', catName: 'TEA', name: 'Honey Lemon Tea', price: 8000, desc: 'Seduhan teh lemon dipadu madu murni alami segar melegakan.', image: 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=400&q=80' },
            { id: 1008, category: 'tea', catName: 'TEA', name: 'Butterscotch Milk Tea', price: 13000, desc: 'Susu teh berpadu sirup butterscotch creamy gurih manis.', image: 'https://images.unsplash.com/photo-1558857563-b37fe878c764?auto=format&fit=crop&w=400&q=80' },
            { id: 1009, category: 'tea', catName: 'TEA', name: 'Strawberry Milk Tea', price: 10000, badge: 'Best Seller', desc: 'Minuman stroberi segar berpadu susu lembut creamy yang nagih.', image: 'https://images.unsplash.com/photo-1558857563-b37fe878c764?auto=format&fit=crop&w=400&q=80' },

            // SACHET & BLENDED
            { id: 1101, category: 'sachet', catName: 'SACHET', name: 'Milo Dino', price: 13000, badge: 'Favorit', desc: 'Es susu Milo dingin dengan taburan bubuk cokelat Milo pekat menggunung.', image: 'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=400&q=80' },
            { id: 1102, category: 'sachet', catName: 'SACHET', name: 'Chocolatos Coklat / Matcha', price: 8000, desc: 'Minuman cokelat kental Chocolatos nikmat disajikan dingin atau hangat.', image: 'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=400&q=80' },
            { id: 1103, category: 'sachet', catName: 'SACHET', name: 'Nutrisari All Variant', price: 6000, desc: 'Es Nutrisari segar aneka varian rasa jeruk peras, mangga, atau blewah.', image: 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?auto=format&fit=crop&w=400&q=80' },
            { id: 1104, category: 'sachet', catName: 'SACHET', name: 'Teh Tarik', price: 7000, desc: 'Teh tarik khas melayu dengan busa lembut dan rasa manis creamy.', image: 'https://images.unsplash.com/photo-1558857563-b37fe878c764?auto=format&fit=crop&w=400&q=80' },
            { id: 1105, category: 'sachet', catName: 'SACHET', name: 'Dancow Putih / Coklat', price: 8000, desc: 'Susu bubuk Dancow full cream diseduh manis kental hangat atau es dingin.', image: 'https://images.unsplash.com/photo-1558857563-b37fe878c764?auto=format&fit=crop&w=400&q=80' },
            { id: 1106, category: 'sachet', catName: 'SACHET', name: 'Josua (Extra Joss Susu)', price: 9000, badge: 'Legend', desc: 'Minuman stamina Extra Joss disatukan dengan susu kental manis creamy.', image: 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?auto=format&fit=crop&w=400&q=80' },
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
                    const tabBtn = document.getElementById('tab-btn-' + current);
                    if (tabBtn) {
                        tabBtn.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                    }
                }
            }, { passive: true });
        },

        scrollToCategory(catId) {
            this.activeCategoryId = catId;
            const tabBtn = document.getElementById('tab-btn-' + catId);
            if (tabBtn) {
                tabBtn.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
            }
            const targetEl = document.getElementById('cat-section-' + catId);
            if (targetEl) {
                const headerOffset = 115;
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
                                    item.desc.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                                    item.catName.toLowerCase().includes(this.searchQuery.toLowerCase());
                return matchCat && matchSearch;
            });
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

        appendNote(text) {
            if (this.modalNotes.length > 0) {
                this.modalNotes += ', ' + text;
            } else {
                this.modalNotes = text;
            }
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
            const existing = this.cart.find(c => c.name === 'Topping: ' + addon.name);
            if (existing) {
                existing.qty += 1;
            } else {
                this.cart.push({
                    cartId: Date.now() + Math.random(),
                    id: 99000 + Math.floor(Math.random() * 1000),
                    name: 'Topping: ' + addon.name,
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
            this.activeNoteItem = item;
            this.tempItemNote = item.notes || '';
            this.showNoteModal = true;
        },

        saveItemNote() {
            if (this.activeNoteItem) {
                this.activeNoteItem.notes = this.tempItemNote.trim();
            }
            this.showNoteModal = false;
        },

        clearCartConfirm() {
            this.showConfirmClearModal = true;
        },

        executeClearCart() {
            this.cart = [];
            this.showConfirmClearModal = false;
            this.goToView('catalog');
        },

        goToView(view) {
            this.currentView = view;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        editTablePrompt() {
            this.tempTableNumber = this.tableNumber;
            this.showTableModal = true;
        },

        saveTableNumber() {
            const val = parseInt(this.tempTableNumber);
            if (!isNaN(val) && val > 0) {
                this.tableNumber = val;
            }
            this.showTableModal = false;
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

            const now = new Date();
            const day = now.getDate();
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const month = months[now.getMonth()];
            const year = now.getFullYear();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            
            this.orderDateStr = `${day} ${month} ${year}, ${hours}:${minutes}`;
            this.lastOrderId = 'PRJ' + Math.random().toString(36).substring(2, 9).toUpperCase();
            this.confirmedCart = JSON.parse(JSON.stringify(this.cart));
            this.confirmedTotal = this.finalTotal;
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
