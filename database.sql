-- Create the database
CREATE DATABASE IF NOT EXISTS art_gallery;
USE art_gallery;

-- Users table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('admin', 'staff', 'artist', 'visitor') NOT NULL,
    full_name VARCHAR(100),
    bio TEXT,
    profile_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive', 'banned') DEFAULT 'active'
);

-- Categories table
CREATE TABLE categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    description TEXT
);

-- Artworks table
CREATE TABLE artworks (
    artwork_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    artist_id INT,
    category_id INT,
    description TEXT,
    image_path VARCHAR(255) NOT NULL,
    creation_date DATE,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'approved', 'featured', 'archived') DEFAULT 'pending',
    FOREIGN KEY (artist_id) REFERENCES users(user_id),
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
);

-- Comments table
CREATE TABLE comments (
    comment_id INT PRIMARY KEY AUTO_INCREMENT,
    artwork_id INT,
    user_id INT,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (artwork_id) REFERENCES artworks(artwork_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Ratings table
CREATE TABLE ratings (
    rating_id INT PRIMARY KEY AUTO_INCREMENT,
    artwork_id INT,
    user_id INT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (artwork_id) REFERENCES artworks(artwork_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    UNIQUE KEY unique_rating (artwork_id, user_id)
);

-- Exhibitions table
CREATE TABLE exhibitions (
    exhibition_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    start_date DATE,
    end_date DATE,
    status ENUM('upcoming', 'ongoing', 'past') DEFAULT 'upcoming',
    curator_id INT,
    FOREIGN KEY (curator_id) REFERENCES users(user_id)
);

-- Exhibition_artworks table (for managing artworks in exhibitions)
CREATE TABLE exhibition_artworks (
    exhibition_id INT,
    artwork_id INT,
    display_order INT,
    PRIMARY KEY (exhibition_id, artwork_id),
    FOREIGN KEY (exhibition_id) REFERENCES exhibitions(exhibition_id),
    FOREIGN KEY (artwork_id) REFERENCES artworks(artwork_id)
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, email, role, full_name) 
VALUES ('admin', '$2y$10$8KN/4VUwF.UE4YnMx9AbkOJzI2Qv4SFB3Qr9yJQWHhqBqFBj0V9Uy', 'admin@artgallery.com', 'admin', 'System Administrator');

-- Insert some basic categories
INSERT INTO categories (name, description) VALUES
('Painting', 'Traditional and digital paintings'),
('Photography', 'Digital and film photography'),
('Sculpture', '3D artworks and installations'),
('Digital Art', 'Computer-generated and digital creations'),
('Mixed Media', 'Artworks combining multiple mediums'); 