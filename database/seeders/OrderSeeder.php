<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Don't duplicate if orders already exist
        if (Order::count() > 0) {
            return;
        }

        $menuItems = [
            ['name' => 'Nasi Ayam Kuah Laos', 'category' => 'Signature', 'price' => 16000],
            ['name' => 'Nasi Ayam Parjo Signature', 'category' => 'Signature', 'price' => 20000],
            ['name' => 'Butter Rice Smoked Beef', 'category' => 'Signature', 'price' => 16000],
            ['name' => 'Nasi Gila Spesial', 'category' => 'Burjo & Nasi', 'price' => 15000],
            ['name' => 'Nasi Goreng Gila', 'category' => 'Burjo & Nasi', 'price' => 20000],
            ['name' => 'Indomie Goreng Nyemek', 'category' => 'Indomie', 'price' => 12000],
            ['name' => 'Indomie Dok-Dok Pedas', 'category' => 'Indomie', 'price' => 15000],
            ['name' => 'Pancong Lumer Coklat Keju', 'category' => 'Pancong', 'price' => 14000],
            ['name' => 'Pancong Lumer Choco Crunchy', 'category' => 'Pancong', 'price' => 14000],
            ['name' => 'Kulit Ayam Crispy', 'category' => 'Kulit', 'price' => 13000],
            ['name' => 'Cireng Bumbu Rujak', 'category' => 'Snack', 'price' => 10000],
            ['name' => 'Kopi Susu Parjo', 'category' => 'Coffee', 'price' => 13000],
            ['name' => 'Kopi Susu Sea Salted', 'category' => 'Coffee', 'price' => 14000],
            ['name' => 'Teh Gentong Ginastel', 'category' => 'Tea', 'price' => 6000],
            ['name' => 'Lychee Tea', 'category' => 'Tea', 'price' => 10000],
            ['name' => 'Milo Dino Dingin', 'category' => 'Sachet', 'price' => 13000],
        ];

        $customers = [
            ['name' => 'Dimas Pratama', 'phone' => '081234567891'],
            ['name' => 'Siti Rahmawati', 'phone' => '082198765432'],
            ['name' => 'Budi Santoso', 'phone' => '085712349876'],
            ['name' => 'Anisa Maharani', 'phone' => '081345678123'],
            ['name' => 'Rizky Fadillah', 'phone' => '087812345678'],
            ['name' => 'Nabila Putri', 'phone' => '089612345678'],
            ['name' => 'Fajar Nugroho', 'phone' => '082233445566'],
            ['name' => 'Dewi Lestari', 'phone' => '081987654321'],
            ['name' => 'Gilang Ramadhan', 'phone' => '085211223344'],
            ['name' => 'Amanda Zahra', 'phone' => '087799887766'],
        ];

        // Seed orders for the past 7 days and today
        $now = Carbon::now();

        // 1. Historical orders (past 6 days) - all completed
        for ($day = 6; $day >= 1; $day--) {
            $date = Carbon::today()->subDays($day);
            $ordersCount = rand(8, 15);

            for ($i = 0; $i < $ordersCount; $i++) {
                $cust = $customers[array_rand($customers)];
                $orderType = rand(0, 10) > 3 ? 'dine-in' : 'take-away';
                $tableNum = $orderType === 'dine-in' ? rand(1, 15) : null;
                $paymentMethod = rand(0, 1) === 1 ? 'QRIS Instant' : 'Bayar di Kasir';

                $orderHour = rand(10, 22);
                $orderMinute = rand(0, 59);
                $createdAt = (clone $date)->setHour($orderHour)->setMinute($orderMinute);

                // Pick 1-4 items
                $itemCount = rand(1, 4);
                $pickedItems = [];
                $subtotal = 0;

                for ($k = 0; $k < $itemCount; $k++) {
                    $item = $menuItems[array_rand($menuItems)];
                    $qty = rand(1, 2);
                    $itemSub = $item['price'] * $qty;
                    $subtotal += $itemSub;
                    $pickedItems[] = array_merge($item, ['qty' => $qty, 'subtotal' => $itemSub]);
                }

                $serviceFee = 2000;
                $discount = rand(0, 5) === 0 ? 5000 : 0;
                $totalPrice = max(0, $subtotal + $serviceFee - $discount);

                $order = Order::create([
                    'order_code' => 'PRJ-' . $createdAt->format('md') . '-' . strtoupper(Str::random(4)),
                    'table_number' => $tableNum,
                    'order_type' => $orderType,
                    'customer_name' => $cust['name'],
                    'customer_phone' => $cust['phone'],
                    'subtotal' => $subtotal,
                    'service_fee' => $serviceFee,
                    'discount' => $discount,
                    'voucher_code' => $discount > 0 ? 'PARJOHEMAT' : null,
                    'total_price' => $totalPrice,
                    'payment_method' => $paymentMethod,
                    'payment_status' => 'paid',
                    'status' => 'completed',
                    'created_at' => $createdAt,
                    'updated_at' => (clone $createdAt)->addMinutes(rand(10, 25)),
                ]);

                foreach ($pickedItems as $p) {
                    $order->items()->create([
                        'product_name' => $p['name'],
                        'category' => $p['category'],
                        'price' => $p['price'],
                        'quantity' => $p['qty'],
                        'subtotal' => $p['subtotal'],
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }
            }
        }

        // 2. Today's orders - mix of completed, processing, and pending
        $todayStatuses = [
            'completed', 'completed', 'completed', 'completed',
            'processing', 'processing',
            'pending', 'pending'
        ];

        $todayOrdersCount = count($todayStatuses);
        $todayStartHour = 10;

        foreach ($todayStatuses as $idx => $status) {
            $cust = $customers[$idx % count($customers)];
            $orderType = rand(0, 10) > 3 ? 'dine-in' : 'take-away';
            $tableNum = $orderType === 'dine-in' ? rand(1, 15) : null;
            $paymentMethod = rand(0, 1) === 1 ? 'QRIS Instant' : 'Bayar di Kasir';

            $createdAt = Carbon::today()->addHours($todayStartHour + $idx)->addMinutes(rand(5, 45));
            if ($createdAt->isFuture()) {
                $createdAt = Carbon::now()->subMinutes(($todayOrdersCount - $idx) * 15);
            }

            $itemCount = rand(1, 3);
            $pickedItems = [];
            $subtotal = 0;

            for ($k = 0; $k < $itemCount; $k++) {
                $item = $menuItems[array_rand($menuItems)];
                $qty = rand(1, 2);
                $itemSub = $item['price'] * $qty;
                $subtotal += $itemSub;
                $pickedItems[] = array_merge($item, ['qty' => $qty, 'subtotal' => $itemSub]);
            }

            $serviceFee = 2000;
            $discount = 0;
            $totalPrice = $subtotal + $serviceFee - $discount;

            $paymentStatus = ($status === 'completed' || $paymentMethod === 'QRIS Instant') ? 'paid' : 'pending';

            $notesOptions = [
                'Pedas sedang, cabai dipisah',
                'Es sedikit (less ice)',
                'Jangan pakai seledri',
                'Nasi dibanyakin ya mas',
                null,
                null,
            ];

            $order = Order::create([
                'order_code' => 'PRJ-' . $createdAt->format('md') . '-' . strtoupper(Str::random(4)),
                'table_number' => $tableNum,
                'order_type' => $orderType,
                'customer_name' => $cust['name'],
                'customer_phone' => $cust['phone'],
                'subtotal' => $subtotal,
                'service_fee' => $serviceFee,
                'discount' => $discount,
                'total_price' => $totalPrice,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'status' => $status,
                'notes' => $notesOptions[array_rand($notesOptions)],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            foreach ($pickedItems as $p) {
                $order->items()->create([
                    'product_name' => $p['name'],
                    'category' => $p['category'],
                    'price' => $p['price'],
                    'quantity' => $p['qty'],
                    'subtotal' => $p['subtotal'],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }
    }
}
