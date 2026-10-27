<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PARJO Cashier & Operations Hub â€” Dashboard Kasir</title>

    <!-- Google Fonts -->
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
                        parjo: {
                            orange: '#FF4800',
                            darkorange: '#D93D00',
                            green: '#00583E',
                            darkgreen: '#003F2C',
                            yellow: '#EEFD37',
                            ink: '#1C1917',
                            surface: '#FDFBF7',
                            border: '#E7E5E4'
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace']
                    },
                    boxShadow: {
                        'hard': '4px 4px 0px 0px #1C1917',
                        'hard-sm': '2px 2px 0px 0px #1C1917',
                        'hard-orange': '3px 3px 0px 0px #B83200',
                        'subtle': '0 4px 20px -2px rgba(0,0,0,0.05)',
                        'float': '0 12px 32px -4px rgba(0,0,0,0.12)'
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>

    <!-- Chart.js for Business Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F8F7F4;
            color: #1C1917;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #F1EFEA;
        }
        ::-webkit-scrollbar-thumb {
            background: #D6D3CD;
            border-radius: 999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #FF4800;
        }

        /* Print Thermal Receipt styles */
        @media print {
            body * {
                visibility: hidden;
            }
            #printable-receipt, #printable-receipt * {
                visibility: visible;
            }
            #printable-receipt {
                position: absolute;
                left: 0;
                top: 0;
                width: 78mm;
                margin: 0;
                padding: 8px;
                background: white !important;
                color: black !important;
                box-shadow: none !important;
                border: none !important;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body x-data="cashierDashboard()" x-init="initDashboard()" class="min-h-screen flex flex-col">

    <!-- ========================================================= -->
    <!-- MASTER TOP NAVIGATION BAR -->
    <!-- ========================================================= -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-stone-200 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <!-- Left: Branding & Tagline -->
                <div class="flex items-center space-x-3.5">
                    <div class="w-10 h-10 rounded-xl bg-parjo-orange text-white flex items-center justify-center font-black text-xl shadow-hard-sm border-2 border-parjo-ink flex-shrink-0">
                        <span>P</span>
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="font-black text-lg tracking-tight text-parjo-ink">PARJO</span>
                            <span class="bg-parjo-yellow text-parjo-darkgreen text-[10px] font-black uppercase px-2 py-0.5 rounded border border-parjo-darkgreen/30 tracking-wider">KASIR & ADMIN</span>
                        </div>
                        <p class="text-[11px] text-stone-500 font-medium">Pancong & Burjo â€¢ Operations Hub</p>
                    </div>
                </div>

                <!-- Center: Navigation Tabs -->
                <nav class="hidden md:flex items-center p-1 bg-stone-100 rounded-xl border border-stone-200">
                    <button @click="switchTab('orders')"
                            :class="activeTab === 'orders' ? 'bg-white text-parjo-ink font-bold shadow-xs' : 'text-stone-600 hover:text-stone-900 font-medium'"
                            class="px-4 py-2 rounded-lg text-xs flex items-center space-x-2 transition">
                        <i class="fa-solid fa-bell text-parjo-orange"></i>
                        <span>Pesanan Masuk</span>
                        <span x-show="counts.pending > 0" x-text="counts.pending" class="px-1.5 py-0.2 text-[10px] font-black rounded-full bg-parjo-orange text-white animate-pulse"></span>
                    </button>
                    <button @click="switchTab('analytics')"
                            :class="activeTab === 'analytics' ? 'bg-white text-parjo-ink font-bold shadow-xs' : 'text-stone-600 hover:text-stone-900 font-medium'"
                            class="px-4 py-2 rounded-lg text-xs flex items-center space-x-2 transition">
                        <i class="fa-solid fa-chart-pie text-parjo-green"></i>
                        <span>Analisis & Laporan</span>
                    </button>
                    <button @click="switchTab('manual-pos')"
                            :class="activeTab === 'manual-pos' ? 'bg-white text-parjo-ink font-bold shadow-xs' : 'text-stone-600 hover:text-stone-900 font-medium'"
                            class="px-4 py-2 rounded-lg text-xs flex items-center space-x-2 transition">
                        <i class="fa-solid fa-cash-register text-stone-700"></i>
                        <span>Input POS Kasir</span>
                    </button>
                </nav>

                <!-- Right: Live Indicator, Sound Toggle & Self-Order Link -->
                <div class="flex items-center space-x-2.5">
                    
                    <!-- Live Sync Indicator -->
                    <div class="hidden lg:flex items-center space-x-2 px-3 py-1.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                        </span>
                        <span>Live Sync (3s)</span>
                    </div>

                    <!-- Audio Alert Toggle Button -->
                    <button @click="toggleSound()" 
                            :title="soundEnabled ? 'Matikan Notifikasi Suara' : 'Nyalakan Notifikasi Suara'"
                            class="p-2 rounded-xl border border-stone-200 bg-white hover:bg-stone-50 transition text-xs flex items-center space-x-1.5"
                            :class="soundEnabled ? 'text-parjo-orange font-bold' : 'text-stone-400'">
                        <i class="fa-solid" :class="soundEnabled ? 'fa-volume-high' : 'fa-volume-xmark'"></i>
                        <span class="hidden sm:inline text-[11px]" x-text="soundEnabled ? 'Suara ON' : 'Muted'"></span>
                    </button>

                    <!-- Refresh Button -->
                    <button @click="fetchOrders(true)" class="p-2 w-9 h-9 rounded-xl border border-stone-200 bg-white hover:bg-stone-50 transition text-stone-700 flex items-center justify-center text-xs" title="Refresh Sekarang">
                        <i class="fa-solid fa-arrows-rotate" :class="{ 'fa-spin': isRefreshing }"></i>
                    </button>

                    <!-- Customer Self-Ordering Link -->
                    <a href="{{ route('home') }}" target="_blank" class="h-9 px-3 rounded-xl bg-parjo-ink text-white hover:bg-stone-800 transition text-xs font-bold flex items-center space-x-1.5 shadow-sm">
                        <i class="fa-solid fa-mobile-screen text-xs text-parjo-yellow"></i>
                        <span class="hidden sm:inline">Menu Pelanggan</span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-stone-400"></i>
                    </a>
                </div>
            </div>

            <!-- Mobile Navigation Bar (visible on small screens) -->
            <div class="flex md:hidden border-t border-stone-100 py-2 space-x-1 overflow-x-auto">
                <button @click="switchTab('orders')"
                        :class="activeTab === 'orders' ? 'bg-parjo-ink text-white font-bold' : 'bg-stone-100 text-stone-700'"
                        class="px-3 py-1.5 rounded-lg text-xs whitespace-nowrap flex items-center space-x-1.5">
                    <i class="fa-solid fa-bell text-parjo-yellow"></i>
                    <span>Pesanan Masuk</span>
                    <span x-show="counts.pending > 0" x-text="counts.pending" class="px-1.5 py-0.2 text-[9px] font-bold rounded-full bg-parjo-orange text-white"></span>
                </button>
                <button @click="switchTab('analytics')"
                        :class="activeTab === 'analytics' ? 'bg-parjo-ink text-white font-bold' : 'bg-stone-100 text-stone-700'"
                        class="px-3 py-1.5 rounded-lg text-xs whitespace-nowrap flex items-center space-x-1.5">
                    <i class="fa-solid fa-chart-pie text-emerald-400"></i>
                    <span>Analisis</span>
                </button>
                <button @click="switchTab('manual-pos')"
                        :class="activeTab === 'manual-pos' ? 'bg-parjo-ink text-white font-bold' : 'bg-stone-100 text-stone-700'"
                        class="px-3 py-1.5 rounded-lg text-xs whitespace-nowrap flex items-center space-x-1.5">
                    <i class="fa-solid fa-cash-register"></i>
                    <span>Input POS</span>
                </button>
            </div>
        </div>
    </header>

    <!-- ========================================================= -->
    <!-- NEW INCOMING ORDER TOAST ALERT -->
    <!-- ========================================================= -->
    <div x-show="newOrderAlert" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-6 right-6 z-50 max-w-md w-full bg-parjo-ink text-white rounded-2xl shadow-float border-2 border-parjo-orange p-4 flex items-center justify-between space-x-3">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-parjo-orange text-white flex items-center justify-center font-black text-lg animate-bounce flex-shrink-0">
                <i class="fa-solid fa-bell"></i>
            </div>
            <div>
                <p class="text-[11px] font-extrabold uppercase tracking-wider text-parjo-yellow">PESANAN BARU MASUK!</p>
                <h4 class="text-sm font-bold" x-text="newOrderAlert ? `${newOrderAlert.code} â€¢ ${newOrderAlert.customer}` : ''"></h4>
                <p class="text-xs text-stone-300" x-text="newOrderAlert ? (newOrderAlert.table ? `Meja #${newOrderAlert.table} â€¢ ${formatRupiah(newOrderAlert.total)}` : `Bawa Pulang â€¢ ${formatRupiah(newOrderAlert.total)}`) : ''"></p>
            </div>
        </div>
        <button @click="dismissAlert()" class="w-8 h-8 rounded-lg bg-stone-800 text-stone-400 hover:text-white flex items-center justify-center transition">
            <i class="fa-solid fa-xmark text-xs"></i>
        </button>
    </div>

    <!-- ========================================================= -->
    <!-- MAIN CONTENT CONTAINER -->
    <!-- ========================================================= -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">

        <!-- ===================================================== -->
        <!-- TAB 1: LIVE ORDERS DASHBOARD -->
        <!-- ===================================================== -->
        <div x-show="activeTab === 'orders'" class="space-y-6">

            <!-- KPI Summary Cards (Hari Ini) -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3.5">
                
                <!-- Pending Card -->
                <div @click="statusFilter = 'pending'" class="bg-white p-4 rounded-2xl border-2 cursor-pointer transition hover:scale-[1.01]"
                     :class="statusFilter === 'pending' ? 'border-amber-500 shadow-hard-sm' : 'border-stone-200'">
                    <div class="flex items-center justify-between text-stone-500 text-xs font-bold uppercase tracking-wider">
                        <span>Menunggu</span>
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500" :class="{ 'animate-ping': counts.pending > 0 }"></span>
                    </div>
                    <div class="mt-2 flex items-baseline justify-between">
                        <span class="text-2xl sm:text-3xl font-black text-amber-600" x-text="counts.pending">0</span>
                        <span class="text-[11px] font-semibold text-stone-400">Perlu Konfirmasi</span>
                    </div>
                </div>

                <!-- Processing Card -->
                <div @click="statusFilter = 'processing'" class="bg-white p-4 rounded-2xl border-2 cursor-pointer transition hover:scale-[1.01]"
                     :class="statusFilter === 'processing' ? 'border-blue-500 shadow-hard-sm' : 'border-stone-200'">
                    <div class="flex items-center justify-between text-stone-500 text-xs font-bold uppercase tracking-wider">
                        <span>Diproses</span>
                        <i class="fa-solid fa-kitchen-set text-blue-500 text-xs"></i>
                    </div>
                    <div class="mt-2 flex items-baseline justify-between">
                        <span class="text-2xl sm:text-3xl font-black text-blue-600" x-text="counts.processing">0</span>
                        <span class="text-[11px] font-semibold text-stone-400">Dapur / Bar</span>
                    </div>
                </div>

                <!-- Completed Card -->
                <div @click="statusFilter = 'completed'" class="bg-white p-4 rounded-2xl border-2 cursor-pointer transition hover:scale-[1.01]"
                     :class="statusFilter === 'completed' ? 'border-emerald-500 shadow-hard-sm' : 'border-stone-200'">
                    <div class="flex items-center justify-between text-stone-500 text-xs font-bold uppercase tracking-wider">
                        <span>Selesai</span>
                        <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                    </div>
                    <div class="mt-2 flex items-baseline justify-between">
                        <span class="text-2xl sm:text-3xl font-black text-emerald-600" x-text="counts.completed">0</span>
                        <span class="text-[11px] font-semibold text-stone-400">Tersaji / Selesai</span>
                    </div>
                </div>

                <!-- Omzet Hari Ini Card -->
                <div class="bg-white p-4 rounded-2xl border-2 border-stone-200">
                    <div class="flex items-center justify-between text-stone-500 text-xs font-bold uppercase tracking-wider">
                        <span>Omzet Hari Ini</span>
                        <i class="fa-solid fa-coins text-parjo-orange text-xs"></i>
                    </div>
                    <div class="mt-2 flex items-baseline justify-between">
                        <span class="text-xl sm:text-2xl font-black text-parjo-ink" x-text="formatRupiah(todayRevenue)">Rp 0</span>
                        <span class="text-[11px] font-semibold text-stone-400" x-text="counts.all + ' Pesanan'"></span>
                    </div>
                </div>
            </div>

            <!-- Filter & Action Toolbar -->
            <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-subtle space-y-3.5">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                    
                    <!-- Search Input -->
                    <div class="relative flex-1 max-w-md">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-xs"></i>
                        <input type="text" 
                               x-model="searchQuery" 
                               @input.debounce.300ms="fetchOrders()"
                               placeholder="Cari Order ID, Nama, No Meja, No WhatsApp..." 
                               class="w-full pl-9 pr-8 py-2 bg-stone-50 border border-stone-200 rounded-xl text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-parjo-orange transition">
                        <button x-show="searchQuery" @click="searchQuery = ''; fetchOrders()" class="absolute right-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-600">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </div>

                    <!-- Right Controls: Order Type, Date Filter, Export CSV -->
                    <div class="flex flex-wrap items-center gap-2">
                        
                        <!-- Order Type Filter -->
                        <select x-model="orderTypeFilter" @change="fetchOrders()" class="px-3 py-2 bg-stone-50 border border-stone-200 rounded-xl text-xs font-semibold text-stone-700 focus:outline-none focus:ring-2 focus:ring-parjo-orange">
                            <option value="all">Semua Tipe</option>
                            <option value="dine-in">Makan di Tempat</option>
                            <option value="take-away">Bawa Pulang</option>
                        </select>

                        <!-- Date Filter -->
                        <select x-model="dateFilter" @change="fetchOrders()" class="px-3 py-2 bg-stone-50 border border-stone-200 rounded-xl text-xs font-semibold text-stone-700 focus:outline-none focus:ring-2 focus:ring-parjo-orange">
                            <option value="today">Hari Ini</option>
                            <option value="yesterday">Kemarin</option>
                            <option value="all">Semua Hari</option>
                        </select>

                        <!-- Export CSV Report -->
                        <a href="{{ route('orders.export') }}" class="px-3 py-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-800 text-xs font-bold transition flex items-center space-x-1.5 border border-stone-200" title="Download Laporan Excel (CSV)">
                            <i class="fa-solid fa-file-excel text-emerald-600 text-xs"></i>
                            <span class="hidden sm:inline">Ekspor CSV</span>
                        </a>
                    </div>
                </div>

                <!-- Status Filter Tabs -->
                <div class="flex items-center space-x-1.5 overflow-x-auto pt-1 border-t border-stone-100">
                    <button @click="statusFilter = 'all'; fetchOrders()" 
                            :class="statusFilter === 'all' ? 'bg-parjo-ink text-white font-bold' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 font-medium'"
                            class="px-3 py-1.5 rounded-lg text-xs transition flex items-center space-x-1.5 whitespace-nowrap">
                        <span>Semua</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="statusFilter === 'all' ? 'bg-stone-700 text-stone-200' : 'bg-stone-200 text-stone-600'" x-text="counts.all"></span>
                    </button>

                    <button @click="statusFilter = 'pending'; fetchOrders()" 
                            :class="statusFilter === 'pending' ? 'bg-amber-500 text-white font-bold' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 font-medium'"
                            class="px-3 py-1.5 rounded-lg text-xs transition flex items-center space-x-1.5 whitespace-nowrap">
                        <i class="fa-solid fa-bell text-[10px]"></i>
                        <span>Menunggu</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="statusFilter === 'pending' ? 'bg-amber-600 text-white' : 'bg-stone-200 text-stone-600'" x-text="counts.pending"></span>
                    </button>

                    <button @click="statusFilter = 'processing'; fetchOrders()" 
                            :class="statusFilter === 'processing' ? 'bg-blue-600 text-white font-bold' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 font-medium'"
                            class="px-3 py-1.5 rounded-lg text-xs transition flex items-center space-x-1.5 whitespace-nowrap">
                        <i class="fa-solid fa-kitchen-set text-[10px]"></i>
                        <span>Diproses</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="statusFilter === 'processing' ? 'bg-blue-700 text-white' : 'bg-stone-200 text-stone-600'" x-text="counts.processing"></span>
                    </button>

                    <button @click="statusFilter = 'completed'; fetchOrders()" 
                            :class="statusFilter === 'completed' ? 'bg-emerald-600 text-white font-bold' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 font-medium'"
                            class="px-3 py-1.5 rounded-lg text-xs transition flex items-center space-x-1.5 whitespace-nowrap">
                        <i class="fa-solid fa-circle-check text-[10px]"></i>
                        <span>Selesai</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="statusFilter === 'completed' ? 'bg-emerald-700 text-white' : 'bg-stone-200 text-stone-600'" x-text="counts.completed"></span>
                    </button>

                    <button @click="statusFilter = 'cancelled'; fetchOrders()" 
                            :class="statusFilter === 'cancelled' ? 'bg-rose-600 text-white font-bold' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 font-medium'"
                            class="px-3 py-1.5 rounded-lg text-xs transition flex items-center space-x-1.5 whitespace-nowrap">
                        <i class="fa-solid fa-ban text-[10px]"></i>
                        <span>Dibatalkan</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="statusFilter === 'cancelled' ? 'bg-rose-700 text-white' : 'bg-stone-200 text-stone-600'" x-text="counts.cancelled"></span>
                    </button>
                </div>
            </div>

            <!-- Orders Grid / List -->
            <div class="space-y-4">
                
                <!-- Loading State -->
                <div x-show="isLoading && orders.length === 0" class="py-16 text-center text-stone-400">
                    <i class="fa-solid fa-spinner fa-spin text-2xl text-parjo-orange mb-2"></i>
                    <p class="text-xs font-semibold">Memuat daftar pesanan...</p>
                </div>

                <!-- Empty State -->
                <div x-show="!isLoading && orders.length === 0" class="py-16 bg-white rounded-2xl border border-stone-200 text-center p-8 space-y-3">
                    <div class="w-16 h-16 rounded-full bg-stone-100 text-stone-400 flex items-center justify-center mx-auto text-2xl">
                        <i class="fa-solid fa-inbox"></i>
                    </div>
                    <h3 class="font-bold text-stone-800 text-sm">Belum Ada Pesanan Sesuai Filter</h3>
                    <p class="text-xs text-stone-500 max-w-sm mx-auto">Pesanan yang masuk dari aplikasi pelanggan akan otomatis muncul di sini secara real-time.</p>
                    <a href="{{ route('home') }}" target="_blank" class="inline-flex items-center space-x-1.5 px-4 py-2 rounded-xl bg-parjo-orange text-white text-xs font-bold hover:bg-parjo-darkorange transition shadow-hard-sm">
                        <i class="fa-solid fa-plus"></i>
                        <span>Buat Pesanan Percobaan</span>
                    </a>
                </div>

                <!-- Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <template x-for="order in orders" :key="order.id">
                        <div class="bg-white rounded-2xl border-2 transition hover:shadow-subtle flex flex-col justify-between overflow-hidden"
                             :class="{
                                 'border-amber-400 bg-amber-50/20 shadow-hard-sm': order.status === 'pending',
                                 'border-blue-300': order.status === 'processing',
                                 'border-stone-200': order.status === 'completed',
                                 'border-rose-200 opacity-75': order.status === 'cancelled'
                             }">
                            
                            <!-- Card Header -->
                            <div class="p-4 border-b border-stone-100 flex items-start justify-between bg-stone-50/50">
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <span class="font-black text-sm text-parjo-ink font-mono" x-text="order.order_code"></span>
                                        <span x-show="order.status === 'pending'" class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-amber-100 text-amber-800 border border-amber-300 animate-pulse">BARU</span>
                                    </div>
                                    <div class="flex items-center space-x-2 mt-1 text-[11px] text-stone-500 font-medium">
                                        <span x-text="order.created_at_time"></span>
                                        <span>â€¢</span>
                                        <span x-text="order.created_at_human"></span>
                                    </div>
                                </div>

                                <!-- Table or Takeaway Badge -->
                                <div>
                                    <span x-show="order.order_type === 'dine-in'" class="px-2.5 py-1 rounded-lg bg-parjo-green text-white text-xs font-black tracking-wide border border-parjo-darkgreen flex items-center space-x-1">
                                        <i class="fa-solid fa-utensils text-[10px]"></i>
                                        <span>MEJA #<span x-text="order.table_number"></span></span>
                                    </span>
                                    <span x-show="order.order_type === 'take-away'" class="px-2.5 py-1 rounded-lg bg-stone-800 text-white text-xs font-black tracking-wide flex items-center space-x-1">
                                        <i class="fa-solid fa-bag-shopping text-[10px] text-parjo-yellow"></i>
                                        <span>TAKE AWAY</span>
                                    </span>
                                </div>
                            </div>

                            <!-- Customer Info -->
                            <div class="px-4 py-2.5 bg-stone-50/80 border-b border-stone-100 flex items-center justify-between text-xs">
                                <div class="flex items-center space-x-2">
                                    <i class="fa-solid fa-user text-stone-400 text-[10px]"></i>
                                    <span class="font-bold text-stone-800" x-text="order.customer_name"></span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <a :href="'https://wa.me/' + cleanPhone(order.customer_phone)" target="_blank" class="text-emerald-700 hover:text-emerald-800 font-semibold flex items-center space-x-1 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                        <i class="fa-brands fa-whatsapp text-xs"></i>
                                        <span class="text-[11px]" x-text="order.customer_phone"></span>
                                    </a>
                                </div>
                            </div>

                            <!-- Items List -->
                            <div class="p-4 flex-1 space-y-2">
                                <div class="space-y-1.5">
                                    <template x-for="item in order.items" :key="item.id">
                                        <div class="flex items-start justify-between text-xs py-0.5">
                                            <div class="flex-1 pr-2">
                                                <div class="flex items-baseline space-x-1.5">
                                                    <span class="font-black text-parjo-orange" x-text="item.quantity + 'x'"></span>
                                                    <span class="font-medium text-stone-800" x-text="item.product_name"></span>
                                                </div>
                                                <p x-show="item.notes" class="text-[10px] text-stone-500 italic mt-0.5 pl-5 flex items-center space-x-1">
                                                    <i class="fa-solid fa-comment-dots text-[9px] text-stone-400"></i>
                                                    <span x-text="item.notes"></span>
                                                </p>
                                            </div>
                                            <span class="font-semibold text-stone-600 text-right whitespace-nowrap" x-text="formatRupiah(item.subtotal)"></span>
                                        </div>
                                    </template>
                                </div>

                                <!-- Order Notes (if any) -->
                                <div x-show="order.notes" class="mt-2 p-2 rounded-lg bg-amber-50/80 border border-amber-200 text-[11px] text-amber-900">
                                    <span class="font-bold">Catatan:</span> <span x-text="order.notes"></span>
                                </div>
                            </div>

                            <!-- Total & Payment Status -->
                            <div class="p-4 bg-stone-50 border-t border-stone-100 flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-stone-400 block leading-tight">Total Pesanan</span>
                                    <span class="font-black text-base text-parjo-ink" x-text="formatRupiah(order.total_price)"></span>
                                </div>
                                
                                <div class="text-right">
                                    <!-- Payment Status Pill (Clickable for Cashier toggle) -->
                                    <button @click="togglePayment(order)" 
                                            :class="order.payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-rose-100 text-rose-800 border-rose-300 animate-pulse'"
                                            class="px-2.5 py-1 rounded-lg text-[11px] font-black uppercase tracking-wider border flex items-center space-x-1 transition"
                                            title="Klik untuk ubah status pembayaran">
                                        <i class="fa-solid" :class="order.payment_status === 'paid' ? 'fa-check-circle' : 'fa-clock'"></i>
                                        <span x-text="order.payment_status === 'paid' ? 'LUNAS' : 'BELUM LUNAS'"></span>
                                    </button>
                                    <span class="text-[10px] text-stone-400 block mt-0.5 font-medium" x-text="order.payment_method"></span>
                                </div>
                            </div>

                            <!-- Action Buttons / Status Stepper -->
                            <div class="p-3 bg-white border-t border-stone-200 flex items-center justify-between gap-2">
                                
                                <!-- Left Utilities: Detail & Print Receipt -->
                                <div class="flex items-center space-x-1">
                                    <button @click="openOrderDetail(order)" class="w-8 h-8 rounded-lg bg-stone-100 hover:bg-stone-200 text-stone-700 flex items-center justify-center transition" title="Lihat Detail">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </button>
                                    <button @click="openPrintModal(order)" class="w-8 h-8 rounded-lg bg-stone-100 hover:bg-stone-200 text-stone-700 flex items-center justify-center transition" title="Cetak Struk Thermal">
                                        <i class="fa-solid fa-print text-xs"></i>
                                    </button>
                                </div>

                                <!-- Right: Main Status Action Button -->
                                <div class="flex items-center space-x-1.5">
                                    
                                    <!-- Pending -> Processing -->
                                    <template x-if="order.status === 'pending'">
                                        <div class="flex items-center space-x-1">
                                            <button @click="updateStatus(order, 'cancelled')" class="px-2.5 py-1.5 rounded-lg border border-stone-300 text-stone-600 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-300 text-xs font-bold transition">
                                                Tolak
                                            </button>
                                            <button @click="updateStatus(order, 'processing')" class="px-3.5 py-1.5 rounded-lg bg-parjo-orange hover:bg-parjo-darkorange text-white text-xs font-black shadow-hard-orange transition flex items-center space-x-1.5">
                                                <i class="fa-solid fa-kitchen-set text-xs"></i>
                                                <span>Proses Dapur</span>
                                            </button>
                                        </div>
                                    </template>

                                    <!-- Processing -> Completed -->
                                    <template x-if="order.status === 'processing'">
                                        <button @click="updateStatus(order, 'completed')" class="px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow-sm transition flex items-center space-x-1.5">
                                            <i class="fa-solid fa-check text-xs"></i>
                                            <span>Tandai Selesai</span>
                                        </button>
                                    </template>

                                    <!-- Completed -->
                                    <template x-if="order.status === 'completed'">
                                        <span class="px-2.5 py-1 rounded-lg bg-stone-100 text-stone-600 text-xs font-bold flex items-center space-x-1">
                                            <i class="fa-solid fa-circle-check text-emerald-500"></i>
                                            <span>Selesai</span>
                                        </span>
                                    </template>

                                    <!-- Cancelled -->
                                    <template x-if="order.status === 'cancelled'">
                                        <span class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-600 text-xs font-bold">
                                            Dibatalkan
                                        </span>
                                    </template>

                                </div>
                            </div>

                        </div>
                    </template>
                </div>

            </div>

        </div>

        <!-- ===================================================== -->
        <!-- TAB 2: BUSINESS ANALYTICS & INSIGHTS -->
        <!-- ===================================================== -->
        <div x-show="activeTab === 'analytics'" class="space-y-6">
            
            <!-- Analytics Period Selector Header -->
            <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-subtle flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="font-black text-lg text-parjo-ink">Analisis & Performa Penjualan</h2>
                    <p class="text-xs text-stone-500 font-medium">Laporan omzet, menu terlaris, pola jam sibuk, dan metrik bisnis PARJO.</p>
                </div>

                <div class="flex items-center space-x-2">
                    <div class="p-1 bg-stone-100 rounded-xl border border-stone-200 flex items-center">
                        <button @click="changeAnalyticsPeriod('today')" 
                                :class="analyticsPeriod === 'today' ? 'bg-white font-bold text-parjo-ink shadow-xs' : 'text-stone-600'" 
                                class="px-3 py-1.5 rounded-lg text-xs transition">Hari Ini</button>
                        <button @click="changeAnalyticsPeriod('7days')" 
                                :class="analyticsPeriod === '7days' ? 'bg-white font-bold text-parjo-ink shadow-xs' : 'text-stone-600'" 
                                class="px-3 py-1.5 rounded-lg text-xs transition">7 Hari</button>
                        <button @click="changeAnalyticsPeriod('month')" 
                                :class="analyticsPeriod === 'month' ? 'bg-white font-bold text-parjo-ink shadow-xs' : 'text-stone-600'" 
                                class="px-3 py-1.5 rounded-lg text-xs transition">Bulan Ini</button>
                        <button @click="changeAnalyticsPeriod('all')" 
                                :class="analyticsPeriod === 'all' ? 'bg-white font-bold text-parjo-ink shadow-xs' : 'text-stone-600'" 
                                class="px-3 py-1.5 rounded-lg text-xs transition">Semua</button>
                    </div>

                    <a href="{{ route('orders.export') }}" class="px-3.5 py-2 rounded-xl bg-parjo-ink text-white hover:bg-stone-800 text-xs font-bold transition flex items-center space-x-1.5 shadow-sm">
                        <i class="fa-solid fa-download text-xs text-parjo-yellow"></i>
                        <span>Unduh CSV</span>
                    </a>
                </div>
            </div>

            <!-- Analytics Key Metrics Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- Total Revenue -->
                <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-subtle">
                    <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-stone-400">
                        <span>Total Omzet Bersih</span>
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <i class="fa-solid fa-sack-dollar text-sm"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <h3 class="text-2xl sm:text-3xl font-black text-parjo-ink" x-text="formatRupiah(analyticsData?.summary?.total_revenue || 0)">Rp 0</h3>
                        <p class="text-[11px] text-emerald-600 font-semibold mt-1 flex items-center gap-1">
                            <i class="fa-solid fa-arrow-trend-up"></i>
                            <span x-text="analyticsData?.period_label || 'Periode Terpilih'"></span>
                        </p>
                    </div>
                </div>

                <!-- Total Orders -->
                <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-subtle">
                    <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-stone-400">
                        <span>Total Transaksi</span>
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                            <i class="fa-solid fa-receipt text-sm"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <h3 class="text-2xl sm:text-3xl font-black text-blue-600" x-text="analyticsData?.summary?.total_orders || 0">0</h3>
                        <p class="text-[11px] text-stone-500 font-medium mt-1">
                            <span class="font-bold text-emerald-600" x-text="analyticsData?.summary?.completed_orders || 0"></span> selesai â€¢ 
                            <span class="text-rose-500" x-text="analyticsData?.summary?.cancelled_orders || 0"></span> batal
                        </p>
                    </div>
                </div>

                <!-- Average Order Value (AOV) -->
                <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-subtle">
                    <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-stone-400">
                        <span>Rata-rata Transaksi (AOV)</span>
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                            <i class="fa-solid fa-calculator text-sm"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <h3 class="text-2xl sm:text-3xl font-black text-parjo-ink" x-text="formatRupiah(analyticsData?.summary?.average_order_value || 0)">Rp 0</h3>
                        <p class="text-[11px] text-stone-500 font-medium mt-1">Rata-rata belanja per tamu</p>
                    </div>
                </div>

                <!-- Order Success Rate -->
                <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-subtle">
                    <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-stone-400">
                        <span>Success Rate</span>
                        <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                            <i class="fa-solid fa-chart-line text-sm"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <h3 class="text-2xl sm:text-3xl font-black text-purple-700" x-text="(analyticsData?.summary?.success_rate || 100) + '%'">100%</h3>
                        <p class="text-[11px] text-stone-500 font-medium mt-1">Tingkat keberhasilan order</p>
                    </div>
                </div>

            </div>

            <!-- Charts Row 1: Trend Line Chart & Distribution Doughnuts -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Main Trend Chart (2 Cols) -->
                <div class="lg:col-span-2 bg-white p-5 rounded-2xl border border-stone-200 shadow-subtle flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h4 class="font-black text-sm text-parjo-ink">Grafik Penjualan & Transaksi</h4>
                            <p class="text-xs text-stone-400 font-medium" x-text="analyticsData?.period_label"></p>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg bg-stone-100 text-stone-700 text-[11px] font-bold">Omzet & Pesanan</span>
                    </div>
                    <div class="h-64 relative">
                        <canvas id="trendChartCanvas"></canvas>
                    </div>
                </div>

                <!-- Order Type & Payment Distribution (1 Col) -->
                <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-subtle flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="font-black text-sm text-parjo-ink">Metode & Tipe Pesanan</h4>
                        <span class="text-xs text-stone-400 font-medium">Distribusi</span>
                    </div>
                    <div class="h-64 relative flex items-center justify-center">
                        <canvas id="paymentChartCanvas"></canvas>
                    </div>
                </div>

            </div>

            <!-- Charts Row 2: Top Selling Products & Category Breakdown -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Top 10 Best Sellers Table (2 Cols) -->
                <div class="lg:col-span-2 bg-white p-5 rounded-2xl border border-stone-200 shadow-subtle">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h4 class="font-black text-sm text-parjo-ink">Menu Terlaris (Top Selling Items)</h4>
                            <p class="text-xs text-stone-400 font-medium">Peringkat menu dengan volume penjualan tertinggi</p>
                        </div>
                        <span class="text-xs font-bold text-parjo-orange"><i class="fa-solid fa-trophy mr-1"></i>Paling Favorit</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left">
                            <thead class="text-[10px] text-stone-400 uppercase bg-stone-50/80 border-b border-stone-100">
                                <tr>
                                    <th class="py-2.5 px-3 font-bold">#</th>
                                    <th class="py-2.5 px-3 font-bold">Nama Menu</th>
                                    <th class="py-2.5 px-3 font-bold">Kategori</th>
                                    <th class="py-2.5 px-3 font-bold text-center">Terjual</th>
                                    <th class="py-2.5 px-3 font-bold text-right">Total Pendapatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100">
                                <template x-for="(prod, idx) in (analyticsData?.top_products || [])" :key="idx">
                                    <tr class="hover:bg-stone-50/60 transition">
                                        <td class="py-2.5 px-3 font-black" :class="{
                                            'text-amber-500': idx === 0,
                                            'text-stone-400': idx === 1,
                                            'text-amber-700': idx === 2,
                                            'text-stone-500': idx > 2
                                        }">
                                            <span x-show="idx === 0">ðŸ¥‡</span>
                                            <span x-show="idx === 1">ðŸ¥ˆ</span>
                                            <span x-show="idx === 2">ðŸ¥‰</span>
                                            <span x-show="idx > 2" x-text="idx + 1"></span>
                                        </td>
                                        <td class="py-2.5 px-3 font-bold text-stone-800" x-text="prod.product_name"></td>
                                        <td class="py-2.5 px-3">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-stone-100 text-stone-600" x-text="prod.category || 'General'"></span>
                                        </td>
                                        <td class="py-2.5 px-3 font-black text-center text-parjo-orange" x-text="prod.total_qty + ' porsi'"></td>
                                        <td class="py-2.5 px-3 font-bold text-right text-stone-900" x-text="formatRupiah(prod.total_sales)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Category Breakdown & Insights (1 Col) -->
                <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-subtle space-y-4">
                    <div>
                        <h4 class="font-black text-sm text-parjo-ink">Kontribusi Penjualan per Kategori</h4>
                        <p class="text-xs text-stone-400 font-medium">Berdasarkan total omzet</p>
                    </div>

                    <div class="space-y-3 pt-2">
                        <template x-for="cat in (analyticsData?.category_breakdown || [])" :key="cat.category">
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="font-bold text-stone-700" x-text="cat.category"></span>
                                    <span class="font-bold text-stone-900" x-text="formatRupiah(cat.total_sales)"></span>
                                </div>
                                <div class="w-full bg-stone-100 rounded-full h-2 overflow-hidden">
                                    <div class="bg-parjo-orange h-2 rounded-full" 
                                         :style="`width: ${Math.min(100, Math.round((cat.total_sales / (analyticsData?.summary?.total_revenue || 1)) * 100))}%`"></div>
                                </div>
                                <div class="flex justify-between text-[10px] text-stone-400 mt-0.5">
                                    <span x-text="cat.total_qty + ' item terjual'"></span>
                                    <span x-text="Math.round((cat.total_sales / (analyticsData?.summary?.total_revenue || 1)) * 100) + '%'"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

            </div>

        </div>

        <!-- ===================================================== -->
        <!-- TAB 3: POS INPUT KASIR MANUAL -->
        <!-- ===================================================== -->
        <div x-show="activeTab === 'manual-pos'" class="space-y-6">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Left 2 Cols: Menu Selection for Cashier -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-subtle flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h3 class="font-black text-base text-parjo-ink">Menu Kasir Walk-In</h3>
                            <p class="text-xs text-stone-400 font-medium">Pilih menu langsung untuk tamu yang memesan di meja kasir</p>
                        </div>
                        <input type="text" x-model="posSearch" placeholder="Cari menu POS..." class="px-3.5 py-1.5 bg-stone-50 border border-stone-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-parjo-orange">
                    </div>

                    <!-- Products Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <template x-for="prod in filteredPosProducts" :key="prod.id">
                            <div @click="addToPosCart(prod)" class="bg-white p-3 rounded-2xl border border-stone-200 hover:border-parjo-orange cursor-pointer transition shadow-xs flex flex-col justify-between hover:scale-[1.01]">
                                <div>
                                    <span class="text-[9px] font-black uppercase tracking-wider text-stone-400 block" x-text="prod.catName"></span>
                                    <h4 class="font-bold text-xs text-stone-800 mt-1 line-clamp-1" x-text="prod.name"></h4>
                                </div>
                                <div class="mt-3 flex items-center justify-between">
                                    <span class="font-black text-xs text-parjo-orange" x-text="formatRupiah(prod.price)"></span>
                                    <span class="w-6 h-6 rounded-lg bg-stone-100 hover:bg-parjo-orange hover:text-white flex items-center justify-center text-xs font-bold text-stone-600 transition">
                                        <i class="fa-solid fa-plus text-[10px]"></i>
                                    </span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Right 1 Col: Current POS Cart & Order Details -->
                <div class="bg-white p-5 rounded-2xl border-2 border-stone-200 shadow-subtle flex flex-col justify-between space-y-4">
                    <div>
                        <div class="flex items-center justify-between border-b border-stone-100 pb-3">
                            <h3 class="font-black text-sm text-parjo-ink">Ringkasan POS</h3>
                            <button @click="posCart = []" x-show="posCart.length > 0" class="text-[11px] text-rose-500 hover:underline font-bold">Kosongkan</button>
                        </div>

                        <!-- Customer Details Form -->
                        <div class="space-y-3 pt-3">
                            <div>
                                <label class="block text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-1">Nama Pemesan</label>
                                <input type="text" x-model="posCustomer.name" placeholder="Contoh: Mas Ryan" class="w-full px-3 py-1.5 bg-stone-50 border border-stone-200 rounded-xl text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-parjo-orange">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-1">WhatsApp</label>
                                    <input type="text" x-model="posCustomer.phone" placeholder="08..." class="w-full px-3 py-1.5 bg-stone-50 border border-stone-200 rounded-xl text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-parjo-orange">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-1">Tipe Pesanan</label>
                                    <select x-model="posOrderType" class="w-full px-3 py-1.5 bg-stone-50 border border-stone-200 rounded-xl text-xs font-bold focus:outline-none focus:ring-2 focus:ring-parjo-orange">
                                        <option value="dine-in">Makan di Tempat</option>
                                        <option value="take-away">Bawa Pulang</option>
                                    </select>
                                </div>
                            </div>
                            <div x-show="posOrderType === 'dine-in'">
                                <label class="block text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-1">Nomor Meja</label>
                                <input type="number" min="1" max="50" x-model.number="posTableNumber" placeholder="No Meja (1-50)" class="w-full px-3 py-1.5 bg-stone-50 border border-stone-200 rounded-xl text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-parjo-orange">
                            </div>
                        </div>

                        <!-- Cart Items List -->
                        <div class="mt-4 pt-3 border-t border-stone-100 max-h-56 overflow-y-auto space-y-2">
                            <div x-show="posCart.length === 0" class="text-center py-6 text-stone-400 text-xs">
                                Keranjang POS masih kosong.
                            </div>
                            <template x-for="item in posCart" :key="item.id">
                                <div class="flex items-center justify-between text-xs py-1">
                                    <div class="flex-1 pr-2">
                                        <p class="font-bold text-stone-800" x-text="item.name"></p>
                                        <p class="text-[10px] text-stone-400" x-text="formatRupiah(item.price)"></p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button @click="updatePosQty(item, -1)" class="w-5 h-5 rounded bg-stone-100 hover:bg-stone-200 text-stone-600 font-bold flex items-center justify-center">-</button>
                                        <span class="font-black text-xs w-4 text-center" x-text="item.qty"></span>
                                        <button @click="updatePosQty(item, 1)" class="w-5 h-5 rounded bg-stone-100 hover:bg-stone-200 text-stone-600 font-bold flex items-center justify-center">+</button>
                                        <span class="font-black text-stone-800 text-right w-16" x-text="formatRupiah(item.price * item.qty)"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Cart Footer & Checkout Button -->
                    <div class="pt-3 border-t border-stone-200 space-y-3">
                        <div class="space-y-1 text-xs">
                            <div class="flex justify-between text-stone-500">
                                <span>Subtotal</span>
                                <span class="font-semibold text-stone-700" x-text="formatRupiah(posSubtotal)"></span>
                            </div>
                            <div class="flex justify-between text-stone-500">
                                <span>Biaya Layanan</span>
                                <span class="font-semibold text-stone-700" x-text="formatRupiah(posServiceFee)"></span>
                            </div>
                            <div class="flex justify-between text-sm font-black text-parjo-ink pt-1 border-t border-stone-100">
                                <span>Total Bayar</span>
                                <span class="text-parjo-orange" x-text="formatRupiah(posTotal)"></span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-1">Metode Pembayaran</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" @click="posPaymentMethod = 'Bayar di Kasir'" 
                                        :class="posPaymentMethod === 'Bayar di Kasir' ? 'bg-parjo-ink text-white font-bold' : 'bg-stone-50 text-stone-600 border border-stone-200'"
                                        class="py-1.5 px-2 rounded-xl text-xs transition">Tunai / Kasir</button>
                                <button type="button" @click="posPaymentMethod = 'QRIS Instant'" 
                                        :class="posPaymentMethod === 'QRIS Instant' ? 'bg-parjo-ink text-white font-bold' : 'bg-stone-50 text-stone-600 border border-stone-200'"
                                        class="py-1.5 px-2 rounded-xl text-xs transition">QRIS Kasir</button>
                            </div>
                        </div>

                        <button @click="submitPosOrder()" 
                                :disabled="posCart.length === 0 || isSubmittingPos"
                                class="w-full py-3 rounded-xl bg-parjo-orange hover:bg-parjo-darkorange text-white font-black text-xs shadow-hard-orange transition disabled:opacity-50 flex items-center justify-center space-x-2">
                            <i class="fa-solid fa-receipt"></i>
                            <span x-text="isSubmittingPos ? 'Menyimpan Pesanan...' : 'Simpan & Proses Pesanan'"></span>
                        </button>
                    </div>

                </div>

            </div>

        </div>

    </main>

    <!-- ========================================================= -->
    <!-- ORDER DETAIL MODAL -->
    <!-- ========================================================= -->
    <div x-show="showDetailModal" class="fixed inset-0 z-50 overflow-y-auto bg-stone-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showDetailModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-float border-2 border-stone-800 space-y-4">
            
            <div class="flex items-center justify-between border-b border-stone-100 pb-3">
                <div>
                    <h3 class="font-black text-base text-parjo-ink font-mono" x-text="selectedOrder?.order_code"></h3>
                    <p class="text-xs text-stone-400 font-medium" x-text="selectedOrder?.created_at_full"></p>
                </div>
                <button @click="showDetailModal = false" class="w-8 h-8 rounded-full bg-stone-100 text-stone-500 hover:text-stone-800 flex items-center justify-center">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>

            <!-- Customer & Order Details -->
            <div class="grid grid-cols-2 gap-3 p-3 bg-stone-50 rounded-2xl text-xs">
                <div>
                    <span class="text-[10px] text-stone-400 uppercase font-bold block">Pemesan</span>
                    <span class="font-bold text-stone-800" x-text="selectedOrder?.customer_name"></span>
                </div>
                <div>
                    <span class="text-[10px] text-stone-400 uppercase font-bold block">WhatsApp</span>
                    <span class="font-bold text-emerald-700" x-text="selectedOrder?.customer_phone"></span>
                </div>
                <div>
                    <span class="text-[10px] text-stone-400 uppercase font-bold block">Tipe / Meja</span>
                    <span class="font-bold text-stone-800" x-text="selectedOrder?.order_type === 'dine-in' ? `Makan di Meja #${selectedOrder?.table_number}` : 'Bawa Pulang (Take-away)'"></span>
                </div>
                <div>
                    <span class="text-[10px] text-stone-400 uppercase font-bold block">Metode Pembayaran</span>
                    <span class="font-bold text-stone-800" x-text="`${selectedOrder?.payment_method} (${selectedOrder?.payment_status === 'paid' ? 'LUNAS' : 'PENDING'})`"></span>
                </div>
            </div>

            <!-- Items -->
            <div>
                <h4 class="font-black text-xs text-stone-500 uppercase tracking-wider mb-2">Rincian Menu Dipesan</h4>
                <div class="divide-y divide-stone-100 max-h-48 overflow-y-auto pr-1">
                    <template x-for="item in (selectedOrder?.items || [])" :key="item.id">
                        <div class="py-2 flex items-start justify-between text-xs">
                            <div>
                                <span class="font-black text-parjo-orange" x-text="item.quantity + 'x'"></span>
                                <span class="font-bold text-stone-800" x-text="item.product_name"></span>
                                <p x-show="item.notes" class="text-[10px] text-stone-400 italic" x-text="'Catatan: ' + item.notes"></p>
                            </div>
                            <span class="font-bold text-stone-800" x-text="formatRupiah(item.subtotal)"></span>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Price Breakdown -->
            <div class="pt-3 border-t border-stone-200 text-xs space-y-1">
                <div class="flex justify-between text-stone-500">
                    <span>Subtotal</span>
                    <span class="font-semibold" x-text="formatRupiah(selectedOrder?.subtotal || 0)"></span>
                </div>
                <div class="flex justify-between text-stone-500">
                    <span>Biaya Layanan</span>
                    <span class="font-semibold" x-text="formatRupiah(selectedOrder?.service_fee || 0)"></span>
                </div>
                <div x-show="selectedOrder?.discount > 0" class="flex justify-between text-emerald-600 font-bold">
                    <span>Diskon Promo</span>
                    <span x-text="'- ' + formatRupiah(selectedOrder?.discount || 0)"></span>
                </div>
                <div class="flex justify-between text-sm font-black text-parjo-ink pt-2 border-t border-stone-100">
                    <span>Total Pembayaran</span>
                    <span class="text-parjo-orange" x-text="formatRupiah(selectedOrder?.total_price || 0)"></span>
                </div>
            </div>

            <!-- Modal Action Buttons -->
            <div class="pt-2 flex items-center justify-end space-x-2">
                <button @click="openPrintModal(selectedOrder)" class="px-4 py-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-800 text-xs font-bold transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-print"></i>
                    <span>Cetak Struk</span>
                </button>
                <button @click="showDetailModal = false" class="px-4 py-2 rounded-xl bg-parjo-ink text-white text-xs font-bold hover:bg-stone-800 transition">
                    Tutup
                </button>
            </div>

        </div>
    </div>

    <!-- ========================================================= -->
    <!-- THERMAL RECEIPT PRINT MODAL -->
    <!-- ========================================================= -->
    <div x-show="showPrintModal" class="fixed inset-0 z-50 overflow-y-auto bg-stone-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showPrintModal = false" class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-float border-2 border-stone-800 space-y-4">
            
            <div class="no-print flex items-center justify-between border-b border-stone-100 pb-2">
                <h3 class="font-bold text-xs text-stone-600">Pratinjau Struk Thermal POS</h3>
                <button @click="showPrintModal = false" class="text-stone-400 hover:text-stone-800">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>

            <!-- Thermal Receipt Box -->
            <div id="printable-receipt" class="bg-stone-50 p-4 rounded-xl border border-stone-200 font-mono text-xs text-stone-800 space-y-3">
                
                <!-- Receipt Header -->
                <div class="text-center space-y-0.5 border-b border-dashed border-stone-400 pb-2">
                    <h2 class="font-black text-sm tracking-widest text-black">PARJO</h2>
                    <p class="text-[10px] text-stone-600 uppercase">Pancong & Burjo Nusantara</p>
                    <p class="text-[9px] text-stone-500">Jl. Kaliurang KM 5, Yogyakarta</p>
                    <p class="text-[9px] text-stone-500">Telp/WA: 0812-3456-7890</p>
                </div>

                <!-- Receipt Meta -->
                <div class="text-[10px] space-y-0.5 border-b border-dashed border-stone-400 pb-2">
                    <div class="flex justify-between">
                        <span>Kode:</span>
                        <span class="font-bold" x-text="printOrder?.order_code"></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Waktu:</span>
                        <span x-text="printOrder?.created_at_full"></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Pelanggan:</span>
                        <span class="font-bold" x-text="printOrder?.customer_name"></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Tipe:</span>
                        <span class="font-bold" x-text="printOrder?.order_type === 'dine-in' ? `MEJA #${printOrder?.table_number}` : 'TAKE AWAY'"></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Kasir:</span>
                        <span>Kasir Utama</span>
                    </div>
                </div>

                <!-- Receipt Items -->
                <div class="space-y-1.5 border-b border-dashed border-stone-400 pb-2 text-[10px]">
                    <template x-for="item in (printOrder?.items || [])" :key="item.id">
                        <div>
                            <div class="flex justify-between font-bold">
                                <span x-text="`${item.quantity}x ${item.product_name}`"></span>
                                <span x-text="formatRupiah(item.subtotal)"></span>
                            </div>
                            <div x-show="item.notes" class="text-[9px] text-stone-500 italic pl-2" x-text="'* ' + item.notes"></div>
                        </div>
                    </template>
                </div>

                <!-- Receipt Totals -->
                <div class="text-[10px] space-y-0.5 border-b border-dashed border-stone-400 pb-2">
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span x-text="formatRupiah(printOrder?.subtotal || 0)"></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Layanan:</span>
                        <span x-text="formatRupiah(printOrder?.service_fee || 0)"></span>
                    </div>
                    <div x-show="printOrder?.discount > 0" class="flex justify-between">
                        <span>Diskon:</span>
                        <span x-text="'-' + formatRupiah(printOrder?.discount || 0)"></span>
                    </div>
                    <div class="flex justify-between text-xs font-black text-black pt-1">
                        <span>TOTAL:</span>
                        <span x-text="formatRupiah(printOrder?.total_price || 0)"></span>
                    </div>
                    <div class="flex justify-between pt-1">
                        <span>Bayar (<span x-text="printOrder?.payment_method"></span>):</span>
                        <span class="font-bold" x-text="printOrder?.payment_status === 'paid' ? 'LUNAS' : 'BELUM LUNAS'"></span>
                    </div>
                </div>

                <!-- Receipt Footer -->
                <div class="text-center text-[9px] text-stone-600 space-y-0.5 pt-1">
                    <p class="font-bold">*** TERIMA KASIH ***</p>
                    <p>Selamat Menikmati Hidangan Kami!</p>
                    <p>Follow IG: @parjo.official</p>
                </div>

            </div>

            <!-- Print Actions -->
            <div class="no-print pt-2 flex items-center justify-end space-x-2">
                <button @click="showPrintModal = false" class="px-4 py-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 text-xs font-bold transition">
                    Batal
                </button>
                <button @click="triggerPrint()" class="px-5 py-2 rounded-xl bg-parjo-orange hover:bg-parjo-darkorange text-white text-xs font-black shadow-hard-orange transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-print"></i>
                    <span>Cetak Sekarang</span>
                </button>
            </div>

        </div>
    </div>

    <!-- ========================================================= -->
    <!-- ALPINE COMPONENT JAVASCRIPT LOGIC -->
    <!-- ========================================================= -->
    <script>
        function cashierDashboard() {
            return {
                activeTab: 'orders', // 'orders', 'analytics', 'manual-pos'
                orders: [],
                counts: {
                    all: 0,
                    pending: 0,
                    processing: 0,
                    completed: 0,
                    cancelled: 0
                },
                todayRevenue: 0,
                latestId: 0,
                
                // Filters
                statusFilter: 'all',
                orderTypeFilter: 'all',
                dateFilter: 'today',
                searchQuery: '',
                
                // Real-time & sound state
                pollingInterval: null,
                soundEnabled: true,
                isRefreshing: false,
                isLoading: true,
                newOrderAlert: null,

                // Modals
                showDetailModal: false,
                selectedOrder: null,
                showPrintModal: false,
                printOrder: null,

                // Analytics Tab State
                analyticsPeriod: 'today',
                analyticsData: null,
                trendChartInstance: null,
                paymentChartInstance: null,

                // POS Manual Order State
                posSearch: '',
                posOrderType: 'dine-in',
                posTableNumber: 1,
                posCustomer: { name: '', phone: '' },
                posPaymentMethod: 'Bayar di Kasir',
                posCart: [],
                posServiceFee: 2000,
                isSubmittingPos: false,

                // Catalog products for POS (matches welcome.blade.php)
                posProducts: [
                    { id: 101, category: 'signature', catName: 'SIGNATURE', name: 'Nasi Ayam Kuah Laos', price: 16000 },
                    { id: 102, category: 'signature', catName: 'SIGNATURE', name: 'Nasi Ayam Parjo Signature', price: 20000 },
                    { id: 103, category: 'signature', catName: 'SIGNATURE', name: 'Butter Rice Smoked Beef', price: 16000 },
                    { id: 104, category: 'signature', catName: 'SIGNATURE', name: 'Nasi Ayam Saus Karamel', price: 16000 },
                    { id: 105, category: 'signature', catName: 'SIGNATURE', name: 'Nasi Ayam Gongso Kuah', price: 18000 },
                    { id: 201, category: 'burjo', catName: 'BURJO & NASI', name: 'Nasi Gila', price: 15000 },
                    { id: 202, category: 'burjo', catName: 'BURJO & NASI', name: 'Nasi Goreng Gila', price: 20000 },
                    { id: 301, category: 'indomie', catName: 'INDOMIE', name: 'Indomie Goreng Nyemek', price: 12000 },
                    { id: 302, category: 'indomie', catName: 'INDOMIE', name: 'Indomie Dok-Dok Kuah Pedas', price: 15000 },
                    { id: 401, category: 'kulit', catName: 'KULIT', name: 'Kulit Ayam Crispy Pedas', price: 14000 },
                    { id: 501, category: 'snack', catName: 'SNACK', name: 'Cireng Bumbu Rujak', price: 10000 },
                    { id: 502, category: 'snack', catName: 'SNACK', name: 'Kentang Goreng Original', price: 12000 },
                    { id: 601, category: 'pancong', catName: 'PANCONG', name: 'Pancong Lumer Choco Crunchy', price: 14000 },
                    { id: 602, category: 'pancong', catName: 'PANCONG', name: 'Pancong Lumer Keju Susu', price: 12000 },
                    { id: 701, category: 'coffee', catName: 'COFFEE', name: 'Kopi Susu Parjo', price: 13000 },
                    { id: 702, category: 'coffee', catName: 'COFFEE', name: 'Kopi Susu Sea Salted', price: 14000 },
                    { id: 801, category: 'tea', catName: 'TEA', name: 'Teh Gentong Ginastel', price: 6000 },
                    { id: 802, category: 'tea', catName: 'TEA', name: 'Lychee Tea', price: 10000 },
                    { id: 901, category: 'sachet', catName: 'SACHET', name: 'Milo Dino Dingin', price: 13000 },
                ],

                get filteredPosProducts() {
                    if (!this.posSearch.trim()) return this.posProducts;
                    const q = this.posSearch.toLowerCase();
                    return this.posProducts.filter(p => p.name.toLowerCase().includes(q) || p.catName.toLowerCase().includes(q));
                },

                get posSubtotal() {
                    return this.posCart.reduce((sum, item) => sum + (item.price * item.qty), 0);
                },

                get posTotal() {
                    return this.posSubtotal > 0 ? this.posSubtotal + this.posServiceFee : 0;
                },

                initDashboard() {
                    // Check local storage for sound preferences
                    const storedSound = localStorage.getItem('parjo_cashier_sound');
                    if (storedSound !== null) {
                        this.soundEnabled = storedSound === 'true';
                    }

                    // Initial fetch
                    this.fetchOrders();

                    // Start Live Polling every 3.5 seconds
                    this.pollingInterval = setInterval(() => {
                        if (this.activeTab === 'orders') {
                            this.pollLiveOrders();
                        }
                    }, 3500);
                },

                switchTab(tab) {
                    this.activeTab = tab;
                    if (tab === 'analytics') {
                        this.fetchAnalytics();
                    } else if (tab === 'orders') {
                        this.fetchOrders();
                    }
                },

                toggleSound() {
                    this.soundEnabled = !this.soundEnabled;
                    localStorage.setItem('parjo_cashier_sound', this.soundEnabled);
                    if (this.soundEnabled) {
                        this.playNotificationSound();
                    }
                },

                async fetchOrders(forceSpinner = false) {
                    if (forceSpinner) this.isRefreshing = true;
                    try {
                        const params = new URLSearchParams({
                            status: this.statusFilter,
                            order_type: this.orderTypeFilter,
                            date: this.dateFilter,
                            search: this.searchQuery
                        });

                        const res = await fetch(`/orders/live?${params.toString()}`);
                        const data = await res.json();

                        if (data.success) {
                            this.orders = data.orders;
                            this.counts = data.counts;
                            this.todayRevenue = data.today_revenue;
                            this.latestId = data.latest_id;
                        }
                    } catch (err) {
                        console.error('Error fetching orders:', err);
                    } finally {
                        this.isLoading = false;
                        this.isRefreshing = false;
                    }
                },

                async pollLiveOrders() {
                    try {
                        const params = new URLSearchParams({
                            status: this.statusFilter,
                            order_type: this.orderTypeFilter,
                            date: this.dateFilter,
                            search: this.searchQuery
                        });

                        const res = await fetch(`/orders/live?${params.toString()}`);
                        const data = await res.json();

                        if (data.success) {
                            // Check if a new order arrived!
                            if (this.latestId > 0 && data.latest_id > this.latestId) {
                                // Find newest order
                                const newest = data.orders.find(o => o.id === data.latest_id) || data.orders[0];
                                if (newest) {
                                    this.playNotificationSound();
                                    this.newOrderAlert = {
                                        code: newest.order_code,
                                        customer: newest.customer_name,
                                        table: newest.table_number,
                                        total: newest.total_price
                                    };
                                    // Auto dismiss after 6 seconds
                                    setTimeout(() => {
                                        if (this.newOrderAlert && this.newOrderAlert.code === newest.order_code) {
                                            this.newOrderAlert = null;
                                        }
                                    }, 6000);
                                }
                            }

                            this.orders = data.orders;
                            this.counts = data.counts;
                            this.todayRevenue = data.today_revenue;
                            this.latestId = data.latest_id;
                        }
                    } catch (err) {
                        console.warn('Live poll error:', err);
                    }
                },

                dismissAlert() {
                    this.newOrderAlert = null;
                },

                // Elegant dual-tone Web Audio chime (no external MP3 needed!)
                playNotificationSound() {
                    if (!this.soundEnabled) return;
                    try {
                        const AudioCtx = window.AudioContext || window.webkitAudioContext;
                        if (!AudioCtx) return;
                        const ctx = new AudioCtx();
                        const now = ctx.currentTime;

                        // Chime tone 1: F#5 (739.99 Hz)
                        const osc1 = ctx.createOscillator();
                        const gain1 = ctx.createGain();
                        osc1.type = 'sine';
                        osc1.frequency.setValueAtTime(739.99, now);
                        gain1.gain.setValueAtTime(0.3, now);
                        gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.4);
                        osc1.connect(gain1);
                        gain1.connect(ctx.destination);
                        osc1.start(now);
                        osc1.stop(now + 0.4);

                        // Chime tone 2: C#6 (1108.73 Hz)
                        const osc2 = ctx.createOscillator();
                        const gain2 = ctx.createGain();
                        osc2.type = 'sine';
                        osc2.frequency.setValueAtTime(1108.73, now + 0.16);
                        gain2.gain.setValueAtTime(0.35, now + 0.16);
                        gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.7);
                        osc2.connect(gain2);
                        gain2.connect(ctx.destination);
                        osc2.start(now + 0.16);
                        osc2.stop(now + 0.7);
                    } catch (e) {
                        console.warn('Audio playback error:', e);
                    }
                },

                async updateStatus(order, newStatus) {
                    try {
                        const res = await fetch(`/orders/${order.id}/status`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            },
                            body: JSON.stringify({ status: newStatus })
                        });
                        const data = await res.json();
                        if (data.success) {
                            order.status = newStatus;
                            if (newStatus === 'completed') {
                                order.payment_status = 'paid';
                            }
                            this.fetchOrders();
                        }
                    } catch (err) {
                        console.error('Error updating status:', err);
                        alert('Gagal mengubah status pesanan.');
                    }
                },

                async togglePayment(order) {
                    const newPayStatus = order.payment_status === 'paid' ? 'pending' : 'paid';
                    try {
                        const res = await fetch(`/orders/${order.id}/payment`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            },
                            body: JSON.stringify({ payment_status: newPayStatus })
                        });
                        const data = await res.json();
                        if (data.success) {
                            order.payment_status = newPayStatus;
                        }
                    } catch (err) {
                        console.error('Error updating payment:', err);
                        alert('Gagal mengubah status pembayaran.');
                    }
                },

                openOrderDetail(order) {
                    this.selectedOrder = order;
                    this.showDetailModal = true;
                },

                openPrintModal(order) {
                    this.printOrder = order;
                    this.showPrintModal = true;
                },

                triggerPrint() {
                    window.print();
                },

                // ----------------------------------------------------
                // BUSINESS ANALYTICS LOGIC & CHART.JS
                // ----------------------------------------------------
                async fetchAnalytics() {
                    try {
                        const res = await fetch(`/orders-analytics?period=${this.analyticsPeriod}`);
                        const data = await res.json();
                        if (data.success) {
                            this.analyticsData = data;
                            this.$nextTick(() => {
                                this.renderCharts(data);
                            });
                        }
                    } catch (err) {
                        console.error('Error fetching analytics:', err);
                    }
                },

                changeAnalyticsPeriod(period) {
                    this.analyticsPeriod = period;
                    this.fetchAnalytics();
                },

                renderCharts(data) {
                    // 1. Trend Chart
                    const trendCanvas = document.getElementById('trendChartCanvas');
                    if (trendCanvas) {
                        if (this.trendChartInstance) {
                            this.trendChartInstance.destroy();
                        }
                        const ctx = trendCanvas.getContext('2d');
                        const labels = data.sales_trend.map(t => t.label);
                        const revenues = data.sales_trend.map(t => t.revenue);
                        const orderCounts = data.sales_trend.map(t => t.orders);

                        this.trendChartInstance = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [
                                    {
                                        label: 'Omzet (Rp)',
                                        data: revenues,
                                        backgroundColor: '#FF4800',
                                        borderRadius: 6,
                                        yAxisID: 'y'
                                    },
                                    {
                                        label: 'Jumlah Pesanan',
                                        data: orderCounts,
                                        type: 'line',
                                        borderColor: '#00583E',
                                        backgroundColor: '#00583E',
                                        borderWidth: 2,
                                        pointRadius: 4,
                                        yAxisID: 'y1'
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: { mode: 'index', intersect: false },
                                scales: {
                                    y: {
                                        type: 'linear',
                                        display: true,
                                        position: 'left',
                                        ticks: {
                                            callback: (val) => 'Rp ' + (val >= 1000 ? (val / 1000) + 'k' : val)
                                        },
                                        grid: { color: '#F1EFEA' }
                                    },
                                    y1: {
                                        type: 'linear',
                                        display: true,
                                        position: 'right',
                                        grid: { drawOnChartArea: false }
                                    },
                                    x: {
                                        grid: { display: false }
                                    }
                                }
                            }
                        });
                    }

                    // 2. Payment & Order Type Distribution Chart
                    const payCanvas = document.getElementById('paymentChartCanvas');
                    if (payCanvas) {
                        if (this.paymentChartInstance) {
                            this.paymentChartInstance.destroy();
                        }
                        const ctx = payCanvas.getContext('2d');

                        const labels = (data.payment_methods || []).map(p => p.payment_method);
                        const counts = (data.payment_methods || []).map(p => p.count);

                        this.paymentChartInstance = new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: labels.length > 0 ? labels : ['QRIS', 'Kasir'],
                                datasets: [{
                                    data: counts.length > 0 ? counts : [1, 1],
                                    backgroundColor: ['#00583E', '#FF4800', '#F59E0B', '#3B82F6']
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { position: 'bottom' }
                                }
                            }
                        });
                    }
                },

                // ----------------------------------------------------
                // POS MANUAL ORDER METHODS
                // ----------------------------------------------------
                addToPosCart(product) {
                    const existing = this.posCart.find(c => c.id === product.id);
                    if (existing) {
                        existing.qty += 1;
                    } else {
                        this.posCart.push({
                            id: product.id,
                            name: product.name,
                            category: product.catName,
                            price: product.price,
                            qty: 1
                        });
                    }
                },

                updatePosQty(item, delta) {
                    item.qty += delta;
                    if (item.qty <= 0) {
                        this.posCart = this.posCart.filter(c => c.id !== item.id);
                    }
                },

                async submitPosOrder() {
                    if (this.posCart.length === 0) return;
                    if (!this.posCustomer.name.trim()) {
                        alert('Silakan isi nama pemesan!');
                        return;
                    }

                    this.isSubmittingPos = true;

                    const payload = {
                        customer_name: this.posCustomer.name.trim(),
                        customer_phone: this.posCustomer.phone.trim() || '0812000000',
                        table_number: this.posOrderType === 'dine-in' ? this.posTableNumber : null,
                        order_type: this.posOrderType,
                        payment_method: this.posPaymentMethod,
                        subtotal: this.posSubtotal,
                        service_fee: this.posServiceFee,
                        discount: 0,
                        total_price: this.posTotal,
                        notes: 'Order via Kasir Desk',
                        items: this.posCart.map(item => ({
                            product_id: item.id,
                            product_name: item.name,
                            category: item.category,
                            price: item.price,
                            quantity: item.qty
                        }))
                    };

                    try {
                        const res = await fetch('/orders', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await res.json();
                        if (res.ok && data.success) {
                            alert(`Pesanan ${data.order.order_code} berhasil dibuat!`);
                            // Reset POS form
                            this.posCart = [];
                            this.posCustomer = { name: '', phone: '' };
                            // Switch to orders tab
                            this.switchTab('orders');
                        } else {
                            alert('Gagal membuat pesanan POS: ' + (data.message || 'Cek kelengkapan input.'));
                        }
                    } catch (err) {
                        console.error('POS submit error:', err);
                        alert('Terjadi kesalahan koneksi.');
                    } finally {
                        this.isSubmittingPos = false;
                    }
                },

                // Helper Utilities
                formatRupiah(amount) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(amount || 0);
                },

                cleanPhone(phone) {
                    if (!phone) return '';
                    let clean = phone.replace(/[^0-9]/g, '');
                    if (clean.startsWith('0')) {
                        clean = '62' + clean.substring(1);
                    }
                    return clean;
                }
            };
        }
    </script>

</body>
</html>
