<?php
include 'db_connection.php';
$reviews = [];
$sql = "SELECT f.*, u.username, p.package_name FROM feedback f JOIN users u ON f.user_id = u.user_id JOIN packages p ON f.package_id = p.package_id ORDER BY f.rating DESC, f.feedback_id DESC LIMIT 6";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $reviews[] = $row;
}
$review_count = 0;
$average_rating = 0;
$count_result = $conn->query("SELECT COUNT(*) as total, AVG(rating) as avg_rating FROM feedback");
if ($count_row = $count_result->fetch_assoc()) {
    $review_count = $count_row['total'];
    $average_rating = $count_row['avg_rating'] ? round($count_row['avg_rating'], 1) : 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - TasikBiruCamps</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .footer-socials-row {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 32px;
  margin-bottom: 24px;
}
.footer-socials-row a {
  color: #555;
  font-size: 2.2rem;
  transition: color 0.2s;
}
.footer-socials-row a:hover {
  color: #28a745;
}
        .main-footer {
  background: #222;
  color: #fff;
  margin-top: auto;
  padding: 40px 0 0 0;
  font-family: 'Poppins', sans-serif;
}
.footer-container {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 32px;
  gap: 32px;
}
.footer-col {
  flex: 1 1 180px;
  min-width: 180px;
  margin-bottom: 24px;
}
.footer-col h4 {
  font-size: 1.1rem;
  margin-bottom: 12px;
  color: #c7b491;
}
.footer-col a, .footer-col p {
  display: block;
  color: #fff;
  text-decoration: none;
  margin-bottom: 8px;
  font-size: 0.98rem;
  transition: color 0.2s;
}

.footer-col a:hover {
  color: #28a745;
}
.logo-col {
  flex: 1 1 220px;
  min-width: 220px;
}
.footer-logo {
  width: 120px;
  margin-bottom: 18px;
}
.footer-socials a {
  color: #fff;
  margin-right: 12px;
  font-size: 1.3rem;
  transition: color 0.2s;
}
.footer-socials a:hover {
  color: #28a745;
}
.footer-bottom {
  width: 100vw;
  left: 0;
  position: relative;
  display: flex;
  justify-content: center;
  align-items: center;
  text-align: center;
  padding: 18px 0 10px 0;
  background: #181818;
  color: #bbb;
  font-size: 0.95rem;
  margin-top: 12px;
  box-sizing: border-box;
}
@media (max-width: 900px) {
  .footer-container {
    flex-direction: column;
    align-items: center;
    gap: 0;
  }
  .footer-col {
    min-width: 0;
    margin-bottom: 18px;
    text-align: center;
  }
}
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            font-family: 'Poppins', 'Montserrat', Arial, sans-serif;
            min-height: 100vh;
            position: relative;
            color: #fff;
            overflow-x: hidden;
        }
        body::before {
            content: "";
            position: fixed;
            top: 0; left: 0;
            width: 100vw;
            height: 100vh;
            background-image: url('Assets/aboutB.jpg');
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            image-rendering: auto;
            z-index: -2;
            filter: none;
        }
        body::after {
            content: "";
            position: fixed;
            top: 0; left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.2); /* Lower opacity for clearer background */
            z-index: -1;
        }
        .hero-navbar-bg {
            background: rgba(20, 20, 20, 0.85);
            width: 100vw;
            position: relative;
            z-index: 2;
        }
        .main-bg {
            background: url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1500&q=80') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            width: 100vw;
            position: relative;
            z-index: 1;
        }
        .hero {
            background: none !important;
            color: #2e8b57;
            padding: 80px 0 260px 0;
            text-align: center;
            position: relative;
            box-shadow: none !important;
        }
        .hero h1 {
            color:rgb(255, 255, 255) !important;
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 24px;
            letter-spacing: 1px;
        }
        .hero p {
            color: #222 !important;
            font-size: 1.3rem;
            margin-bottom: 32px;
            text-shadow: 0 2px 8px rgba(255,255,255,0.15);
        }
        .hero .cta-btn {
            background: #28a745;
            color: #fff;
            border: none;
            border-radius: 30px;
            padding: 16px 38px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(40,167,69,0.13);
            transition: background 0.2s, transform 0.2s, text-decoration 0.2s;
            text-decoration: none;
        }
        .hero .cta-btn:hover {
            background: #218838;
            transform: translateY(-2px) scale(1.04);
            text-decoration: underline;
        }
        .about-section, .main-bg, .hero, .features-row, .team-section {
            position: relative;
            z-index: 1;
        }
        .about-section {
            background: rgba(20, 20, 20, 0.85) !important;
            color: #fff;
            box-shadow: none;
            text-align: center;
            padding: 80px 20px 40px 20px;
            max-width: 900px;
            margin: 0 auto;
        }
        .about-section h2, .about-section p {
            color: #fff;
            text-shadow: 0 2px 8px rgba(0,0,0,0.25);
        }
        .about-section h2 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 24px;
        }
        .about-section p {
            font-size: 1.3rem;
            margin-bottom: 32px;
        }
        .about-img {
            flex: 1 1 320px;
            min-width: 280px;
            max-width: 400px;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(80,80,160,0.13);
        }
        .about-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 18px;
        }
        .about-content {
            background: rgba(20, 20, 20, 0.85);
            color: #fff;
            border-radius: 18px;
            padding: 24px;
        }
        .about-content h2, .about-content p {
            color: #fff;
        }
        .about-content h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 18px;
        }
        .about-content p {
            font-size: 1.1rem;
            margin-bottom: 18px;
            line-height: 1.7;
        }
        .features-row {
            display: flex;
            gap: 32px;
            margin: 48px auto 0 auto;
            justify-content: center;
            flex-wrap: wrap;
        }
        .feature-card {
            background: rgba(30, 30, 30, 0.95);
            color: #fff;
            border-color: rgba(40,167,69,0.13);
            border-radius: 22px;
            box-shadow: 0 8px 32px rgba(40,167,69,0.10), 0 1.5px 8px rgba(80,80,160,0.08);
            padding: 36px 32px 28px 32px;
            min-width: 260px;
            max-width: 340px;
            flex: 1 1 260px;
            text-align: center;
            transition: transform 0.22s cubic-bezier(.4,2,.6,1), box-shadow 0.22s, background 0.22s;
            margin-bottom: 18px;
            position: relative;
            overflow: hidden;
        }
        .feature-card:before {
            display: none;
        }
        .feature-card:hover {
            transform: translateY(-10px) scale(1.045);
            box-shadow: 0 16px 48px rgba(40,167,69,0.18), 0 2px 12px rgba(80,80,160,0.10);
            background: #222;
            border-color: #28a745;
        }
        .feature-card i {
            font-size: 2.7rem;
            color: #6f74c6;
            margin-bottom: 18px;
            z-index: 1;
            position: relative;
            transition: color 0.2s;
        }
        .feature-card:hover i {
            color: #28a745;
        }
        .feature-card h3 {
            font-size: 1.25rem;
            color: #fff;
            margin-bottom: 14px;
            font-weight: 700;
            z-index: 1;
            position: relative;
        }
        .feature-card p {
            color: #fff;
            font-size: 1.08rem;
            z-index: 1;
            position: relative;
        }
        @media (max-width: 900px) {
            .features-row { flex-direction: column; align-items: center; gap: 24px; }
            .feature-card { max-width: 95vw; }
        }
        .team-section {
            background: rgba(20, 20, 20, 0.85);
            color: #fff;
            border-radius: 18px;
            padding: 24px;
            width: 80%;
            max-width: 1200px;
            margin: 60px auto 60px auto;
            text-align: center;
        }
        .team-section h2 {
            color: #fff;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 30px;
        }
        .team-row {
            display: flex;
            gap: 32px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .team-card {
            background: rgba(30,30,30,0.95) !important;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(80,80,160,0.10);
            padding: 28px 22px;
            min-width: 200px;
            max-width: 240px;
            flex: 1 1 200px;
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .team-card:hover {
            transform: translateY(-8px) scale(1.04);
            box-shadow: 0 8px 32px rgba(80,80,160,0.18);
        }
        .team-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #e0e7ff;
            margin: 0 auto 14px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: #6f74c6;
            overflow: hidden;
        }
        .team-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        .team-card h4 {
            margin: 0 0 6px 0;
            font-size: 1.1rem;
            color: #fff !important;
            font-weight: 700;
            text-shadow: 0 2px 8px rgba(0,0,0,0.25);
        }
        .team-card p {
            color: #fff !important;
            font-size: 0.98rem;
            text-shadow: 0 2px 8px rgba(0,0,0,0.25);
        }
        @media (max-width: 900px) {
            .about-section, .features-row, .team-row { flex-direction: column; align-items: center; }
            .about-img, .about-content { max-width: 100%; }
        }
        .hero-navbar {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.8rem 3vw;
            background: rgba(20, 20, 20, 0.75);
            box-sizing: border-box;
        }
        .hero-logo {
            font-size: 2rem;
            font-weight: bold;
            color: #fff;
            letter-spacing: 1px;
        }
        .hero-nav-links {
            list-style: none;
            display: flex;
            gap: 2.5rem;
            margin: 0;
            padding: 0;
        }
        .hero-nav-links li a {
            color: #fff;
            text-decoration: none;
            font-size: 1.2rem;
            font-weight: 500;
            transition: color 0.2s;
        }
        .hero-nav-links li a:hover {
            color: #28a745;
        }
        @media (max-width: 700px) {
            .hero-navbar {
                flex-direction: column;
                align-items: flex-start;
                padding: 1rem 1vw;
            }
            .hero-nav-links {
                gap: 1.2rem;
                font-size: 1rem;
            }
        }
        .hero-section {
            width: 100vw;
            min-height: 60vh;
            background: url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1500&q=80') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
            position: relative;
        }
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(20, 20, 20, 0.55);
            z-index: 1;
        }
        .hero-content {
            position: relative;
            z-index: 2;
            color: #fff;
            padding: 80px 20px 60px 20px;
        }
        .hero-content h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 24px;
            letter-spacing: 1px;
        }
        .hero-content p {
            font-size: 1.3rem;
            margin-bottom: 32px;
            color: #e0e7ff;
        }
        .discover-btn {
            background: #ff9800;
            color: #fff;
            border: none;
            border-radius: 30px;
            padding: 16px 38px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(255,152,0,0.13);
            transition: background 0.2s, transform 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        .discover-btn:hover {
            background: #e65100;
            transform: translateY(-2px) scale(1.04);
        }
        @media (max-width: 700px) {
            .hero-content h1 { font-size: 2rem; }
            .hero-content p { font-size: 1rem; }
        }
        /* --- Our Story Animated Section --- */
        .our-story-section {
            width: 85%;
            max-width: 1400px;
            margin: 60px auto 0 auto;
            border-radius: 40px;
            box-shadow: 0 8px 32px rgba(80,80,160,0.10);
            background: rgba(20,20,20,0.85) !important;
            display: flex;
            gap: 48px;
            align-items: stretch;
            flex-wrap: wrap;
            padding-left: max(4vw, 16px);
            padding-right: max(4vw, 16px);
            padding-top: 48px;
            padding-bottom: 32px;
            box-sizing: border-box;
            overflow-x: hidden;
        }
        .our-story-img-card {
            display: flex;
            align-items: stretch;
            justify-content: center;
            flex: 1 1 350px;
            min-width: 280px;
            animation: fadeInLeft 1s;
            max-height: 320px;
        }
        .our-story-img-card img {
            width: auto;
            height: 320px;
            max-width: 100%;
            max-height: 320px;
            display: block;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(80,80,160,0.13);
            object-fit: cover;
            margin: 0;
        }
        .our-story-content {
            flex: 2 1 400px;
            min-width: 300px;
            animation: fadeInRight 1s;
            display: flex;
            flex-direction: column;
            max-height: 320px;
            overflow-y: auto;
            padding-right: 8px;
            scrollbar-width: thin;
            scrollbar-color: #c7b491 #222;
        }
        .our-story-content::-webkit-scrollbar {
            width: 8px;
        }
        .our-story-content::-webkit-scrollbar-thumb {
            background: #c7b491;
            border-radius: 8px;
        }
        .our-story-content::-webkit-scrollbar-track {
            background: #222;
        }
        .our-story-content h2 {
            color: #fff !important;
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 18px;
            text-shadow: 0 2px 8px rgba(0,0,0,0.25);
        }
        .our-story-content p {
            color: #fff !important;
            font-size: 1.15rem;
            font-weight: 500;
            text-shadow: 0 2px 8px rgba(0,0,0,0.25);
        }
        @keyframes fadeInLeft {
            from { opacity: 0; transform: translateX(-40px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes fadeInRight {
            from { opacity: 0; transform: translateX(40px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @media (max-width: 900px) {
            .our-story-section {
                flex-direction: column;
                gap: 24px;
                padding: 32px 2vw;
                border-radius: 0 0 18px 18px;
            }
            .our-story-img-card, .our-story-content { width: 100%; min-width: 0; }
            .our-story-img-card { justify-content: center; align-items: center; }
            .our-story-img-card img {
                width: 100%;
                height: auto;
                max-width: 100%;
                max-height: 220px;
            }
            .our-story-content {
                max-height: 220px;
            }
        }
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            background-color: rgba(0, 0, 0, 0.6);
        }
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: #fff !important;
        }
        .nav-links {
            list-style: none;
            display: flex;
            gap: 1.5rem;
            padding: 0;
            margin: 0;
        }
        .nav-links li a {
            color: #fff;
            text-decoration: none;
            font-weight: 500;
        }
        body, h1, h2, h3, p, a {
            color: #fff !important;
        }
        .hero p, .about-content p, .our-story-content p, .team-card h4, .team-card p {
            color: #e0e7ff !important;
        }
        .our-story-content h2 {
            color: #fff !important;
            font-weight: 800;
            text-shadow: 0 2px 8px rgba(0,0,0,0.25);
        }
        .our-story-content p {
            color: #fff !important;
            font-size: 1.15rem;
            font-weight: 500;
            text-shadow: 0 2px 8px rgba(0,0,0,0.25);
        }
        .team-card {
            background: rgba(30,30,30,0.95) !important;
        }
        .team-card h4 {
            font-weight: 700;
        }
        .team-section h2,
        .our-story-content h2 {
            color: #fff !important;
            font-weight: 800;
            text-shadow: 0 2px 8px rgba(0,0,0,0.25);
        }
        .reviews-section {
            width: 80%;
            max-width: 1200px;
            margin: 60px auto 0 auto;
        }
        .reviews-title {
            text-align: center;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 30px;
            color: #222;
        }
        .reviews-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            width: 100%;
            gap: 24px;
        }
        .review-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(80,80,160,0.10);
            padding: 24px 20px 16px 20px;
            min-width: 280px;
            max-width: 340px;
            flex: 1 1 calc(33.333% - 16px);
            max-width: calc(33.333% - 16px);
            min-width: 280px;
            box-sizing: border-box;
            text-align: left;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            margin-bottom: 24px;
        }
        .review-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
        }
        .review-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e7ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: #6f74c6;
        }
        .review-username {
            font-weight: 700;
            color: #222;
            font-size: 1.05rem;
        }
        .review-package {
            font-size: 0.95rem;
            color: #888;
        }
        .review-rating {
            margin: 6px 0 8px 0;
        }
        .star {
            color: #FFD600;
            font-size: 1.1rem;
        }
        .star.gray {
            color: #ccc;
        }
        .review-comment {
            color: #222;
            font-size: 1rem;
            margin-bottom: 10px;
        }
        .review-image {
            width: 100%;
            border-radius: 12px;
            object-fit: cover;
            margin-top: 10px;
        }
        .media-thumb {
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
            margin-top: 10px;
        }
        .media-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0; top: 0;
            width: 100vw; height: 100vh;
            background: rgba(0,0,0,0.8);
            justify-content: center;
            align-items: center;
        }
        .media-modal-content {
            background: none;
            border-radius: 12px;
            max-width: 90vw;
            max-height: 80vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .media-modal-content img,
        .media-modal-content video {
            max-width: 90vw;
            max-height: 80vh;
            border-radius: 12px;
            background: #000;
        }
        .media-modal-close {
            position: absolute;
            top: 24px;
            right: 40px;
            color: #fff;
            font-size: 2.5rem;
            font-weight: bold;
            cursor: pointer;
            z-index: 10001;
        }
        .review-video {
            width: 100%;
            border-radius: 12px;
            margin-top: 10px;
            background: #000;
        }
        .location-section {
            width: 80%;
            max-width: 1200px;
            margin: 40px auto 0 auto;
            text-align: center;
            padding: 48px 0 48px 0;
            min-height: 650px;
            border-radius: 40px;
            box-sizing: border-box;
        }
        .location-section h2 {
            color: #fff;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 18px;
        }
        .location-section p {
            color: #e0e7ff;
            font-size: 1.1rem;
            margin-bottom: 24px;
        }
        .location-section iframe {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(80,80,160,0.13);
        }
        .custom-review-summary {
            display: flex;
            align-items: center;
            gap: 12px;
            justify-content: center;
            margin-bottom: 10px;
        }
        .custom-review-summary span {
            font-size: 1.2rem;
            font-weight: 700;
            color: #222;
        }
        .custom-review-summary span:last-child {
            cursor: pointer;
        }
        .custom-gmap-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(80,80,160,0.13);
            padding: 22px 28px 18px 28px;
            max-width: 370px;
            margin: 0 auto 24px auto;
            text-align: left;
            font-family: 'Poppins', 'Montserrat', Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        .gmap-card-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #222;
            margin-bottom: 4px;
        }
        .gmap-card-address {
            color: #555;
            font-size: 1.02rem;
            margin-bottom: 14px;
        }
        .gmap-card-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 2px;
        }
        .gmap-card-rating {
            color: #222;
            font-size: 1.08rem;
            font-weight: 600;
        }
        .gmap-card-stars .star {
            color: #FFD600;
            font-size: 1.15rem;
        }
        .gmap-card-stars .gray {
            color: #ccc;
        }
        .gmap-card-reviews {
            color: #1a73e8;
            font-size: 1.08rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            margin-left: 2px;
        }
        .gmap-card-reviews:hover {
            text-decoration: underline;
        }
        .location-info-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(80,80,160,0.13);
            padding: 28px 32px 18px 32px;
            max-width: 370px;
            margin: 0 auto 24px auto;
            text-align: left;
            font-family: 'Poppins', 'Montserrat', Arial, sans-serif;
        }
        .location-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: #222;
            margin-bottom: 4px;
        }
        .location-address {
            color: #888;
            font-size: 1.08rem;
            margin-bottom: 18px;
        }
        .location-rating-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 2px;
        }
        .location-rating-value {
            color: #222;
            font-size: 1.18rem;
            font-weight: 700;
        }
        .location-rating-stars .star {
            color: #FFD600;
            font-size: 1.18rem;
            vertical-align: middle;
        }
        .location-rating-stars .gray {
            color: #ccc;
        }
        .location-review-link {
            color: #1a73e8;
            font-size: 1.08rem;
            font-weight: 600;
            text-decoration: none;
            margin-left: 8px;
            cursor: pointer;
            transition: color 0.2s;
        }
        .location-review-link:hover {
            text-decoration: underline;
            color: #1558b0;
        }
        .location-banner {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto 24px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255, 255, 255, 0.89);
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(80,80,160,0.13);
            padding: 22px 36px;
            font-family: 'Poppins', 'Montserrat', Arial, sans-serif;
        }
        .location-banner-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: #222;
            margin-right: 12px;
        }
        .location-banner-address {
            color: #888;
            font-size: 1.08rem;
        }
        .location-banner-rating {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .location-banner-value {
            color: #222;
            font-size: 1.18rem;
            font-weight: 700;
        }
        .location-banner-stars .star {
            color: #FFD600;
            font-size: 1.18rem;
            vertical-align: middle;
        }
        .location-banner-stars .gray {
            color: #ccc;
        }
        .location-banner-review {
            background: #28a745;
            color: #fff;
            font-size: 1.08rem;
            font-weight: 700;
            text-decoration: none;
            margin-left: 12px;
            cursor: pointer;
            border: none;
            border-radius: 16px;
            padding: 8px 32px;
            box-shadow: none;
            transition: background 0.2s, color 0.2s, text-decoration 0.2s;
            display: inline-block;
        }
        .location-banner-review:hover {
            text-decoration: underline;
            background: #28a745;
            color: #fff;
        }
        .leaflet-control-zoom-in, .leaflet-control-zoom-out {
            font-size: 1.3rem !important;
            font-weight: normal;
            background: #fff !important;
            color: #222 !important;
            border: 1px solidrgba(224, 224, 224, 0.71) !important;
            border-radius: 6px !important;
            box-shadow: 0 1px 4px rgba(80,80,160,0.08);
            width: 32px !important;
            height: 32px !important;
            line-height: 28px !important;
            margin-bottom: 4px;
            transition: background 0.2s, color 0.2s, border 0.2s;
        }
        .leaflet-control-zoom-in:hover, .leaflet-control-zoom-out:hover {
            background:rgba(255, 227, 227, 0.46) !important;
            color: #1a73e8 !important;
            border: 1px solid #b3d1ff !important;
        }
        .scroll-down-guide {
            position: absolute;
            left: 50%;
            bottom: 40px;
            transform: translateX(-50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
            z-index: 10;
            animation: fadeInUp 1.2s;
        }
        .scroll-down-guide .arrow {
            width: 32px;
            height: 32px;
            border-left: 4px solid #fff;
            border-bottom: 4px solid #fff;
            transform: rotate(-45deg);
            margin-top: 0;
            animation: bounce 1.5s infinite;
        }
        @keyframes bounce {
            0%, 100% { transform: translateY(0) rotate(-45deg);}
            50% { transform: translateY(12px) rotate(-45deg);}
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px);}
            to { opacity: 1; transform: translateY(0);}
        }
        .hero {
            position: relative; /* Ensure .scroll-down-guide is positioned relative to .hero */
        }
        .footer-ig-handle {
  color: #555;
  font-size: 1.1rem;
  margin-left: 8px;
  font-family: 'Poppins', sans-serif;
  vertical-align: middle;
}
    /* Centered logo above footer, with extra margin below */
    .footer-main-logo {
      display: block;
      margin: 0 auto 36px auto;
      width: 180px;
      height: auto;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(80,80,160,0.10);
      background: #fff;
      object-fit: contain;
    }
    .footer-socials-row {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 32px;
      margin-bottom: 24px;
    }
    .logo-col {
      display: flex;
      align-items: center;
      justify-content: center;
      min-width: 220px;
      max-width: 260px;
    }
    .footer-main-logo {
      width: 170px;
      height: auto;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(80,80,160,0.10);
      background: #fff;
      object-fit: contain;
      margin: 0 auto;
      display: block;
    }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">TasikBiruCamps</div>
        <ul class="nav-links">
            <li><a href="homepage.php">Home</a></li>
            <li><a href="campsites.php">Campsites</a></li>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>
        </ul>
    </nav>
    <div class="hero">
        <h1 class="font_0 wixui-rich-text__text" style="text-align:center; font-size:100px; font-family:'Times New Roman', serif;">About TasikBiruCamps</h1>
        <p>
  We are passionate about creating unforgettable outdoor experiences that bring people closer to nature and to each other.<br>
  At TasikBiru Camps, we offer comfort, adventure, and serenity all in one place.<br>
  Every moment is thoughtfully crafted to inspire connection, relaxation, and lasting memories.
</p>
        <a href="campsites.php" class="cta-btn">Explore Our Campsites</a>
        <div class="scroll-down-guide" onclick="scrollToAboutSection()">
            <div class="arrow"></div>
        </div>
    </div>
    <div class="our-story-section" id="about-section">
        <div class="our-story-img-card">
            <img src="ourStoryBackground.jpg" alt="TasikBiruCamps Nature" />
        </div>
        <div class="our-story-content">
            <h2>Our Story</h2>
            <p>TasikBiruCamps was founded in 2018 by a group of passionate nature enthusiasts, educators, and outdoor adventure experts with a shared vision: to make camping in Malaysia not only more accessible, but also safer, more meaningful, and more enjoyable for people of all ages. Inspired by the breathtaking beauty of Tasik Biru and frustrated by the lack of structured outdoor experiences, we saw an opportunity to transform the way people experience nature.</p>
            <p>From humble beginnings, TasikBiruCamps has grown into a trusted platform that connects adventurers, families, students, and organizations with curated, high-quality camping experiences in one of Malaysia’s most serene and picturesque destinations. We believe that nature is a powerful teacher capable of building confidence, strengthening teamwork, and inspiring self-discovery. That belief is at the heart of every program and package we design.</p>
            <p>Today, TasikBiruCamps proudly partners with carefully selected campsite operators and outdoor facilitators to offer a variety of adventure-filled and educational packages. These include team-building activities, water-based challenges, survival skills training, leadership development, and much more all conducted in a safe and supportive environment.</p>
            <p>Our mission is to inspire more people to reconnect with nature, form meaningful bonds, and create unforgettable memories. Whether you're a student attending a school camp, a company team looking to recharge, or a family craving an outdoor getaway, TasikBiruCamps welcomes you to discover the beauty, thrill, and tranquility of the outdoors right here at Kem Tasik Biru.</p>
            <p>Let us guide you on your next adventure, and together, we’ll make every moment count under the open sky.</p>
        </div>
    </div>
    <div class="location-section">
        <h2 style="color:#fff;font-size:2rem;font-weight:700;margin-bottom:18px;">Our Location</h2>
        <div class="location-banner">
            <div>
                <span class="location-banner-title">Kem Tasik Biru</span>
                <span class="location-banner-address">77000 Jasin, Malacca</span>
            </div>
            <div class="location-banner-rating">
                <span class="location-banner-value"><?php echo $average_rating; ?></span>
                <span class="location-banner-stars">
                    <?php
                    $fullStars = floor($average_rating);
                    $halfStar = ($average_rating - $fullStars) >= 0.5;
                    for ($i = 0; $i < $fullStars; $i++) echo '<span class="star">&#9733;</span>';
                    if ($halfStar) echo '<span class="star">&#189;</span>';
                    for ($i = $fullStars + $halfStar; $i < 5; $i++) echo '<span class="star gray">&#9733;</span>';
                    ?>
                </span>
                <a href="#reviews-section" class="location-banner-review" onclick="document.getElementById('reviews-section').scrollIntoView({behavior:'smooth'});return false;">
                    Review
                </a>
            </div>
        </div>
        <div id="customMap" style="width:100%;height:550px;border-radius:24px;box-shadow:0 4px 24px rgba(80,80,160,0.13);margin:0 auto;"></div>
    </div>
    <div class="reviews-section" id="reviews-section">
        <h2 class="reviews-title">What Our Campers Say</h2>
        <div class="reviews-row">
            <?php foreach ($reviews as $review): ?>
            <div class="review-card">
                <div class="review-header">
                    <div class="review-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div>
                        <div class="review-username"><?php echo htmlspecialchars($review['username']); ?></div>
                        <div class="review-package"><?php echo htmlspecialchars($review['package_name']); ?></div>
                    </div>
                </div>
                <div class="review-rating">
                    <?php
                    $maxStars = 5;
                    for ($i = 0; $i < $review['rating']; $i++): ?>
                        <span class="star">&#9733;</span>
                    <?php endfor;
                    for ($i = $review['rating']; $i < $maxStars; $i++): ?>
                        <span class="star gray">&#9733;</span>
                    <?php endfor; ?>
                </div>
                <div class="review-comment">
                    <?php echo htmlspecialchars($review['comment']); ?>
                </div>
                <?php if (!empty($review['photo_path'])): ?>
                    <button class="media-thumb" onclick="openMediaModal('<?php echo htmlspecialchars($review['photo_path']); ?>', 'image')">
                        <img src="<?php echo htmlspecialchars($review['photo_path']); ?>" alt="Review Photo" style="width:60px;height:60px;object-fit:cover;border-radius:8px;">
                    </button>
                <?php endif; ?>
                <?php if (!empty($review['video_path'])): ?>
                    <video class="review-video" controls>
                        <source src="<?php echo htmlspecialchars($review['video_path']); ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="team-section">
        <h2>Meet Our Team</h2>
        <div class="team-row">
            <div class="team-card">
                <div class="team-avatar"><img src="Assets/syahidah.jpg" alt="Team Member 1"></div>
                <h4>Siti Nur Syahidah</h4>
                <p>Project and System Manager</p>
            </div>
            <div class="team-card">
                <div class="team-avatar"><img src="Assets/siti aisyah.jpg" alt="Team Member 2"></div>
                <h4>Siti Aisyah</h4>
                <p>System Analyst</p>
            </div>
            <div class="team-card">
                <div class="team-avatar"><img src="Assets/lutfiah.jpg" alt="Team Member 3"></div>
                <h4>Lutfiah Qistina</h4>
                <p>System Programmer</p>
            </div>
            <div class="team-card">
                <div class="team-avatar"><img src="Assets/ridhwan.jpg" alt="Team Member 4"></div>
                <h4>Muhammad Ridhwan</h4>
                <p>System Designer</p>
            </div>
        </div>
    </div>
    <div id="mediaModal" class="media-modal" onclick="closeMediaModal()">
        <span class="media-modal-close" onclick="closeMediaModal()">&times;</span>
        <div class="media-modal-content" id="mediaModalContent"></div>
    </div>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    function openMediaModal(src, type) {
        var modal = document.getElementById('mediaModal');
        var content = document.getElementById('mediaModalContent');
        if (type === 'image') {
            content.innerHTML = '<img src="' + src + '" alt="Review Photo">';
        } else if (type === 'video') {
            content.innerHTML = '<video src="' + src + '" controls autoplay></video>';
        }
        modal.style.display = 'flex';
    }
    function closeMediaModal() {
        var modal = document.getElementById('mediaModal');
        var content = document.getElementById('mediaModalContent');
        modal.style.display = 'none';
        content.innerHTML = '';
    }
    document.addEventListener('DOMContentLoaded', function() {
        var modal = document.getElementById('mediaModal');
        modal.onclick = function(e) {
            if (e.target === modal) closeMediaModal();
        }
    });
    function initMap() {
        var map = L.map('customMap').setView([2.312, 102.489], 15); // Replace with your coordinates
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        L.marker([2.312, 102.489]).addTo(map)
            .bindPopup('Kem Tasik Biru<br>77000 Jasin, Malacca')
            .openPopup();
    }
    window.onload = initMap;
    function scrollToAboutSection() {
        document.getElementById('about-section').scrollIntoView({ behavior: 'smooth' });
    }
    </script>
    
    <!-- Removed duplicate footer here -->
    
    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-col logo-col">
                <img src="Assets/logoTasik.jpg" alt="TasikBiru Logo" class="footer-main-logo">
            </div>
            <div class="footer-col">
                <h4>Links</h4>
                <a href="about.php">About Us</a>
                <a href="campsites.php">Campsites</a>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            </div>
            <div class="footer-col">
                <h4>Contact</h4>
                <p><i class="fas fa-envelope" style="margin-right:8px;"></i>info@tasikbirucamps.com</p>
                <p><i class="fas fa-phone" style="margin-right:8px;"></i>012-2252945</p>
            </div>
            <div class="footer-col">
                <h4>Legal</h4>
                <a href="#">Terms & Conditions</a>
                <a href="#">Privacy Policy</a>
            </div>
        </div>
        <div class="footer-socials-row">
            <span class="footer-logo-inline" style="width:auto;height:40px;background:#c7b491;color:#222;display:flex;align-items:center;justify-content:center;border-radius:8px;font-weight:bold;font-size:1.3rem;padding:0 24px 0 24px;margin-right:24px;">TasikBiru</span>
            <a href="https://www.facebook.com/kemtasikbirumelaka" target="_blank"><i class="fab fa-facebook-f"></i></a>
            <a href="https://www.tiktok.com/@kemtasikbiruofficial" target="_blank"><i class="fab fa-tiktok"></i></a>
            <a href="https://instagram.com/kemtasikbiru" target="_blank"><i class="fab fa-instagram"></i></a>
            <span class="footer-ig-handle">@kemtasikbiru</span>
        </div>
      <div class="footer-bottom">
        &copy; 2025 TasikBiru Camps. All rights reserved.
      </div>
    </footer>
</body>
</html>