-- Create customer table
CREATE TABLE IF NOT EXISTS customer (
    customer_id INT PRIMARY KEY AUTO_INCREMENT,
    customer_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create feedback table
CREATE TABLE IF NOT EXISTS feedback (
    feedback_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    package_chosen VARCHAR(100) NOT NULL,
    comment TEXT,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    media_video VARCHAR(255),
    media_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES customer(customer_id)
);

-- Insert some sample data
INSERT INTO customer (customer_name, email, phone) VALUES
('John Doe', 'john@example.com', '+1234567890'),
('Jane Smith', 'jane@example.com', '+1987654321'),
('Mike Johnson', 'mike@example.com', '+1122334455');

INSERT INTO feedback (user_id, package_chosen, comment, rating) VALUES
(1, 'Weekend Camping', 'Great experience! Loved the facilities.', 5),
(2, 'Family Adventure', 'Nice location but could improve cleanliness.', 4),
(3, 'Solo Camping', 'Amazing staff and beautiful surroundings!', 5); 