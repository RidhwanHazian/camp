<?php
include 'session_check.php';
checkCustomerSession();

require_once 'db_connection.php';

// Check if user has any paid bookings
$stmt = $conn->prepare("
    SELECT COUNT(*) as paid_bookings 
    FROM bookings 
    WHERE user_id = ? AND status = 'paid'
");
$stmt->bind_param("i", $_SESSION['customer_id']);
$stmt->execute();
$result = $stmt->get_result();
$paid_bookings_row = $result->fetch_assoc();
$has_paid_bookings = $paid_bookings_row['paid_bookings'] > 0;

// Get packages for the dropdown
if ($has_paid_bookings) {
    $stmt = $conn->prepare("
        SELECT DISTINCT p.package_id, p.package_name 
        FROM bookings b
        JOIN packages p ON b.package_id = p.package_id
        WHERE b.user_id = ? AND b.status = 'paid'
        AND NOT EXISTS (
            SELECT 1 FROM feedback f 
            WHERE f.user_id = b.user_id 
            AND f.package_id = b.package_id
        )
    ");
    $stmt->bind_param("i", $_SESSION['customer_id']);
    $stmt->execute();
} else {
    // In preview mode, show all packages
    $stmt = $conn->prepare("SELECT package_id, package_name FROM packages");
    $stmt->execute();
}
$result = $stmt->get_result();
$packages = [];
while ($row = $result->fetch_assoc()) {
    $packages[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TasikBiruCamps - Feedback</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        body {
            background-color: #f8f9fa;
            min-height: 100vh;
        }

        .header-banner {
            position: relative;
            height: 300px;
            background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), 
                            url('https://images.unsplash.com/photo-1504280390367-361c6d9f38f4?q=80&w=2070&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .header-banner h1 {
            color: white;
            font-size: 4rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .container {
            max-width: 1200px;
            margin: -100px auto 40px;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            position: relative;
            z-index: 1;
        }

        .preview-notice {
            grid-column: 1 / -1;
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            padding: 1rem;
            border-radius: 12px;
            text-align: center;
            font-weight: 500;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .feedback-card {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .feedback-section {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        h2 {
            color: #333;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .package-select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            color: #333;
            background: white;
            margin-bottom: 2rem;
            cursor: pointer;
        }

        .emoji-rating {
            display: flex;
            justify-content: space-between;
            margin: 2rem 0;
            padding: 0 1rem;
        }

        .emoji-rating input {
            display: none;
        }

        .emoji-rating label {
            cursor: pointer;
            font-size: 2.5rem;
            opacity: 0.5;
            transition: all 0.2s ease;
            filter: grayscale(100%);
        }

        .emoji-rating label:hover,
        .emoji-rating input:checked + label {
            opacity: 1;
            transform: scale(1.2);
            filter: grayscale(0%);
        }

        .media-section {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            text-align: center;
            margin-top: 2rem;
        }

        .media-section h3 {
            color: #333;
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .upload-area {
            display: flex;
            justify-content: center;
            gap: 4rem;
        }

        .upload-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            padding: 1rem;
            border-radius: 8px;
            transition: all 0.2s ease;
            color: #666;
        }

        .upload-box:hover {
            background: #f8f9fa;
        }

        .upload-box i {
            font-size: 2rem;
        }

        textarea {
            width: 100%;
            height: 200px;
            padding: 1rem;
            border: 1px solid #ddd;
            border-radius: 12px;
            resize: none;
            font-size: 1rem;
            margin-bottom: 1rem;
            background: #f8f9fa;
        }

        textarea::placeholder {
            color: #999;
        }

        .submit-btn {
            background: #00e676;
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            float: right;
            transition: all 0.2s ease;
        }

        .submit-btn:hover {
            background: #00c853;
            transform: translateY(-1px);
        }

        .submit-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .form-disabled {
            opacity: 0.7;
            pointer-events: none;
        }

        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
                padding: 0 15px;
                margin: 20px auto;
            }

            .header-banner {
                height: 200px;
            }

            .header-banner h1 {
                font-size: 3rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="header-banner">
        <h1>Feedback</h1>
    </div>

    <div class="container">
        <?php if (!$has_paid_bookings): ?>
            <div class="preview-notice">
                <i class="fas fa-info-circle"></i>
                This is a preview of the feedback form. To submit actual feedback, you need to make a booking and complete the payment first.
            </div>
        <?php endif; ?>

        <div class="feedback-card <?php echo !$has_paid_bookings ? 'form-disabled' : ''; ?>">
            <h2>Rate package:</h2>
            <select class="package-select" name="package_id" required <?php echo !$has_paid_bookings ? 'disabled' : ''; ?>>
                <option value="">Select a package...</option>
                <?php foreach ($packages as $package): ?>
                    <option value="<?php echo $package['package_id']; ?>">
                        <?php echo htmlspecialchars($package['package_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <div class="emoji-rating">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <input type="radio" name="rating" id="rating<?php echo $i; ?>" value="<?php echo $i; ?>" <?php echo !$has_paid_bookings ? 'disabled' : ''; ?>>
                    <label for="rating<?php echo $i; ?>"><?php echo $i === 1 ? '😠' : ($i === 2 ? '😕' : ($i === 3 ? '😊' : ($i === 4 ? '😃' : '🤩'))); ?></label>
                <?php endfor; ?>
            </div>

            <div class="media-section">
                <h3>Add photos and video</h3>
                <div class="upload-area">
                    <div class="upload-box" id="photoUpload">
                        <i class="fas fa-camera"></i>
                        <p>Photo</p>
                        <input type="file" id="photoInput" name="photo" accept="image/*" style="display: none" <?php echo !$has_paid_bookings ? 'disabled' : ''; ?>>
                    </div>
                    <div class="upload-box" id="videoUpload">
                        <i class="fas fa-video"></i>
                        <p>Video</p>
                        <input type="file" id="videoInput" name="video" accept="video/*" style="display: none" <?php echo !$has_paid_bookings ? 'disabled' : ''; ?>>
                    </div>
                </div>
            </div>
        </div>

        <div class="feedback-section">
            <h2>Your feedback:</h2>
            <form action="submit_feedback.php" method="POST" enctype="multipart/form-data">
                <textarea name="feedback" placeholder="type your comment here...." required <?php echo !$has_paid_bookings ? 'disabled' : ''; ?>></textarea>
                <button type="submit" class="submit-btn" <?php echo !$has_paid_bookings ? 'disabled' : ''; ?>>
                    <?php echo !$has_paid_bookings ? 'Preview Mode' : 'Submit Feedback'; ?>
                </button>
            </form>
        </div>
    </div>

    <script>
        // Handle photo upload
        document.getElementById('photoUpload').addEventListener('click', function() {
            if (!this.querySelector('input').disabled) {
                document.getElementById('photoInput').click();
            }
        });

        // Handle video upload
        document.getElementById('videoUpload').addEventListener('click', function() {
            if (!this.querySelector('input').disabled) {
                document.getElementById('videoInput').click();
            }
        });
    </script>
</body>
</html>