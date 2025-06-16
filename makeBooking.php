<?php
include 'session_check.php';
checkCustomerSession();

require_once 'db_connection.php'; // Include your database connection

// Fetch packages to display in the dropdown
$packages = [];
try {
    $stmt = $conn->prepare("SELECT package_id, package_name, price FROM packages");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $packages[] = $row;
    }
} catch (Exception $e) {
    error_log("Error fetching packages: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Make Booking - TasikBiruCamps</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Arial', sans-serif;
    }

    html {
      scroll-behavior: smooth;
    }

    .hero {
      height: 300px;
      background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('https://images.unsplash.com/photo-1682687220742-aba13b6e50ba?q=80&w=2070&auto=format&fit=crop');
      background-size: cover;
      background-position: center;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      text-align: center;
    }

    .hero h1 {
      font-size: 3.5rem;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
    }

    .booking-container {
      max-width: 1200px;
      margin: 2rem auto;
      padding: 2rem;
      background: white;
      border-radius: 10px;
      box-shadow: 0 0 20px rgba(0,0,0,0.1);
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 2rem;
    }

    .booking-form {
      padding: 1rem;
    }

    .booking-form h2 {
      margin-bottom: 1.5rem;
      color: #333;
    }

    .form-group {
      margin-bottom: 1.5rem;
    }

    .form-group label {
      display: block;
      margin-bottom: 0.5rem;
      color: #333;
      font-weight: 500;
    }

    .form-group input,
    .form-group select {
      width: 100%;
      padding: 0.8rem;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 1rem;
    }

    .number-inputs {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }

    .calendar-section {
      padding: 1rem;
    }

    .calendar-section h3 {
      margin-bottom: 1rem;
      color: #333;
    }

    .date-inputs {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
      margin-bottom: 1.5rem;
    }

    .calendar {
      border: 1px solid #ddd;
      border-radius: 5px;
      padding: 1rem;
    }

    .calendar-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
    }

    .calendar-grid {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      gap: 0.5rem;
      text-align: center;
    }

    .calendar-day {
      padding: 0.5rem;
      border: 1px solid #eee;
      border-radius: 5px;
    }

    .calendar-day.available {
      background-color: #e8f5e9;
      cursor: pointer;
    }

    .calendar-day.unavailable {
      background-color: #ffebee;
      color: #999;
    }

    .submit-btn {
      background-color: #ff0000;
      color: white;
      padding: 1rem 2rem;
      border: none;
      border-radius: 5px;
      font-size: 1rem;
      cursor: pointer;
      width: 100%;
      margin-top: 1rem;
    }

    .submit-btn:hover {
      background-color: #cc0000;
    }

    @media (max-width: 768px) {
      .booking-container {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>

<?php include 'header.php'; ?>

<section class="hero" id="booking">
  <h1>Make Booking</h1>
</section>

<div class="booking-container">
  <div class="booking-form">
    <h2>Make a Booking</h2>
    <form id="bookingForm" action="process_booking.php" method="POST" onsubmit="submitBookingForm(event)">
      <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($_SESSION['customer_id']); ?>">
      <div class="form-group">
        <label for="fullName">Full Name</label>
        <input type="text" id="fullName" name="fullName" required>
      </div>
      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" required>
      </div>
      <div class="form-group">
        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone" required>
      </div>
      <div class="number-inputs">
        <div class="form-group">
          <label for="adults">Number of Adults</label>
          <input type="number" id="adults" name="adults" min="1" value="1" required>
        </div>
        <div class="form-group">
          <label for="children">Number of Children</label>
          <input type="number" id="children" name="children" min="0" value="0">
        </div>
      </div>
      <div class="form-group">
        <label for="package">Choose a Package</label>
        <select id="package" name="package" required>
          <option value="">-- Select a Package --</option>
          <option value="1">Package A-3 Days 2 Nights</option>
          <option value="2">Package B-3 Days 2 Nights</option>
          <option value="3">Package C-3 Days 2 Nights</option>
          <option value="4">Package D-2 Days 1 Nights</option>
          <option value="5">Package E-1 Day</option>
        </select>
      </div>
      <div class="date-inputs">
        <div class="form-group">
          <label for="arriveDate">ARRIVE</label>
          <input type="date" id="arriveDate" name="arriveDate" required>
        </div>
        <div class="form-group">
          <label for="departDate">DEPARTURE</label>
          <input type="date" id="departDate" name="departDate" required>
        </div>
      </div>
      <button type="submit" class="submit-btn">Submit Booking</button>
    </form>
  </div>

  <div class="calendar-section">
    <h3>YOUR STAY DATES</h3>
    <div class="calendar">
      <div class="calendar-header">
        <button id="prevMonth">&lt;</button>
        <h4 id="monthYear">MONTH YEAR</h4>
        <button id="nextMonth">&gt;</button>
      </div>
      <div class="calendar-grid" id="calendarGrid">
        <div>SU</div><div>MO</div><div>TU</div><div>WE</div><div>TH</div><div>FR</div><div>SA</div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendarGrid = document.getElementById('calendarGrid');
    const monthYear = document.getElementById('monthYear');
    const prevMonth = document.getElementById('prevMonth');
    const nextMonth = document.getElementById('nextMonth');
    const arriveDate = document.getElementById('arriveDate');
    const departDate = document.getElementById('departDate');

    let currentDate = new Date();
    let bookedDates = [];

    // Fetch booked dates from the server
    fetch('get_booked_dates.php')
      .then(response => response.json())
      .then(dates => {
        bookedDates = dates;
        renderCalendar(currentDate);
      })
      .catch(error => console.error('Error fetching booked dates:', error));

    // Set minimum date for arrival and departure
    const today = new Date().toISOString().split('T')[0];
    arriveDate.min = today;
    departDate.min = today;

    // Ensure departure date is after arrival date
    arriveDate.addEventListener('change', function() {
      departDate.min = this.value;
      if (departDate.value && departDate.value < this.value) {
        departDate.value = this.value;
      }
    });

    function isDateBooked(dateString) {
      return bookedDates.includes(dateString);
    }

    function renderCalendar(date) {
      // Clear all calendar days except the day names
      while (calendarGrid.children.length > 7) {
        calendarGrid.removeChild(calendarGrid.lastChild);
      }

      const year = date.getFullYear();
      const month = date.getMonth();
      const firstDay = new Date(year, month, 1);
      const lastDay = new Date(year, month + 1, 0);
      const startDay = firstDay.getDay();
      const totalDays = lastDay.getDate();

      monthYear.textContent = date.toLocaleString('default', { month: 'long', year: 'numeric' }).toUpperCase();

      // Add empty cells for days before the first day of the month
      for (let i = 0; i < startDay; i++) {
        calendarGrid.appendChild(document.createElement('div'));
      }

      // Add cells for each day of the month
      for (let day = 1; day <= totalDays; day++) {
        const cell = document.createElement('div');
        const currentDateString = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const isUnavailable = isDateBooked(currentDateString) || new Date(currentDateString) < new Date(today);
        
        cell.className = 'calendar-day ' + (isUnavailable ? 'unavailable' : 'available');
        cell.textContent = day;
        
        if (!isUnavailable) {
          cell.addEventListener('click', () => {
            const selectedDate = currentDateString;
            arriveDate.value = selectedDate;
            // Trigger the change event to update departure date minimum
            arriveDate.dispatchEvent(new Event('change'));
          });
        }
        
        calendarGrid.appendChild(cell);
      }
    }

    prevMonth.addEventListener('click', () => {
      currentDate.setMonth(currentDate.getMonth() - 1);
      renderCalendar(currentDate);
    });

    nextMonth.addEventListener('click', () => {
      currentDate.setMonth(currentDate.getMonth() + 1);
      renderCalendar(currentDate);
    });

    // Form submission
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
      const selectedArriveDate = new Date(arriveDate.value);
      const selectedDepartDate = new Date(departDate.value);

      // Check if dates are valid
      if (selectedDepartDate < selectedArriveDate) {
        e.preventDefault();
        alert('Departure date must be after arrival date');
        return;
      }

      // Check if any selected dates are booked
      const dateRange = [];
      let currentDate = new Date(selectedArriveDate);
      while (currentDate <= selectedDepartDate) {
        dateRange.push(currentDate.toISOString().split('T')[0]);
        currentDate.setDate(currentDate.getDate() + 1);
      }

      const hasBookedDate = dateRange.some(date => isDateBooked(date));
      if (hasBookedDate) {
        e.preventDefault();
        alert('Some of the selected dates are already booked. Please choose different dates.');
        return;
      }
    });

    // Initial render
    renderCalendar(currentDate);
});

function submitBookingForm(event) {
    event.preventDefault();
    
    const form = document.getElementById('bookingForm');
    const formData = new FormData(form);
    
    fetch('process_booking.php', {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Booking submitted successfully!');
            window.location.href = 'my_bookings.php';
        } else {
            alert(data.error || 'Failed to submit booking. Please try again.');
        }
    })
    .catch(error => {
        alert('An error occurred while submitting the booking. Please try again.');
    });
}
</script>

</body>
</html>