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
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #e9d5c0 0%, #bca48a 100%);
            background-attachment: fixed;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
            position: relative;
            overflow-x: hidden;
        }
        .hero {
            height: 260px;
            background: linear-gradient(120deg, #bca48a 60%, #e9d5c0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            text-align: center;
            position: relative;
            border-radius: 0 0 40px 40px;
            box-shadow: 0 4px 24px rgba(140,109,82,0.10);
        }
        .hero-content {
            z-index: 1;
        }
        .hero h1 {
            font-size: 2.8rem;
            margin-bottom: 0.7rem;
            font-family: 'Segoe UI', Arial, sans-serif;
            letter-spacing: 2px;
            color: #fff7e6;
            text-shadow: 1px 2px 8px #bca48a;
        }
        .rating-summary {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            color: #8c6d52;
        }
        .stars {
            color: #ffd700;
            font-size: 1.5rem;
            margin: 0.5rem 0;
            letter-spacing: 2px;
        }
        .container {
            max-width: 1100px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .reviews-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2rem;
            padding: 2rem 0;
        }
        .review-card {
            background: rgba(255,255,255,0.85);
            border-radius: 22px;
            box-shadow: 0 8px 32px rgba(140,109,82,0.13), 0 2px 8px rgba(140,109,82,0.10);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
            border: 1.5px solid #e9d5c0;
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
        }
        .review-card:hover {
            transform: translateY(-7px) scale(1.025);
            box-shadow: 0 12px 36px rgba(140,109,82,0.18), 0 4px 16px rgba(140,109,82,0.13);
        }
        .review-media {
            position: relative;
            width: 100%;
            padding-top: 70%;
            background: #f8f9fa;
            overflow: hidden;
            border-radius: 0 0 18px 18px;
        }
        .review-media img, .review-media video {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;
            border-radius: 0 0 18px 18px;
            box-shadow: 0 2px 8px rgba(140,109,82,0.10);
        }
        .review-content {
            padding: 1.5rem 1.5rem 1rem 1.5rem;
            position: relative;
        }
        .review-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            gap: 1rem;
        }
        .user-avatar {
            width: 48px; height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, #bca48a 60%, #e9d5c0 100%);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.7rem;
            color: #fff;
            font-weight: bold;
            box-shadow: 0 2px 8px rgba(140,109,82,0.10);
        }
        .user-info { flex: 1; }
        .username {
            font-weight: bold;
            color: #8c6d52;
            font-size: 1.1rem;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .package-name {
            font-size: 0.98rem;
            color: #bca48a;
            margin-top: 2px;
        }
        .review-date {
            font-size: 0.92rem;
            color: #bca48a;
            margin-top: 2px;
        }
        .review-rating {
            margin-bottom: 0.7rem;
        }
        .review-rating .star {
            color: #ffd700;
            font-size: 1.3rem;
            margin-right: 2px;
            text-shadow: 0 1px 2px #fffbe6;
        }
        .review-text {
            font-size: 1.08rem;
            color: #5e4630;
            margin-bottom: 0.5rem;
            line-height: 1.6;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .cute-icon {
            font-size: 1.2rem;
            margin-right: 6px;
            color: #ffb6b9;
        }
        /* Decorative SVGs */
        .bg-svg {
            position: absolute;
            z-index: 1;
            opacity: 0.10;
            pointer-events: none;
        }
        .bg-svg.topleft {
            top: 0;
            left: 0;
            width: 180px;
            height: 180px;
        }
        .bg-svg.bottomright {
            bottom: 0;
            right: 0;
            width: 200px;
            height: 200px;
        }
        @media (max-width: 700px) {
            .container { padding: 0 0.2rem; }
            .reviews-grid { grid-template-columns: 1fr; gap: 1.2rem; }
            .bg-svg.topleft, .bg-svg.bottomright { width: 100px; height: 100px; }
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
                                <?php echo substr($review['username'], 0, 1); ?>
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

<!-- Decorative SVGs -->
<svg class="bg-svg topleft" viewBox="0 0 200 200" fill="none"><ellipse cx="100" cy="100" rx="100" ry="100" fill="#bca48a"/></svg>
<svg class="bg-svg bottomright" viewBox="0 0 200 200" fill="none"><rect x="0" y="0" width="200" height="200" rx="60" fill="#8c6d52"/></svg>
</body>
</html>
