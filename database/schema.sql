CREATE DATABASE IF NOT EXISTS tienda_padel;
USE tienda_padel;

DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user','admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    brand VARCHAR(50),
    description TEXT,
    short_description VARCHAR(255),
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(500) NOT NULL,
    stock INT DEFAULT 0,
    category VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'paid',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

-- Admin (password: password)
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@padelpro.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Productos demo (con imágenes locales en /public/img/products)
INSERT INTO products (name, brand, category, price, stock, image, short_description, description) VALUES
('Bullpadel Vertex 03', 'Bullpadel', 'Palas', 249.95, 20, '/public/img/products/bullpadel-vertex-03.png', 'Potencia máxima', 'La pala definitiva de potencia.'),
('Nox AT10 Genius 18K', 'Nox', 'Palas', 285.00, 15, '/public/img/products/nox-at10-genius-18k.png', 'Control total', 'Carbono 18K para un tacto único.'),
('Adidas Metalbone 3.2', 'Adidas', 'Palas', 270.90, 10, '/public/img/products/adidas-metalbone-3-2.png', 'Potencia personalizable', 'Sistema Weight & Balance.'),
('Head Speed Pro X', 'Head', 'Palas', 239.95, 12, '/public/img/products/head-speed-pro-x.png', 'Velocidad Auxetic', 'Sensación de impacto mejorada.'),
('Siux Diablo Revolution', 'Siux', 'Palas', 299.00, 8, '/public/img/products/siux-diablo-revolution.png', 'El mito renovado', 'Carbono 12K y gran salida.'),
('Babolat Technical Viper', 'Babolat', 'Palas', 289.90, 9, '/public/img/products/babolat-technical-viper.png', 'Ataque explosivo', 'Pala para jugadores ofensivos.'),
('Asics Gel-Resolution 9', 'Asics', 'Zapatillas', 119.00, 50, '/public/img/products/asics-gel-resolution-9.png', 'Estabilidad superior', 'Tecnología DYNAWALL para soporte.'),
('Bullpadel Hack Vibram', 'Bullpadel', 'Zapatillas', 95.50, 40, '/public/img/products/bullpadel-hack-vibram.png', 'Suela Vibram', 'Durabilidad extrema en pista.'),
('Adidas Solematch Padel', 'Adidas', 'Zapatillas', 89.95, 35, '/public/img/products/adidas-solematch-padel.png', 'Agarre y confort', 'Suela optimizada para pádel.'),
('Head Sprint Pro 3.5', 'Head', 'Zapatillas', 99.95, 25, '/public/img/products/head-sprint-pro-3-5.png', 'Ligereza', 'Transpirables y rápidas.'),
('Joma Slam Padel', 'Joma', 'Zapatillas', 74.90, 60, '/public/img/products/joma-slam-padel.png', 'Calidad-precio', 'Amortiguación y estabilidad.'),
('Babolat Jet Premura', 'Babolat', 'Zapatillas', 129.90, 18, '/public/img/products/babolat-jet-premura.png', 'Máxima movilidad', 'Diseñadas para cambios rápidos.'),
('Camiseta Bullpadel WPT', 'Bullpadel', 'Ropa', 39.95, 100, '/public/img/products/camiseta-bullpadel-wpt.png', 'Oficial WPT', 'Tejido técnico transpirable.'),
('Pantalón Corto Nox Pro', 'Nox', 'Ropa', 29.90, 80, '/public/img/products/pantalon-corto-nox-pro.png', 'Comodidad', 'Con bolsillos para pelotas.'),
('Sudadera Adidas Club', 'Adidas', 'Ropa', 49.95, 30, '/public/img/products/sudadera-adidas-club.png', 'Calidez ligera', 'Ideal para prepartido.'),
('Falda Asics Padel', 'Asics', 'Ropa', 34.95, 45, '/public/img/products/falda-asics-padel.png', 'Libertad de movimiento', 'Short interior incluido.'),
('Calcetines Head Performance', 'Head', 'Ropa', 9.95, 200, '/public/img/products/calcetines-head-performance.png', 'Refuerzo', 'Mayor confort y ajuste.'),
('Gorra Babolat Logo', 'Babolat', 'Ropa', 19.95, 120, '/public/img/products/gorra-babolat-logo.png', 'Protección', 'Visera curva y ajuste trasero.'),
('Paletero Adidas Multi', 'Adidas', 'Paleteros', 64.95, 30, '/public/img/products/paletero-adidas-multi.png', 'Gran capacidad', 'Bolsillo térmico para palas.'),
('Mochila Bullpadel Vertex', 'Bullpadel', 'Paleteros', 59.95, 25, '/public/img/products/mochila-bullpadel-vertex.png', 'Versátil', 'Compartimentos organizados.'),
('Paletero Nox Luxury', 'Nox', 'Paleteros', 79.90, 20, '/public/img/products/paletero-nox-luxury.png', 'Premium', 'Acabados de alta calidad.'),
('Paletero Head Tour Team', 'Head', 'Paleteros', 54.95, 35, '/public/img/products/paletero-head-tour-team.png', 'Espacioso', 'Para palas y ropa.'),
('Paletero Siux Diablo', 'Siux', 'Paleteros', 69.90, 18, '/public/img/products/paletero-siux-diablo.png', 'Diseño Diablo', 'Sección ventilada para calzado.'),
('Mochila Babolat Court', 'Babolat', 'Paleteros', 44.90, 40, '/public/img/products/mochila-babolat-court.png', 'Compacta', 'Ideal para el día a día.'),
('Bote Head Padel Pro S', 'Head', 'Pelotas', 5.95, 500, '/public/img/products/bote-head-padel-pro-s.png', 'Velocidad', 'Pelota oficial WPT.'),
('Bote Wilson X3 Padel', 'Wilson', 'Pelotas', 6.25, 450, '/public/img/products/bote-wilson-x3-padel.png', 'Resistencia', 'Fieltro duradero.'),
('Bote Bullpadel Premium Pro', 'Bullpadel', 'Pelotas', 6.10, 420, '/public/img/products/bote-bullpadel-premium-pro.png', 'Control', 'Rebote estable.'),
('Bote Nox Pro Titanium', 'Nox', 'Pelotas', 6.50, 300, '/public/img/products/bote-nox-pro-titanium.png', 'Salida viva', 'Buena presión y rebote.'),
('Pack 3 Botes Adidas Speed RX', 'Adidas', 'Pelotas', 17.90, 200, '/public/img/products/pack-3-botes-adidas-speed-rx.png', 'Pack ahorro', '3 botes para entreno.'),
('Bote Dunlop Pro Padel', 'Dunlop', 'Pelotas', 5.75, 380, '/public/img/products/bote-dunlop-pro-padel.png', 'Consistencia', 'Pensadas para competición.');
