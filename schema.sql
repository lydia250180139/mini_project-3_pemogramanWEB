
CREATE DATABASE IF NOT EXISTS galeri_laptop;
USE galeri_laptop;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff') NOT NULL
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) UNIQUE NOT NULL
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    sku VARCHAR(40) UNIQUE NOT NULL,
    name VARCHAR(150) NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    minimum_stock INT NOT NULL DEFAULT 2,
    description TEXT,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    CHECK (price >= 0),
    CHECK (stock >= 0)
);

CREATE TABLE suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(30),
    address VARCHAR(255)
);

CREATE TABLE product_suppliers (
    product_id INT NOT NULL,
    supplier_id INT NOT NULL,
    PRIMARY KEY (product_id, supplier_id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
);

CREATE TABLE stock_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    type ENUM('IN', 'OUT') NOT NULL,
    quantity INT NOT NULL,
    note VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    CHECK (quantity > 0)
);

INSERT INTO users (username, password, role) VALUES
('admin', SHA2('admin123', 256), 'admin'),
('staff', SHA2('staff123', 256), 'staff');

INSERT INTO categories (name) VALUES
('Laptop'),
('iPad'),
('Aksesoris');

INSERT INTO suppliers (name, phone, address) VALUES
('Supplier Galeri Laptop', '081234567890', 'Lhokseumawe');

INSERT INTO products
(category_id, sku, name, price, stock, minimum_stock, description)
VALUES
(1, 'LAP001', 'ASUS Vivobook 14', 7800000, 5, 2,
 'Laptop untuk kuliah dan pekerjaan'),
(1, 'LAP002', 'Lenovo IdeaPad Slim 3', 7200000, 3, 2,
 'Laptop untuk produktivitas'),
(2, 'PAD001', 'iPad Generasi 10', 6500000, 4, 2,
 'Tablet untuk belajar dan hiburan'),
(3, 'AKS001', 'Keyboard Wireless', 250000, 8, 3,
 'Keyboard tanpa kabel'),
(3, 'AKS002', 'Mouse Bluetooth', 150000, 2, 3,
 'Mouse bluetooth praktis');
