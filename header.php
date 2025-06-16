<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get the current page name
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Navigation Menu Styles -->
<style>
    .nav-menu {
        background: white;
        padding: 0 2%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        position: sticky;
        top: 0;
        z-index: 1000;
        height: 60px;
    }

    .nav-logo {
        font-size: 20px;
        font-weight: bold;
        color: #333;
        text-decoration: none;
        white-space: nowrap;
    }

    .nav-menu-links {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .nav-welcome-msg {
        color: #6b4423;
        font-size: 14px;
        background: #f8f4e9;
        padding: 6px 12px;
        border-radius: 15px;
        white-space: nowrap;
    }

    .nav-menu-links a {
        text-decoration: none;
        color: #654321;
        font-size: 14px;
        padding: 6px 12px;
        border-radius: 15px;
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .nav-menu-links a:hover {
        background: rgba(139, 94, 52, 0.1);
        color: #8b5e34;
    }

    .nav-menu-links a.active {
        background: rgba(255, 0, 0, 0.1);
        color: #ff0000;
        font-weight: 500;
    }

    .nav-menu-btn {
        display: none;
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        padding: 5px;
        color: #333;
    }

    @media (max-width: 1024px) {
        .nav-menu-btn {
            display: block;
        }

        .nav-menu-links {
            display: none;
            position: absolute;
            top: 60px;
            left: 0;
            right: 0;
            background: white;
            flex-direction: column;
            padding: 1rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            gap: 0.5rem;
        }

        .nav-menu-links.show {
            display: flex;
        }

        .nav-menu-links a, 
        .nav-welcome-msg {
            width: 100%;
            text-align: center;
            padding: 10px;
        }

        .nav-welcome-msg {
            order: -1;
        }
    }
</style>

<!-- Navigation Menu HTML -->
<nav class="nav-menu">
    <a href="customer_dashboard.php" class="nav-logo">TasikBiruCamps</a>
    <button class="nav-menu-btn" onclick="toggleNavMenu()">☰</button>
    <div class="nav-menu-links" id="navMenuLinks">
        <span class="nav-welcome-msg">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
        <a href="view_packages.php" <?php echo $current_page == 'view_packages.php' ? 'class="active"' : ''; ?>>Packages</a>
        <a href="makeBooking.php" <?php echo $current_page == 'makeBooking.php' ? 'class="active"' : ''; ?>>Booking</a>
        <a href="my_bookings.php" <?php echo $current_page == 'my_bookings.php' ? 'class="active"' : ''; ?>>My Bookings</a>
        <a href="payment.php" <?php echo $current_page == 'payment.php' ? 'class="active"' : ''; ?>>Payment</a>
        <a href="feedback.php" <?php echo $current_page == 'feedback.php' ? 'class="active"' : ''; ?>>Feedback</a>
        <a href="notifications.php" <?php echo $current_page == 'notifications.php' ? 'class="active"' : ''; ?>>Notifications</a>
        <a href="review.php" <?php echo $current_page == 'review.php' ? 'class="active"' : ''; ?>>Review</a>
        <a href="#" onclick="confirmLogout(); return false;">Logout</a>
    </div>
</nav>

<!-- Navigation Menu JavaScript -->
<script>
    function toggleNavMenu() {
        const navLinks = document.getElementById('navMenuLinks');
        navLinks.classList.toggle('show');
    }

    function confirmLogout() {
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = 'logout.php';
        }
    }

    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        const navLinks = document.getElementById('navMenuLinks');
        const menuBtn = document.querySelector('.nav-menu-btn');
        if (!navLinks.contains(event.target) && !menuBtn.contains(event.target)) {
            navLinks.classList.remove('show');
        }
    });
</script> 