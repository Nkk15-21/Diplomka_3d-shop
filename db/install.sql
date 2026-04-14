DROP DATABASE IF EXISTS 3d_print_shop;
CREATE DATABASE 3d_print_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE 3d_print_shop;

CREATE TABLE users (
                       id INT AUTO_INCREMENT PRIMARY KEY,
                       name VARCHAR(100) NOT NULL,
                       email VARCHAR(150) NOT NULL UNIQUE,
                       phone VARCHAR(50) DEFAULT NULL,
                       password_hash VARCHAR(255) NOT NULL,
                       role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
                       created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            name VARCHAR(100) NOT NULL,
                            description TEXT DEFAULT NULL,
                            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          category_id INT DEFAULT NULL,
                          name VARCHAR(150) NOT NULL,
                          short_description VARCHAR(255) DEFAULT NULL,
                          description TEXT DEFAULT NULL,
                          price DECIMAL(10,2) NOT NULL,
                          image_path VARCHAR(255) DEFAULT NULL,
                          is_active TINYINT(1) NOT NULL DEFAULT 1,
                          created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                          CONSTRAINT fk_products_category
                              FOREIGN KEY (category_id) REFERENCES categories(id)
                                  ON DELETE SET NULL
                                  ON UPDATE CASCADE
);

CREATE TABLE orders (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT DEFAULT NULL,
                        customer_name VARCHAR(100) NOT NULL,
                        customer_email VARCHAR(150) NOT NULL,
                        customer_phone VARCHAR(50) DEFAULT NULL,
                        total_amount DECIMAL(10,2) DEFAULT NULL,
                        status ENUM('new','processing','done','cancelled') NOT NULL DEFAULT 'new',
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        CONSTRAINT fk_orders_user
                            FOREIGN KEY (user_id) REFERENCES users(id)
                                ON DELETE SET NULL
                                ON UPDATE CASCADE
);

CREATE TABLE order_items (
                             id INT AUTO_INCREMENT PRIMARY KEY,
                             order_id INT NOT NULL,
                             product_id INT NOT NULL,
                             quantity INT NOT NULL,
                             unit_price DECIMAL(10,2) NOT NULL,
                             CONSTRAINT fk_order_items_order
                                 FOREIGN KEY (order_id) REFERENCES orders(id)
                                     ON DELETE CASCADE
                                     ON UPDATE CASCADE,
                             CONSTRAINT fk_order_items_product
                                 FOREIGN KEY (product_id) REFERENCES products(id)
                                     ON DELETE CASCADE
                                     ON UPDATE CASCADE
);

CREATE TABLE custom_orders (
                               id INT AUTO_INCREMENT PRIMARY KEY,
                               user_id INT DEFAULT NULL,
                               customer_name VARCHAR(100) NOT NULL,
                               customer_email VARCHAR(150) NOT NULL,
                               customer_phone VARCHAR(50) DEFAULT NULL,
                               material VARCHAR(150) NOT NULL,
                               color VARCHAR(100) DEFAULT NULL,
                               layer_height DECIMAL(5,2) DEFAULT NULL,
                               infill INT DEFAULT NULL,
                               estimated_price DECIMAL(10,2) DEFAULT NULL,
                               status ENUM('new','processing','done','cancelled') NOT NULL DEFAULT 'new',
                               model_file VARCHAR(255) NOT NULL,
                               comment TEXT DEFAULT NULL,
                               created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                               CONSTRAINT fk_custom_orders_user
                                   FOREIGN KEY (user_id) REFERENCES users(id)
                                       ON DELETE SET NULL
                                       ON UPDATE CASCADE
);

CREATE TABLE contacts (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          name VARCHAR(100) NOT NULL,
                          email VARCHAR(150) NOT NULL,
                          subject VARCHAR(150) DEFAULT NULL,
                          message TEXT NOT NULL,
                          created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO categories (name, description) VALUES
                                               ('Декор', 'Декоративные изделия для дома и интерьера'),
                                               ('Аксессуары', 'Полезные аксессуары и держатели'),
                                               ('Запчасти', 'Функциональные детали и крепления'),
                                               ('Фигурки', 'Коллекционные и декоративные модели');

INSERT INTO products (category_id, name, short_description, description, price, image_path, is_active) VALUES
                                                                                                           (1, 'Подставка для телефона', 'Удобная подставка для смартфона', 'Компактная 3D-печатная подставка для телефона. Подходит для рабочего стола и дома.', 12.50, 'uploads/images/phone_stand.jpg', 1),
                                                                                                           (2, 'Органайзер для кабелей', 'Держатель для проводов на столе', 'Практичный органайзер для аккуратного размещения кабелей и зарядок.', 7.90, 'uploads/images/cable_holder.jpg', 1),
                                                                                                           (3, 'Кронштейн', 'Функциональная 3D-печатная деталь', 'Прочный кронштейн для бытового применения и небольших проектов.', 15.00, 'uploads/images/bracket.jpg', 1),
                                                                                                           (4, 'Фигурка дракона', 'Декоративная фигурка для коллекции', 'Эффектная фигурка дракона. Подойдёт как подарок или украшение полки.', 24.99, 'uploads/images/dragon.jpg', 1);