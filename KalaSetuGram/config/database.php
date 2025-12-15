<?php
// Database configuration
define('DB_HOST', 'sql108.infinityfree.com');
define('DB_USERNAME', 'if0_40671151');
define('DB_PASSWORD', 'aIdY6IxXdXR'); // Replace with your actual password
define('DB_NAME', 'if0_40671151_kalasetugramdb');

// Create connection
function getConnection() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", 
                      DB_USERNAME, 
                      DB_PASSWORD,
                      [
                          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                          PDO::ATTR_EMULATE_PREPARES => false
                      ]);
        return $pdo;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Initialize database and create tables if they don't exist
function initializeDatabase() {
    try {
        // First, create the database if it doesn't exist
        $pdo = new PDO("mysql:host=" . DB_HOST . ";charset=utf8mb4", DB_USERNAME, DB_PASSWORD);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Now connect to the specific database
        $pdo = getConnection();
        
        // Create users table
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            role ENUM('buyer', 'artisan', 'tourist', 'admin') DEFAULT 'buyer',
            location VARCHAR(100),
            profile_picture VARCHAR(255),
            email_verified BOOLEAN DEFAULT FALSE,
            otp VARCHAR(6),
            otp_expires DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        // Create artisans table
        $pdo->exec("CREATE TABLE IF NOT EXISTS artisans (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            craft_type VARCHAR(100) NOT NULL,
            district VARCHAR(50) NOT NULL,
            gi_tag_status BOOLEAN DEFAULT FALSE,
            experience_years INT,
            bio TEXT,
            workshop_address TEXT,
            verification_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");
        
        // Create craft_categories table
        $pdo->exec("CREATE TABLE IF NOT EXISTS craft_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            image VARCHAR(255),
            gi_tagged BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Create crafts table
        $pdo->exec("CREATE TABLE IF NOT EXISTS crafts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            artisan_id INT NOT NULL,
            category_id INT NOT NULL,
            title VARCHAR(200) NOT NULL,
            description TEXT,
            story TEXT,
            price DECIMAL(10,2) NOT NULL,
            material VARCHAR(100),
            dimensions VARCHAR(100),
            weight VARCHAR(50),
            status ENUM('active', 'inactive', 'featured') DEFAULT 'active',
            stock_quantity INT DEFAULT 1,
            ar_model_url VARCHAR(255),
            video_url VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (artisan_id) REFERENCES artisans(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES craft_categories(id)
        )");
        
        // Create craft_images table
        $pdo->exec("CREATE TABLE IF NOT EXISTS craft_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            craft_id INT NOT NULL,
            image_url VARCHAR(255) NOT NULL,
            is_primary BOOLEAN DEFAULT FALSE,
            alt_text VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (craft_id) REFERENCES crafts(id) ON DELETE CASCADE
        )");
        
        // Create orders table
        $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            order_number VARCHAR(50) UNIQUE NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            tax_amount DECIMAL(10,2) DEFAULT 0,
            discount_amount DECIMAL(10,2) DEFAULT 0,
            shipping_address TEXT NOT NULL,
            billing_address TEXT,
            payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
            order_status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
            payment_method VARCHAR(50),
            transaction_id VARCHAR(100),
            coupon_code VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )");
        
        // Create order_items table
        $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            craft_id INT NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            price DECIMAL(10,2) NOT NULL,
            total DECIMAL(10,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (craft_id) REFERENCES crafts(id)
        )");
        
        // Create cart table
        $pdo->exec("CREATE TABLE IF NOT EXISTS cart (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            craft_id INT NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (craft_id) REFERENCES crafts(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_craft (user_id, craft_id)
        )");
        
        // Create coupons table
        $pdo->exec("CREATE TABLE IF NOT EXISTS coupons (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(50) UNIQUE NOT NULL,
            description TEXT,
            discount_type ENUM('flat', 'percentage') NOT NULL,
            discount_value DECIMAL(10,2) NOT NULL,
            minimum_amount DECIMAL(10,2) DEFAULT 0,
            usage_limit INT DEFAULT NULL,
            used_count INT DEFAULT 0,
            expires_at DATETIME,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Create heritage_stories table
        $pdo->exec("CREATE TABLE IF NOT EXISTS heritage_stories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(200) NOT NULL,
            content TEXT NOT NULL,
            category_id INT,
            village VARCHAR(100),
            district VARCHAR(50),
            image_url VARCHAR(255),
            video_url VARCHAR(255),
            author VARCHAR(100),
            is_featured BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES craft_categories(id)
        )");
        
        // Create adopt_artisan table
        $pdo->exec("CREATE TABLE IF NOT EXISTS adopt_artisan (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            artisan_id INT NOT NULL,
            monthly_amount DECIMAL(10,2) NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE,
            status ENUM('active', 'paused', 'cancelled') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (artisan_id) REFERENCES artisans(id)
        )");
        
        // Create reviews table
        $pdo->exec("CREATE TABLE IF NOT EXISTS reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            craft_id INT NOT NULL,
            rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
            review_text TEXT,
            is_verified_purchase BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (craft_id) REFERENCES crafts(id)
        )");
        
        // Insert default admin user
        $adminExists = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
        if ($adminExists == 0) {
            $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $pdo->exec("INSERT INTO users (name, email, password, role, email_verified) 
                       VALUES ('Admin', 'admin@kalasetugramdb.com', '$hashedPassword', 'admin', TRUE)");
        }
        
        // Insert default craft categories
        $categoriesExist = $pdo->query("SELECT COUNT(*) FROM craft_categories")->fetchColumn();
        if ($categoriesExist == 0) {
            $categories = [
                ['Kondapalli Toys', 'Colorful wooden toys from Krishna district', 'kondapalli-toys.jpg', 1],
                ['Kalamkari', 'Hand-painted textiles using natural dyes', 'kalamkari.jpg', 1],
                ['Bidriware', 'Metal handicrafts with silver inlay work', 'bidriware.jpg', 1],
                ['Pochampally Ikat', 'Traditional tie-dye textile art', 'pochampally-ikat.jpg', 1],
                ['Lepakshi Handicrafts', 'Stone and wood carvings', 'lepakshi.jpg', 0],
                ['Nirmal Paintings', 'Traditional wooden paintings', 'nirmal-paintings.jpg', 1]
            ];
            
            $stmt = $pdo->prepare("INSERT INTO craft_categories (name, description, image, gi_tagged) VALUES (?, ?, ?, ?)");
            foreach ($categories as $category) {
                $stmt->execute($category);
            }
        }
        
        return true;
    } catch(PDOException $e) {
        die("Database initialization failed: " . $e->getMessage());
    }
}

// Initialize the database when this file is included
initializeDatabase();
?>
