<?php
include 'session_check.php';
checkCustomerSession();

require_once 'db_connection.php';

// Add error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Enable error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    // First, check if we can get any data from feedback table
    $check = $conn->query("SELECT COUNT(*) as count FROM feedback");
    $count = $check->fetch_assoc();
    echo "<!-- Total feedback count: " . $count['count'] . " -->";
    
    // Query to get feedback with package names
    $stmt = $conn->prepare("
        SELECT 
            f.*,
            u.username,
            p.package_name
        FROM feedback f
        LEFT JOIN users u ON f.user_id = u.user_id
        LEFT JOIN packages p ON f.package_id = p.package_id
        ORDER BY f.feedback_id DESC
    ");
    
    $stmt->execute();
    $result = $stmt->get_result();
    $reviews = [];
    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }
    
    // Debug information
    echo "<!-- Debug: SQL Query executed -->";
    echo "<!-- Number of feedback entries found: " . count($reviews) . " -->";
    
    if (!empty($reviews)) {
        echo "<!-- Sample of first review:\n";
        print_r($reviews[0]);
        echo "\n-->";
    }
    
    // Get average rating from feedback table
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_reviews,
            COALESCE(AVG(rating), 0) as avg_rating
        FROM feedback
        WHERE rating IS NOT NULL
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $rating_info = $result->fetch_assoc();
    
} catch(Exception $e) { // Changed to generic Exception for mysqli errors
    error_log("Error: " . $e->getMessage());
    echo "<!-- Debug: Database error: " . htmlspecialchars($e->getMessage()) . " -->";
}

// Add this right before the reviews display section to check the data
echo "<!-- Debug: Before display section -->";
echo "<!-- Number of reviews to display: " . (isset($reviews) ? count($reviews) : 'none') . " -->";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Reviews - TasikBiruCamps</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f5f5f5;
            padding-top: 60px;
        }

        .hero {
            height: 300px;
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://images.unsplash.com/photo-1504280390367-361c6d9f38f4?ixlib=rb-4.0.3');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            position: relative;
            margin-top: 60px;
        }

        .hero-content {
            z-index: 1;
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .rating-summary {
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }

        .stars {
            color: #ffd700;
            font-size: 1.5rem;
            margin: 0.5rem 0;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .reviews-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            padding: 2rem 0;
        }

        .review-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .review-card:hover {
            transform: translateY(-5px);
        }

        .review-media {
            position: relative;
            width: 100%;
            padding-top: 75%; /* 4:3 Aspect Ratio */
            background: #f8f9fa;
            overflow: hidden;
        }

        .review-media img, 
        .review-media video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .review-content {
            padding: 1.5rem;
        }

        .review-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            gap: 1rem;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #007bff;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-avatar i {
            font-size: 24px;
            color: white;
        }

        .user-info {
            flex: 1;
        }

        .username {
            font-weight: bold;
            color: #333;
            margin-bottom: 0.25rem;
        }

        .package-name {
            font-size: 0.9rem;
            color: #666;
        }

        .review-rating {
            margin: 1rem 0;
        }

        .review-rating .fas {
            color: #ffd700;
            margin-right: 2px;
        }

        .review-rating .fa-star-o {
            color: #ccc;
        }

        .review-text {
            color: #444;
            line-height: 1.6;
            margin-bottom: 1rem;
            white-space: pre-line;
        }

        .no-reviews {
            grid-column: 1 / -1;
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }

        .no-reviews h2 {
            color: #333;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }

            .reviews-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Lightbox styles */
        .lightbox {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 1100;
            justify-content: center;
            align-items: center;
        }

        .lightbox.active {
            display: flex;
        }

        .lightbox-content {
            max-width: 90%;
            max-height: 90%;
        }

        .lightbox-content img,
        .lightbox-content video {
            max-width: 100%;
            max-height: 90vh;
            object-fit: contain;
        }

        .close-lightbox {
            position: absolute;
            top: 20px;
            right: 20px;
            color: white;
            font-size: 2rem;
            cursor: pointer;
        }

        .additional-photos {
            display: flex;
            gap: 0.5rem;
            padding: 0.5rem;
            background: #f8f9fa;
            overflow-x: auto;
        }

        .thumbnail {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .thumbnail:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="hero">
    <div class="hero-content">
        <h1>Customer Reviews</h1>
        <?php if (isset($rating_info) && $rating_info['total_reviews'] > 0): ?>
        <div class="rating-summary">
            <div class="stars">
                <?php
                $avg_rating = round($rating_info['avg_rating'], 1);
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= $avg_rating) {
                        echo '<i class="fas fa-star"></i>';
                    } elseif ($i - 0.5 <= $avg_rating) {
                        echo '<i class="fas fa-star-half-alt"></i>';
                    } else {
                        echo '<i class="far fa-star"></i>';
                    }
                }
                ?>
            </div>
            <div><?php echo $avg_rating; ?> out of 5</div>
            <div><?php echo $rating_info['total_reviews']; ?> reviews</div>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="container">
    <div class="reviews-grid">
        <?php if (empty($reviews)): ?>
            <div class="no-reviews">
                <h2>No reviews yet</h2>
                <p>Be the first to share your experience!</p>
            </div>
        <?php else: ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review-card">
                    <div class="review-content">
                        <div class="review-header">
                            <div class="user-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="user-info">
                                <div class="username">
                                    <?php 
                                    if (isset($review['username'])) {
                                        echo htmlspecialchars($review['username']);
                                    } else {
                                        echo "User #" . htmlspecialchars($review['user_id']);
                                    }
                                    ?>
                                </div>
                                <?php if (isset($review['package_name'])): ?>
                                    <div class="package-name">
                                        <?php echo htmlspecialchars($review['package_name']); ?>
                                    </div>
                                <?php elseif (isset($review['package_id'])): ?>
                                    <div class="package-name">
                                        Package #<?php echo htmlspecialchars($review['package_id']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="review-rating">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star<?php echo $i <= $review['rating'] ? '' : '-o'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="review-text">
                            <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                        </p>
                        
                        <?php if (!empty($review['photo_path'])): ?>
                            <div class="review-media">
                                <img src="<?php echo htmlspecialchars($review['photo_path']); ?>" 
                                     alt="Review photo" 
                                     onclick="openLightbox(this.src)">
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($review['video_path'])): ?>
                            <div class="review-media">
                                <video controls>
                                    <source src="<?php echo htmlspecialchars($review['video_path']); ?>" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Lightbox for images/videos -->
<div class="lightbox" id="lightbox">
    <span class="close-lightbox" onclick="closeLightbox()">&times;</span>
    <div class="lightbox-content" id="lightbox-content"></div>
</div>

<script>
    function openLightbox(src, type) {
        const lightbox = document.getElementById('lightbox');
        const content = document.getElementById('lightbox-content');
        
        content.innerHTML = type === 'image' 
            ? `<img src="${src}" alt="Full size image">` 
            : `<video controls autoplay><source src="${src}" type="video/mp4"></video>`;
        
        lightbox.classList.add('active');
    }

    function closeLightbox() {
        const lightbox = document.getElementById('lightbox');
        const content = document.getElementById('lightbox-content');
        content.innerHTML = '';
        lightbox.classList.remove('active');
    }

    // Close lightbox when clicking outside the content
    document.getElementById('lightbox').addEventListener('click', function(e) {
        if (e.target === this) {
            closeLightbox();
        }
    });
</script>
</body>
</html>