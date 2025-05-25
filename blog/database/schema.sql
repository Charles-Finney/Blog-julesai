-- SQL schema for the blog database

-- Table for blog posts
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL, -- Default/fallback title
    content TEXT NOT NULL, -- Default/fallback content
    title_en VARCHAR(255),
    content_en TEXT,
    title_fr VARCHAR(255),
    content_fr TEXT,
    type VARCHAR(50), -- e.g., 'software', 'document', 'project', 'book', 'video_tutorial'
    
    -- Fields for 'book' type
    author VARCHAR(255) NULL,
    cover_image_url VARCHAR(255) NULL,
    publication_year INT NULL,
    isbn VARCHAR(20) NULL,
    purchase_link VARCHAR(255) NULL,
    
    -- Fields for 'video_tutorial' type
    video_url VARCHAR(255) NULL,
    platform VARCHAR(100) NULL, -- e.g., 'YouTube', 'Vimeo', 'Self-hosted'
    duration VARCHAR(20) NULL, -- e.g., '10:35', '1h 20m'
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table for project/post images
CREATE TABLE project_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT,
    image_url VARCHAR(255) NOT NULL,
    description TEXT, -- Default/fallback description
    description_en TEXT,
    description_fr TEXT,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);

-- Table for messages from contact form
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    message TEXT NOT NULL,
    received_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for click counts (e.g., for a homepage counter)
CREATE TABLE click_counts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_identifier VARCHAR(255) UNIQUE, -- e.g., 'homepage_counter'
    count INT DEFAULT 0
);

-- Table for user roles
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE
);

-- Insert default roles
INSERT INTO roles (role_name) VALUES ('admin'); -- Assuming id will be 1
INSERT INTO roles (role_name) VALUES ('user');  -- Assuming id will be 2

-- Table for users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255), -- Nullable
    profile_picture_url VARCHAR(255), -- Nullable
    role_id INT DEFAULT 2, -- Default to 'user' role (assuming 'user' role has id 2)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);
