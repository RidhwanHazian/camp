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
        .feedback-main {
            max-width: 600px;
            margin: 60px auto 0 auto;
            background: rgba(255,255,255,0.78);
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(140,109,82,0.13), 0 2px 8px rgba(140,109,82,0.10);
            padding: 2.5rem 2.5rem 1.5rem 2.5rem;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1.5px solid #bca48a;
            position: relative;
            z-index: 2;
        }
        .camp-logo {
            font-size: 2rem;
            font-weight: bold;
            color: #8c6d52;
            text-align: center;
            margin-bottom: 0.2rem;
            letter-spacing: 2px;
        }
        .feedback-title {
            text-align: center;
            font-size: 1.6rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            letter-spacing: 1px;
            color: #8c6d52;
        }
        .feedback-desc {
            text-align: center;
            color: #bca48a;
            margin-bottom: 2rem;
        }
        .form-section {
            margin-bottom: 2rem;
        }
        .package-select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            color: #333;
            background: white;
            margin-bottom: 1.5rem;
            cursor: pointer;
        }
        .star-rating {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .star-rating input[type="radio"] {
            display: none;
        }
        .star-rating label {
            font-size: 2.2rem;
            color: #ccc;
            cursor: pointer;
            transition: color 0.2s;
        }
        .star-rating input[type="radio"]:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #f7b731;
        }
        textarea {
            width: 100%;
            height: 120px;
            padding: 1rem;
            border: 1px solid #ddd;
            border-radius: 10px;
            resize: none;
            font-size: 1rem;
            margin-bottom: 1.5rem;
            background: #f8f9fa;
        }
        textarea::placeholder {
            color: #999;
        }
        .media-section {
            margin-bottom: 1.5rem;
        }
        .media-label {
            font-weight: 500;
            color: #333;
            margin-bottom: 0.5rem;
            display: block;
        }
        .upload-area {
            display: flex;
            gap: 2rem;
            justify-content: center;
        }
        .upload-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            padding: 1rem;
            border-radius: 8px;
            border: 1.5px dashed #bbb;
            background: #fafbfc;
            transition: border-color 0.2s, background 0.2s;
            color: #666;
            min-width: 120px;
        }
        .upload-box:hover {
            border-color: #2980b9;
            background: #f0f6fa;
        }
        .upload-box i {
            font-size: 2rem;
        }
        .upload-preview {
            margin-top: 8px;
            max-width: 100px;
            border-radius: 8px;
            display: none;
        }
        .upload-preview.video {
            max-width: 120px;
        }
        .submit-btn {
            background: #2980b9;
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
            display: block;
            margin: 2rem auto 0 auto;
        }
        .submit-btn:hover {
            background: #1a5e8a;
        }
        .thankyou {
            text-align: center;
            font-size: 1.1rem;
            margin: 2rem 0 1rem 0;
            color: #27ae60;
            font-weight: 600;
        }
        .footer {
            text-align: center;
            font-size: 0.95rem;
            color: #888;
            margin-top: 2rem;
            border-top: 1px dashed #ccc;
            padding-top: 1rem;
        }
        @media (max-width: 700px) {
            .feedback-main {
                padding: 1.2rem 0.5rem 1rem 0.5rem;
            }
            .upload-area {
                flex-direction: column;
                gap: 1.2rem;
            }
            .bg-svg.topleft, .bg-svg.bottomright {
                width: 100px;
                height: 100px;
            }
        }
        /* Decorative SVGs */
        .bg-svg {
            position: absolute;
            z-index: 1;
            opacity: 0.13;
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
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="feedback-main">
    <div class="camp-logo">TasikBiruCamps</div>
    <div class="feedback-title">We Value Your Feedback</div>
    <div class="feedback-desc">Help us improve by sharing your experience below.</div>
    <?php if (!$has_paid_bookings): ?>
        <div class="thankyou" style="color:#bfa100;">This is a preview. Please make a booking and payment to submit real feedback.</div>
    <?php endif; ?>
    <form action="submit_feedback.php" method="POST" enctype="multipart/form-data" <?php echo !$has_paid_bookings ? 'class="form-disabled"' : ''; ?>>
        <div class="form-section">
            <label for="package_id" style="font-weight:500;">Select Package</label>
            <select class="package-select" name="package_id" id="package_id" required <?php echo !$has_paid_bookings ? 'disabled' : ''; ?>>
                <option value="">Select a package...</option>
                <?php foreach ($packages as $package): ?>
                    <option value="<?php echo $package['package_id']; ?>">
                        <?php echo htmlspecialchars($package['package_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-section">
            <label style="font-weight:500;">Your Rating</label>
            <div class="star-rating">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <input type="radio" name="rating" id="star<?php echo $i; ?>" value="<?php echo $i; ?>" <?php echo !$has_paid_bookings ? 'disabled' : ''; ?>>
                    <label for="star<?php echo $i; ?>" title="<?php echo $i; ?> stars">&#9733;</label>
                <?php endfor; ?>
            </div>
        </div>
        <div class="form-section">
            <label for="feedback" style="font-weight:500;">Your Comments</label>
            <textarea name="feedback" id="feedback" placeholder="Type your comment here..." required <?php echo !$has_paid_bookings ? 'disabled' : ''; ?>></textarea>
        </div>
        <div class="form-section media-section">
            <span class="media-label">Add Photos and Video (optional)</span>
            <div class="upload-area">
                <div class="upload-box" onclick="document.getElementById('photoInput').click();">
                    <i class="fas fa-camera"></i>
                    <p>Photo</p>
                    <input type="file" id="photoInput" name="photo" accept="image/*" style="display: none" <?php echo !$has_paid_bookings ? 'disabled' : ''; ?> onchange="previewPhoto(event)">
                    <img id="photoPreview" class="upload-preview" src="#" alt="Photo Preview" />
                </div>
                <div class="upload-box" onclick="document.getElementById('videoInput').click();">
                    <i class="fas fa-video"></i>
                    <p>Video</p>
                    <input type="file" id="videoInput" name="video" accept="video/*" style="display: none" <?php echo !$has_paid_bookings ? 'disabled' : ''; ?> onchange="previewVideo(event)">
                    <video id="videoPreview" class="upload-preview video" controls></video>
                </div>
            </div>
        </div>
        <button type="submit" class="submit-btn" <?php echo !$has_paid_bookings ? 'disabled' : ''; ?>>
            <?php echo !$has_paid_bookings ? 'Preview Mode' : 'Submit Feedback'; ?>
        </button>
    </form>
    <div class="footer">
        TasikBiruCamps, Jalan Tasik Biru, 12345 Kampung Damai, Malaysia<br>
        Phone: 012-3456789 &nbsp;|&nbsp; Email: info@tasikbirucamps.com<br>
        <em>Thank you for helping us improve!</em>
    </div>
</div>
<script>
function previewPhoto(event) {
    const input = event.target;
    const preview = document.getElementById('photoPreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.src = '#';
        preview.style.display = 'none';
    }
}
function previewVideo(event) {
    const input = event.target;
    const preview = document.getElementById('videoPreview');
    if (input.files && input.files[0]) {
        const fileURL = URL.createObjectURL(input.files[0]);
        preview.src = fileURL;
        preview.style.display = 'block';
    } else {
        preview.src = '';
        preview.style.display = 'none';
    }
}
</script>
<!-- Decorative SVGs -->
<svg class="bg-svg topleft" viewBox="0 0 200 200" fill="none"><ellipse cx="100" cy="100" rx="100" ry="100" fill="#bca48a"/></svg>
<svg class="bg-svg bottomright" viewBox="0 0 200 200" fill="none"><rect x="0" y="0" width="200" height="200" rx="60" fill="#8c6d52"/></svg>
</body>
</html>
