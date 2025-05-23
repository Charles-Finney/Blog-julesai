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
    type VARCHAR(50), -- e.g., 'software', 'document', 'project'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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
