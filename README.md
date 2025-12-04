# ClothStudioz - E-commerce Fashion Platform

ClothStudioz adalah platform e-commerce fashion yang dibangun menggunakan Laravel 12 dan Tailwind CSS.

## 🚀 Fitur Utama

### Frontend (User)
- ✅ **Product Display & Search** (NINDYA)
  - Katalog produk dengan grid layout responsive
  - Live search dengan AJAX
  - Filter produk (kategori, harga, ukuran)
  - Sorting (terbaru, harga, nama)
  - Discount badge untuk produk diskon
  
- ✅ **Wishlist & Shopping Cart** (RIDHO)
  - Wishlist dengan counter di navbar
  - Add/remove wishlist dengan AJAX
  - Shopping cart dengan size selection
  - Cart counter di navbar
  - Stock validation
  - Update quantity cart items
  
- ✅ **Product Detail**
  - Gambar produk
  - Informasi lengkap (harga, stok, deskripsi)
  - Size selection (S, M, L, XL)
  - Add to cart & wishlist buttons
  - Related products

### Admin Panel (PITOM)
- ✅ **Dashboard**
  - Statistics (total produk, user, transaksi, wishlist)
  - Top 5 products
  - Recent activities
  - Recent products table
  
- ✅ **Product Management**
  - CRUD operations for products
  - Image upload
  - Stock management per size
  - Search functionality
  - Product listing with images

## 📋 Requirements

- PHP >= 8.2
- Composer
- Node.js & NPM
- SQLite (atau MySQL/PostgreSQL)

## ⚙️ Installation

### 1. Clone Repository

```bash
git clone https://github.com/ridhoapdel/clothstudioz.git
cd clothstudioz
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### 3. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create SQLite database
touch database/database.sqlite
```

### 4. Database Migration & Seeding

```bash
# Run migrations
php artisan migrate

# Seed sample data
php artisan db:seed
```

**Default Credentials:**
- Admin: `username: admin`, `password: admin123`
- User: `username: user1-5`, `password: password`

### 5. Create Upload Directory

```bash
# Create uploads directory for product images
mkdir -p public/uploads
chmod -R 775 public/uploads
```

### 6. Build Assets

```bash
# Development
npm run dev

# Production
npm run build
```

### 7. Run Application

```bash
# Start development server
php artisan serve
```

Visit `http://localhost:8000` in your browser.

## 📁 Project Structure

```
clothstudioz/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── HomeController.php        # Product listing, search
│   │   │   ├── CartController.php        # Cart & wishlist
│   │   │   ├── AdminController.php       # Admin panel
│   │   │   └── UserController.php        # Authentication
│   │   └── Middleware/
│   │       └── AdminSession.php          # Admin authentication
│   └── Models/
│       ├── Product.php                   # Product model
│       ├── Cart.php                      # Cart model
│       ├── Wishlist.php                  # Wishlist model
│       ├── Discount.php                  # Discount model
│       └── Review.php                    # Review model
├── database/
│   ├── migrations/                       # Database migrations
│   └── seeders/
│       └── DatabaseSeeder.php            # Sample data seeder
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── app.blade.php            # Main layout
│   │   │   ├── navbar.blade.php         # Navigation with counters
│   │   │   └── footer.blade.php
│   │   ├── admin/                        # Admin views
│   │   ├── index.blade.php               # Homepage
│   │   ├── shopAll.blade.php            # Product listing with filters
│   │   ├── product.blade.php            # Product detail
│   │   ├── keranjang.blade.php          # Shopping cart
│   │   └── wishlist.blade.php           # Wishlist
│   └── js/                               # JavaScript files
└── routes/
    └── web.php                           # Application routes
```

## 🛠️ Technologies Used

- **Backend:** Laravel 12
- **Frontend:** Tailwind CSS 4.0
- **Database:** SQLite (default), MySQL/PostgreSQL compatible
- **JavaScript:** Vanilla JS with Fetch API for AJAX
- **Icons:** Font Awesome
- **Build Tool:** Vite

## 🔑 Key Features Implementation

### Live Search
Located in `resources/views/layouts/navbar.blade.php`
- Debounced AJAX search
- Real-time product suggestions
- Click-to-navigate results

### Product Filtering
Located in `resources/views/shopAll.blade.php`
- Client-side filtering with JavaScript
- Category, price range, and size filters
- Sortable product list
- Clear filters option

### Cart & Wishlist
Located in `app/Http/Controllers/CartController.php`
- AJAX-based add/remove operations
- Stock validation
- Size-based inventory tracking
- Session-based user tracking

### Admin Dashboard
Located in `resources/views/admin/dashboard.blade.php`
- Real-time statistics
- Top products display
- Recent activities
- Product management

## 📝 Database Schema

### Products (`produk`)
- `produk_id` - Primary key
- `nama_produk` - Product name
- `deskripsi` - Description
- `harga` - Price
- `stok` - Total stock
- `stok_s, stok_m, stok_l, stok_xl` - Stock per size
- `kategori` - Category (Pria/Wanita/Anak/Aksesoris)
- `warna` - Color
- `brand` - Brand name
- `gambar_produk` - Image filename

### Cart (`keranjang`)
- `keranjang_id` - Primary key
- `user_id` - Foreign key to users
- `produk_id` - Foreign key to products
- `size` - Selected size
- `jumlah` - Quantity

### Wishlist
- `wishlist_id` - Primary key
- `user_id` - Foreign key to users
- `produk_id` - Foreign key to products
- `tanggal_ditambahkan` - Date added

## 🎯 Team Members & Contributions

- **NINDYA** - Product Display & Search
  - Product listing with responsive grid
  - Live search functionality
  - Filters & sorting
  
- **RIDHO** - Wishlist & Shopping Cart
  - Wishlist management
  - Shopping cart functionality
  - Stock validation
  
- **PITOM** - Admin Dashboard & Product Management
  - Admin dashboard with statistics
  - Product CRUD operations
  - User management

## 🐛 Known Issues & Future Improvements

- [ ] Implement pagination for product listing
- [ ] Add quick view modal for products
- [ ] Enhance notification system (SweetAlert2/Toast)
- [ ] Add order management system
- [ ] Implement payment gateway integration
- [ ] Add product review system
- [ ] Multi-image upload for products
- [ ] Advanced analytics dashboard
- [ ] Email notifications

## 📄 License

This project is open-source and available under the MIT License.

## 👥 Contributors

- **ridhoapdel** - Project Owner
- GitHub Copilot - AI Assistant

---

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

Built with Laravel 12

