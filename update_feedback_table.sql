-- Drop existing table if it exists
DROP TABLE IF EXISTS feedback;

-- Create feedback table with the correct structure
CREATE TABLE feedback (
    feedback_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT,
    user_id INT,
    package_id INT,
    rating INT,
    comment TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
    photo_path VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
    video_path VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (package_id) REFERENCES packages(package_id)
);

-- Add average_rating column to packages table if it doesn't exist
ALTER TABLE packages
ADD COLUMN IF NOT EXISTS average_rating DECIMAL(3,2) DEFAULT NULL; 