<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Camping Reservation</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">

  <style>
    body {
      margin: 0;
      font-family: 'Montserrat', sans-serif;
      min-height: 100vh;
      position: relative;
      color: #fff;
      overflow-x: hidden;
      display: flex;
      flex-direction: column;
    }

    body::before {
      content: "";
      position: fixed;
      top: 0; left: 0;
      width: 100%;
      height: 100%;
      background-image: url('backgroundcamp.jpg'); /* Your custom image */
      background-size: cover;
      background-position: center center;
      background-repeat: no-repeat;
      background-attachment: fixed;
      z-index: -2;
    }

    /* Add a subtle white overlay for vibrancy */
    body::before {
      pointer-events: none;
      box-shadow: 0 0 0 100vw rgba(255,255,255,0.04) inset;
    }

    body::after {
      content: "";
      position: fixed;
      top: 0; left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.10); /* Very light dark overlay */
      z-index: -1;
    }

    .header, .campsites-section, .footer {
      text-align: center;
      padding: 2rem;
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

    .hero {
      min-height: 80vh;
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
      align-items: center;
      padding-top: 7rem;
      padding-bottom: 0;
      padding-left: 2rem;
      padding-right: 2rem;
    }

    .hero h1,
    .hero p,
    .btn {
      margin-bottom: 0;
    }

    .hero h1 {
      font-size: 3.5rem;
      font-family: 'Times New Roman', serif;
      font-weight: normal;
      text-align: center;
    }

    .hero p {
      font-size: 1.7rem;
      font-family: 'Times New Roman', serif;
      text-align: center;
    }

    .btn {
      display: inline-block;
      padding: 1rem 2.1rem;
      background-color: #28a745;
      color: #fff;
      border-radius: 5px;
      text-decoration: none;
      font-weight: bold;
      font-family: 'Times New Roman', serif;
      font-size: 1.3rem;
      margin-bottom: 0;
      margin-top: 1.2rem;
    }

    .footer {
      background-color: rgba(0, 0, 0, 0.6);
      padding: 1rem;
      margin-top: 2rem;
    }

    /* Fade-up animation */
    .fade-up {
      opacity: 0;
      transform: translateY(40px);
      transition: opacity 1.2s cubic-bezier(0.4,0,0.2,1), transform 1.2s cubic-bezier(0.4,0,0.2,1);
    }
    .fade-up.visible {
      opacity: 1;
      transform: translateY(0);
    }

    .why-camp-section {
      background: #c7b491;
      padding: 4rem 0 3rem 0;
      text-align: center;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .why-camp-card {
      background: #fff;
      border-radius: 2.2rem;
      box-shadow: 0 6px 32px rgba(0,0,0,0.10);
      padding: 2.5rem 2.5rem 2.2rem 2.5rem;
      max-width: 1200px;
      width: 100%;
      margin: 0 auto;
    }
    .why-camp-title {
      font-size: 2.8rem;
      font-weight: 700;
      color: #c7b491;
      margin-bottom: 2.5rem;
      letter-spacing: 1px;
    }
    .why-camp-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      grid-template-rows: repeat(2, 220px);
      gap: 1.2rem;
      max-width: 1100px;
      margin: 0 auto;
    }
    .why-camp-item {
      position: relative;
      background-size: cover;
      background-position: center;
      border-radius: 12px;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 120px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.10);
    }
    .why-camp-item span {
      color: #fff;
      font-size: 1.5rem;
      font-weight: 600;
      text-shadow: 0 2px 8px rgba(0,0,0,0.5);
      z-index: 2;
      text-align: center;
      padding: 0 1rem;
    }
    .why-camp-item::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(0deg, rgba(0,0,0,0.45) 60%, rgba(0,0,0,0.15) 100%);
      z-index: 1;
    }
    .why-camp-item-large {
      grid-row: 1 / 3;
      grid-column: 1 / 2;
    }
    .why-camp-item-tall {
      grid-row: 1 / 3;
      grid-column: 2 / 3;
    }
    @media (max-width: 900px) {
      .why-camp-grid {
        grid-template-columns: 1fr 1fr;
        grid-template-rows: repeat(3, 180px);
      }
      .why-camp-item-large, .why-camp-item-tall {
        grid-row: auto;
        grid-column: auto;
      }
    }
    @media (max-width: 600px) {
      .why-camp-grid {
        grid-template-columns: 1fr;
        grid-template-rows: repeat(6, 160px);
      }
    }

    .features-section {
      background: rgba(182, 164, 122, 0);
      padding: 0 0 2.5rem 0;
      margin-top: 0;
      display: flex;
      justify-content: center;
    }
    .features-grid {
      display: flex;
      gap: 2.5rem;
      max-width: 1200px;
      width: 100%;
      justify-content: center;
      flex-wrap: wrap;
    }
    .feature-card {
      background: #b6a47a;
      border-radius: 32px;
      box-shadow: 0 4px 24px rgba(0,0,0,0.08);
      width: 270px;
      height: 270px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      transition: transform 0.2s, box-shadow 0.2s;
      cursor: pointer;
      transform: perspective(600px) rotateX(90deg);
      opacity: 0;
      transition: transform 0.7s cubic-bezier(0.23, 1, 0.32, 1), opacity 0.7s cubic-bezier(0.23, 1, 0.32, 1);
    }
    .feature-card:hover {
      transform: translateY(-8px) scale(1.03);
      box-shadow: 0 8px 32px rgba(0,0,0,0.13);
    }
    .feature-card.flipped {
      transform: perspective(600px) rotateX(0deg);
      opacity: 1;
    }
    .feature-icon {
      margin-bottom: 1.5rem;
    }
    .feature-label {
      color: #fff;
      font-size: 1.35rem;
      font-weight: 700;
      letter-spacing: 1px;
      text-align: center;
      margin-top: 0.5rem;
    }
    @media (max-width: 1100px) {
      .features-grid {
        gap: 1.2rem;
      }
      .feature-card {
        width: 200px;
        height: 200px;
      }
    }
    @media (max-width: 700px) {
      .features-grid {
        flex-direction: column;
        align-items: center;
      }
      .feature-card {
        width: 90vw;
        max-width: 350px;
        height: 140px;
        flex-direction: row;
        gap: 1.2rem;
        padding: 0 1.2rem;
      }
      .feature-icon {
        margin-bottom: 0;
        margin-right: 1.2rem;
      }
    }

    .video-gallery-section {
      background: #fff;
      padding: 3.5rem 0;
      text-align: center;
    }
    .video-gallery-title {
      color: #b6a47a;
      font-size: 2.2rem;
      font-weight: 700;
      margin-bottom: 2.5rem;
      letter-spacing: 1px;
    }
    .video-gallery-row {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 2.5rem;
      width: 100%;
      max-width: 1600px;
      margin: 0 auto;
    }
    .main-video-wrapper,
    .side-slider-bg {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 600px;
      min-width: 800px;
      background: #b6a47a;
      border-radius: 1.5rem;
      box-shadow: 0 4px 24px rgba(0,0,0,0.12);
      padding: 0.5rem;
    }
    .main-video {
      width: 100%;
      max-width: 800px;
      height: 450px;
      border-radius: 1.2rem;
      background: #000;
      object-fit: cover;
    }
    .side-slider-bg {
      background: #b6a47a;
      min-width: 400px;
      max-width: 400px;
      height: 600px;
      position: relative;
      flex-direction: row;
      padding: 0;
    }
    .side-slider-track {
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
    }
    .side-video {
      display: none;
      width: 95%;
      height: 95%;
      border-radius: 1.2rem;
      background: #000;
      object-fit: cover;
    }
    .side-video.active {
      display: block;
    }
    .side-arrow {
      background: #fff;
      border: none;
      color: #222;
      font-size: 2rem;
      border-radius: 50%;
      width: 2.5rem;
      height: 2.5rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.10);
      cursor: pointer;
      z-index: 2;
      opacity: 0.85;
      margin: 0 0.5rem;
      transition: background 0.2s, opacity 0.2s;
      align-self: center;
    }
    .side-arrow.left { order: 0; }
    .side-slider-track { order: 1; }
    .side-arrow.right { order: 2; }
    .side-arrow:hover {
      background: #c7b491;
      color: #fff;
      opacity: 1;
    }
    @media (max-width: 900px) {
      .video-gallery-row {
        flex-direction: column;
        align-items: center;
        gap: 1.5rem;
      }
      .side-slider-bg {
        min-width: 90vw;
        max-width: 95vw;
        flex-direction: row;
        justify-content: center;
      }
      .main-video {
        max-width: 95vw;
      }
    }

    .activity-section {
      padding: 4rem 0 3rem 0;
      background:rgba(81, 80, 80, 0.43);
      text-align: center;
    }
    .activity-title {
      font-size: 2.5rem;
      font-weight: 700;
      color:rgb(255, 255, 255);
      margin-bottom: 2.5rem;
      letter-spacing: 1px;
    }
    .activity-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 2rem;
      max-width: 1300px;
      margin: 0 auto;
    }
    .activity-card {
      position: relative;
      background-size: cover;
      background-position: center;
      border-radius: 1.5rem;
      min-height: 320px;
      box-shadow: 0 4px 24px rgba(0,0,0,0.10);
      overflow: hidden;
      display: flex;
      align-items: flex-end;
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .activity-card:hover {
      transform: translateY(-6px) scale(1.03);
      box-shadow: 0 8px 32px rgba(0,0,0,0.13);
    }
    .activity-overlay {
      width: 100%;
      padding: 2rem 1.5rem 1.5rem 1.5rem;
      background: linear-gradient(0deg, rgba(0,0,0,0.65) 80%, rgba(0,0,0,0.15) 100%);
      color: #fff;
      border-radius: 0 0 1.5rem 1.5rem;
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      justify-content: flex-end;
    }
    .activity-type {
      font-size: 1rem;
      font-weight: 500;
      opacity: 0.85;
      margin-bottom: 0.5rem;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    .activity-name {
      font-size: 1.5rem;
      font-weight: 700;
      margin-bottom: 1.2rem;
      line-height: 1.2;
    }
    .activity-btn {
      display: inline-block;
      padding: 0.5rem 1.2rem;
      border: 2px solid #fff;
      color: #fff;
      background: transparent;
      border-radius: 0.5rem;
      font-size: 1rem;
      font-weight: 600;
      text-decoration: none;
      transition: background 0.2s, color 0.2s;
    }
    .activity-btn:hover {
      background: #fff;
      color: #b6a47a;
    }
  </style>
</head>
<body>
  <header class="header">
    <nav class="navbar">
      <div class="logo">TasikBiruCamps</div>
      <ul class="nav-links">
        <li><a href="#">Home</a></li>
        <li><a href="campsites.php">Campsites</a></li>
        <li><a href="login.php">Login</a></li>
        <li><a href="register.php">Register</a></li>
      </ul>
    </nav>
    <div class="hero">
      <h1 class="font_0 wixui-rich-text__text fade-up" id="fadeup-title" style="text-align:center; font-size:100px; font-family:'Times New Roman', serif;">
        Find Your Perfect Campsite
      </h1>
      <p class="fade-up" id="fadeup-desc" style="font-family:'Times New Roman', serif; text-align:center;">Book nature's best spots with ease and comfort.</p>
      <a href="about.php" class="btn fade-up" id="fadeup-about" style="font-family:'Times New Roman', serif;">About Us</a>
    </div>
  </header>

  <!-- Features Section -->
  <section class="features-section">
    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon">
          <!-- Trekking SVG -->
          <svg width="56" height="56" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 56 56"><path d="M14 44L34 14"/><path d="M42 44L22 14"/><circle cx="14" cy="44" r="2.5" fill="#fff"/><circle cx="42" cy="44" r="2.5" fill="#fff"/></svg>
        </div>
        <div class="feature-label">TREKKING</div>
      </div>
      <div class="feature-card">
        <div class="feature-icon">
          <!-- Camping SVG -->
          <svg width="56" height="56" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 56 56"><path d="M28 14L14 42H42L28 14Z"/><circle cx="38" cy="20" r="2" fill="#fff"/><path d="M24 32h8"/></svg>
        </div>
        <div class="feature-label">CAMPING</div>
      </div>
      <div class="feature-card">
        <div class="feature-icon">
          <!-- Beach Tent SVG -->
          <svg width="56" height="56" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 56 56"><path d="M28 14L14 42H42L28 14Z"/><circle cx="28" cy="32" r="2" fill="#fff"/></svg>
        </div>
        <div class="feature-label">BEACH TENTS</div>
      </div>
      <div class="feature-card">
        <div class="feature-icon">
          <!-- News & Events SVG -->
          <svg width="56" height="56" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 56 56"><path d="M28 38V28"/><path d="M28 18a6 6 0 0 1 6 6c0 2.5-2 4.5-6 8-4-3.5-6-5.5-6-8a6 6 0 0 1 6-6Z"/><circle cx="28" cy="44" r="2.5" fill="#fff"/></svg>
        </div>
        <div class="feature-label">NEWS & EVENTS</div>
      </div>
    </div>
  </section>

  <!-- Why Camp Section -->
  <section class="why-camp-section">
    <div class="why-camp-card">
      <h2 class="why-camp-title">Why Camp?</h2>
      <div class="why-camp-grid">
        <div class="why-camp-item why-camp-item-large" style="background-image:url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=600&q=80');">
          <span>Develop Life Skills</span>
        </div>
        <div class="why-camp-item" style="background-image:url('https://images.unsplash.com/photo-1464983953574-0892a716854b?auto=format&fit=crop&w=400&q=80');">
          <span>Improve Health</span>
        </div>
        <div class="why-camp-item" style="background-image:url('https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=400&q=80');">
          <span>Explore Nature</span>
        </div>
        <div class="why-camp-item why-camp-item-tall" style="background-image:url('https://images.unsplash.com/photo-1502082553048-f009c37129b9?auto=format&fit=crop&w=400&q=80');">
          <span>Strengthen Relationships</span>
        </div>
        <div class="why-camp-item" style="background-image:url('https://images.unsplash.com/photo-1465101046530-73398c7f28ca?auto=format&fit=crop&w=400&q=80');">
          <span>Tradition</span>
        </div>
        <div class="why-camp-item" style="background-image:url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=400&q=80');">
          <span>Digital Detox</span>
        </div>
      </div>
    </div>
  </section>

  <!-- Video Section -->
  <section class="video-gallery-section">
    <h2 class="video-gallery-title">Explore Tasik Biru</h2>
    <div class="video-gallery-row">
      <!-- Main video on the left -->
      <div class="main-video-wrapper">
        <video class="main-video" controls poster="tasikbiru-thumb.jpg" id="mainVideo" autoplay muted>
          <source src="gambarhomepage/tasikbiruvideo1.mp4" type="video/mp4">
        </video>
      </div>
      <!-- Side video with left/right arrows -->
      <div class="side-slider-bg">
        <button class="side-arrow left" onclick="slideSideVideo(-1)">&#8592;</button>
        <div class="side-slider-track">
          <video class="side-video" controls poster="tasikbiru-thumb2.jpg">
            <source src="gambarhomepage/tasikbiruvideo2.mp4" type="video/mp4">
          </video>
          <video class="side-video" controls poster="tasikbiru-thumb3.jpg">
            <source src="gambarhomepage/tasikbiruvideo3.mp4" type="video/mp4">
          </video>
        </div>
        <button class="side-arrow right" onclick="slideSideVideo(1)">&#8594;</button>
      </div>
    </div>
  </section>

  <section class="activity-section">
    <h2 class="activity-title">Our Activities</h2>
    <div class="activity-grid">
      <div class="activity-card" style="background-image:url('gambarhomepage/camping1.jpg')">
        <div class="activity-overlay">
          <div class="activity-type">Outdoor</div>
          <div class="activity-name">Camping</div>
        </div>
      </div>
      <div class="activity-card" style="background-image:url('gambarhomepage/waterconfident2.jpg')">
        <div class="activity-overlay">
          <div class="activity-type">Water</div>
          <div class="activity-name">Water Confident</div>
        </div>
      </div>
      <div class="activity-card" style="background-image:url('gambarhomepage/kayaking3.jpeg')">
        <div class="activity-overlay">
          <div class="activity-type">Water</div>
          <div class="activity-name">Kayak</div>
        </div>
      </div>
      <div class="activity-card" style="background-image:url('gambarhomepage/ldk4.jpeg')">
        <div class="activity-overlay">
          <div class="activity-type">Leadership</div>
          <div class="activity-name">LDK</div>
        </div>
      </div>
      <div class="activity-card" style="background-image:url('gambarhomepage/latenightwalk5.jpg')">
        <div class="activity-overlay">
          <div class="activity-type">Adventure</div>
          <div class="activity-name">Night Walk</div>
        </div>
      </div>
      <div class="activity-card" style="background-image:url('gambarhomepage/potensidiri6.jpeg')">
        <div class="activity-overlay">
          <div class="activity-type">Self Development</div>
          <div class="activity-name">Potensi Diri</div>
        </div>
      </div>
      <div class="activity-card" style="background-image:url('gambarhomepage/jungle7.jpeg')">
        <div class="activity-overlay">
          <div class="activity-type">Adventure</div>
          <div class="activity-name">Jungle Tracking</div>
        </div>
      </div>
      <div class="activity-card" style="background-image:url('gambarhomepage/war8.jpeg')">
        <div class="activity-overlay">
          <div class="activity-type">Game</div>
          <div class="activity-name">War Game</div>
        </div>
      </div>
      <div class="activity-card" style="background-image:url('gambarhomepage/makanminum9.jpeg')">
        <div class="activity-overlay">
          <div class="activity-type">Food</div>
          <div class="activity-name">Makan Minum</div>
        </div>
      </div>
    </div>
  </section>
  <footer class="footer">
    <p>&copy; 2025 TasikBiruCamps. All rights reserved.</p>
  </footer>
  <script>
    // Fade-up animation for hero elements on scroll (in and out)
    function fadeUpOnScroll() {
      const fadeEls = [
        document.getElementById('fadeup-title'),
        document.getElementById('fadeup-desc'),
        document.getElementById('fadeup-about')
      ];
      fadeEls.forEach((el, i) => {
        if (!el) return;
        const rect = el.getBoundingClientRect();
        if (rect.top < window.innerHeight * 0.92 && rect.bottom > 0) {
          setTimeout(() => el.classList.add('visible'), i * 200);
        } else {
          el.classList.remove('visible');
        }
      });
    }
    window.addEventListener('scroll', fadeUpOnScroll);
    window.addEventListener('DOMContentLoaded', fadeUpOnScroll);

    // Flip animation for feature cards on scroll (in and out)
    function flipCardsOnScroll() {
      const cards = document.querySelectorAll('.feature-card');
      const trigger = window.innerHeight * 0.92;
      cards.forEach((card, i) => {
        const rect = card.getBoundingClientRect();
        if (rect.top < trigger && rect.bottom > 0) {
          setTimeout(() => card.classList.add('flipped'), i * 120);
        } else {
          card.classList.remove('flipped');
        }
      });
    }
    window.addEventListener('scroll', flipCardsOnScroll);
    window.addEventListener('DOMContentLoaded', flipCardsOnScroll);

    let currentSideVideo = 0;
    const sideVideos = document.querySelectorAll('.side-video');
    function showSideVideo(idx, autoPlay = false) {
      sideVideos.forEach((v, i) => {
        v.classList.toggle('active', i === idx);
        if (i !== idx) v.pause();
      });
      if (autoPlay && sideVideos[idx]) {
        sideVideos[idx].play();
      }
      currentSideVideo = idx;
    }
    function slideSideVideo(dir) {
      let next = currentSideVideo + dir;
      if (next < 0) next = sideVideos.length - 1;
      if (next >= sideVideos.length) next = 0;
      showSideVideo(next, true);
    }
    document.addEventListener('DOMContentLoaded', () => {
      showSideVideo(0);
      // Auto-play main video on load
      const mainVideo = document.getElementById('mainVideo');
      if (mainVideo) {
        mainVideo.play();
      }
    });
  </script>
</body>
</html>
