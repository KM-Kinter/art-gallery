-- Create the database
CREATE DATABASE IF NOT EXISTS art_gallery;
USE art_gallery;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('user', 'artist', 'admin') NOT NULL DEFAULT 'user',
    bio TEXT,
    profile_image VARCHAR(255),
    status ENUM('active', 'inactive', 'banned') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Artworks table
CREATE TABLE IF NOT EXISTS artworks (
    artwork_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    image_path VARCHAR(255) NOT NULL,
    artist_id INT NOT NULL,
    category_id INT NOT NULL,
    price DECIMAL(10,2),
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (artist_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE
);

-- Comments table
CREATE TABLE IF NOT EXISTS comments (
    comment_id INT PRIMARY KEY AUTO_INCREMENT,
    artwork_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (artwork_id) REFERENCES artworks(artwork_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Ratings table
CREATE TABLE IF NOT EXISTS ratings (
    rating_id INT PRIMARY KEY AUTO_INCREMENT,
    artwork_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_rating (artwork_id, user_id),
    FOREIGN KEY (artwork_id) REFERENCES artworks(artwork_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Follows table
CREATE TABLE IF NOT EXISTS follows (
    follow_id INT PRIMARY KEY AUTO_INCREMENT,
    follower_id INT NOT NULL,
    artist_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_follow (follower_id, artist_id),
    FOREIGN KEY (follower_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (artist_id) REFERENCES users(user_id) ON DELETE CASCADE
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

-- Insert default categories
INSERT INTO categories (name, description) VALUES
('Painting', 'Traditional and digital paintings'),
('Photography', 'Digital and film photography'),
('Sculpture', '3D artworks and sculptures'),
('Digital Art', 'Computer-generated artwork'),
('Drawing', 'Sketches and illustrations'),
('Mixed Media', 'Combination of different mediums');

-- Insert admin user
INSERT INTO users (username, email, password, full_name, role, status) VALUES
('admin', 'admin@artgallery.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin', 'active');
-- Password: admin123 