<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        DB::table('user')->insert([
            'username' => 'admin',
            'email' => 'admin@clothstudioz.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create sample users
        for ($i = 1; $i <= 5; $i++) {
            DB::table('user')->insert([
                'username' => 'user' . $i,
                'email' => 'user' . $i . '@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Create sample products
        $categories = ['Pria', 'Wanita', 'Anak', 'Aksesoris'];
        $colors = ['Hitam', 'Putih', 'Merah', 'Biru', 'Hijau', 'Kuning'];

        $products = [
            ['name' => 'Kaos Polos Premium', 'price' => 150000, 'category' => 'Pria'],
            ['name' => 'Kemeja Formal Slim Fit', 'price' => 250000, 'category' => 'Pria'],
            ['name' => 'Celana Jeans Denim', 'price' => 300000, 'category' => 'Pria'],
            ['name' => 'Jaket Bomber', 'price' => 400000, 'category' => 'Pria'],
            ['name' => 'Dress Casual', 'price' => 200000, 'category' => 'Wanita'],
            ['name' => 'Blouse Elegant', 'price' => 180000, 'category' => 'Wanita'],
            ['name' => 'Rok Mini', 'price' => 120000, 'category' => 'Wanita'],
            ['name' => 'Cardigan Rajut', 'price' => 220000, 'category' => 'Wanita'],
            ['name' => 'Kaos Anak Karakter', 'price' => 80000, 'category' => 'Anak'],
            ['name' => 'Celana Pendek Anak', 'price' => 60000, 'category' => 'Anak'],
            ['name' => 'Topi Baseball', 'price' => 50000, 'category' => 'Aksesoris'],
            ['name' => 'Tas Ransel', 'price' => 350000, 'category' => 'Aksesoris'],
            ['name' => 'Kaos Polo Shirt', 'price' => 175000, 'category' => 'Pria'],
            ['name' => 'Sweater Hoodie', 'price' => 280000, 'category' => 'Pria'],
            ['name' => 'Jumpsuit Wanita', 'price' => 320000, 'category' => 'Wanita'],
        ];

        foreach ($products as $index => $product) {
            DB::table('produk')->insert([
                'nama_produk' => $product['name'],
                'deskripsi' => 'Produk fashion berkualitas tinggi dengan bahan premium dan desain modern. Cocok untuk gaya sehari-hari maupun acara formal.',
                'harga' => $product['price'],
                'kategori' => $product['category'],
                'warna' => $colors[array_rand($colors)],
                'brand' => 'ClothStudioz',
                'stok' => rand(50, 200),
                'stok_s' => rand(10, 50),
                'stok_m' => rand(10, 50),
                'stok_l' => rand(10, 50),
                'stok_xl' => rand(10, 50),
                'gambar_produk' => 'default.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Add discount for some products
            if ($index % 3 == 0) {
                $productId = DB::table('produk')->where('nama_produk', $product['name'])->first()->produk_id;
                $discountPercent = rand(10, 30);
                $discountPrice = $product['price'] * (100 - $discountPercent) / 100;
                
                DB::table('barang_diskon')->insert([
                    'produk_id' => $productId,
                    'diskon_persen' => $discountPercent,
                    'harga_diskon' => $discountPrice,
                    'mulai_diskon' => now()->subDays(5),
                    'selesai_diskon' => now()->addDays(25),
                ]);
            }
        }

        echo "Database seeded successfully!\n";
        echo "Admin credentials: username=admin, password=admin123\n";
        echo "User credentials: username=user1-5, password=password\n";
    }
}
