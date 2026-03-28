<?php

namespace Database\Seeders;

use App\Models\Product; // Pastikan Model Product di-import
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data Dummy Toko Komputer
        $products = [
            // PROCESSOR
            [
                'name' => 'Processor Intel Core i5-12400F Box',
                'price' => 2100000,
                'stock' => 15,
                'description' => 'Processor Intel Gen 12 Alder Lake, 6 Cores 12 Threads, Base Clock 2.5GHz, Turbo 4.4GHz.',
            ],
            [
                'name' => 'Processor AMD Ryzen 5 5600G Box',
                'price' => 1950000,
                'stock' => 10,
                'description' => 'Processor AMD Ryzen 5000 Series dengan Integrated Radeon Graphics, 6 Cores 12 Threads.',
            ],

            // MOTHERBOARD
            [
                'name' => 'Motherboard Asrock B550M-HDV',
                'price' => 1150000,
                'stock' => 8,
                'description' => 'Motherboard socket AM4 untuk Ryzen, support DDR4 up to 4733+ MHz, Slot M.2 NVMe.',
            ],
            [
                'name' => 'Motherboard MSI PRO H610M-E DDR4',
                'price' => 1250000,
                'stock' => 12,
                'description' => 'Motherboard socket LGA1700 untuk Intel Gen 12/13/14, Support PCIe 4.0.',
            ],

            // RAM
            [
                'name' => 'RAM Kingston Fury Beast 8GB DDR4 3200MHz',
                'price' => 350000,
                'stock' => 50,
                'description' => 'RAM DDR4 Single Channel 8GB, Heatspreader hitam, Garansi Lifetime.',
            ],
            [
                'name' => 'RAM Corsair Vengeance RGB RS 16GB (2x8GB) 3200MHz',
                'price' => 950000,
                'stock' => 20,
                'description' => 'RAM Kit Dual Channel 16GB dengan lampu RGB yang bisa diatur via iCUE software.',
            ],

            // SSD & STORAGE
            [
                'name' => 'SSD Samsung 980 500GB NVMe M.2',
                'price' => 850000,
                'stock' => 25,
                'description' => 'SSD NVMe PCIe 3.0 x4, Kecepatan Baca hingga 3100MB/s, Tulis hingga 2600MB/s.',
            ],
            [
                'name' => 'SSD Adata Legend 710 512GB NVMe M.2',
                'price' => 550000,
                'stock' => 30,
                'description' => 'SSD Budget kencang, PCIe Gen3 x4, Read up to 2400MB/s.',
            ],
            [
                'name' => 'Hardisk Seagate Barracuda 1TB SATA',
                'price' => 650000,
                'stock' => 15,
                'description' => 'HDD Internal 3.5 inch 7200RPM, cocok untuk penyimpanan data massal.',
            ],

            // VGA (KARTU GRAFIS)
            [
                'name' => 'VGA Zotac Gaming GeForce RTX 3060 12GB Twin Edge',
                'price' => 4450000,
                'stock' => 5,
                'description' => 'VGA Nvidia RTX 30 Series, VRAM 12GB GDDR6, Cocok untuk gaming 1080p Ultra & Editing.',
            ],
            [
                'name' => 'VGA Sapphire Pulse Radeon RX 6600 8GB',
                'price' => 3200000,
                'stock' => 7,
                'description' => 'VGA AMD Radeon terbaik untuk budget gaming 1080p, VRAM 8GB GDDR6.',
            ],

            // PSU & CASING
            [
                'name' => 'PSU FSP HV PRO 550W 80+ Bronze',
                'price' => 650000,
                'stock' => 20,
                'description' => 'Power Supply 550 Watt sertifikasi 80 Plus Bronze, Kabel Flat hitam.',
            ],
            [
                'name' => 'Casing Cube Gaming Axel - Black',
                'price' => 450000,
                'stock' => 10,
                'description' => 'Casing m-ATX Gaming dengan sisi Tempered Glass dan airflow bagus.',
            ],

            // MONITOR
            [
                'name' => 'Monitor LG 24MP400 24 Inch IPS 75Hz',
                'price' => 1450000,
                'stock' => 12,
                'description' => 'Monitor Full HD Panel IPS, 75Hz Refresh Rate, AMD FreeSync, Borderless.',
            ],
        ];

        foreach ($products as $item) {
            Product::create($item);
        }
    }
}