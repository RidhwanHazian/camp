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
        background: #fff;
        padding: 0 2%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        position: sticky;
        top: 0;
        z-index: 1000;
        height: 70px;
    }

    .nav-logo {
        font-size: 24px;
        font-weight: bold;
        color: #333;
        text-decoration: none;
    }

    .nav-menu-links {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .nav-welcome-msg {
        color: #6b4423;
        font-size: 14px;
        background: #f8f4e9;
        padding: 8px 15px;
        border-radius: 20px;
        margin-right: 1rem;
    }

    .nav-menu-links > a, .dropdown .dropbtn {
        text-decoration: none;
        color: white;
        background-color: #8c6d52; /* Darker aesthetic brown */
        font-size: 14px;
        padding: 10px 20px;
        border-radius: 20px;
        transition: all 0.2s ease-in-out;
        border: none;
        cursor: pointer;
        font-family: 'Arial', sans-serif;
        box-shadow: 0 2px 4px rgba(0,0,0,0.15);
        transform: translateY(0);
    }

    .nav-menu-links > a:hover, .dropdown .dropbtn:hover {
        background-color: #735b43; /* Darker on hover */
        box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        transform: translateY(-2px);
    }

    .dropdown {
        position: relative;
        display: inline-block;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        background-color: transparent; /* No background */
        min-width: 160px;
        box-shadow: none; /* No shadow */
        z-index: 1;
        padding-top: 10px; /* Space from main button */
        left: 50%;
        transform: translateX(-50%);
    }

    .dropdown-content a {
        color: #333;
        padding: 10px 20px;
        text-decoration: none;
        display: block;
        text-align: center;
        background-color: #d3bfa8; /* Lighter aesthetic brown */
        border-radius: 20px;
        margin-bottom: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.15);
        transition: all 0.2s ease-in-out;
        transform: translateY(0);
    }

    .dropdown-content a:hover {
        background-color: #c9bca7; /* Darker on hover */
        box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        transform: translateY(-2px);
    }

    .dropdown-content.show {
        display: block;
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
        
        <div class="dropdown">
            <button class="dropbtn" onclick="toggleDropdown(this)">Reservation</button>
            <div class="dropdown-content">
                <a href="makeBooking.php">Book</a>
                <a href="my_bookings.php">MyBookings</a>
            </div>
        </div>

        <a href="notifications.php">Notification</a>

        <div class="dropdown">
            <button class="dropbtn" onclick="toggleDropdown(this)">Rate us</button>
            <div class="dropdown-content">
                <a href="feedback.php">give Feedback</a>
                <a href="review.php">view reviews</a>
            </div>
        </div>
        
        <a href="#" onclick="confirmLogout(); return false;">Log Out</a>
    </div>
</nav>

<!-- Navigation Menu JavaScript -->
<script>
    function toggleNavMenu() {
        const navLinks = document.getElementById('navMenuLinks');
        navLinks.classList.toggle('show');
    }

    function toggleDropdown(button) {
        // Close other dropdowns
        closeAllDropdowns(button);
        // Toggle current dropdown
        const dropdownContent = button.nextElementSibling;
        dropdownContent.classList.toggle('show');
    }

    function confirmLogout() {
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = 'logout.php';
        }
    }
    
    function closeAllDropdowns(exceptButton) {
        const dropdowns = document.querySelectorAll('.dropdown-content');
        const dropbtns = document.querySelectorAll('.dropbtn');

        for (let i = 0; i < dropbtns.length; i++) {
            if (dropbtns[i] !== exceptButton) {
                dropbtns[i].nextElementSibling.classList.remove('show');
            }
        }
    }

    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        const navLinks = document.getElementById('navMenuLinks');
        const menuBtn = document.querySelector('.nav-menu-btn');
        if (!navLinks.contains(event.target) && !menuBtn.contains(event.target)) {
            navLinks.classList.remove('show');
        }

        // Close dropdowns when clicking outside
        if (!event.target.matches('.dropbtn')) {
            closeAllDropdowns(null);
        }
    });
</script> 
