<?php
session_start();
include 'db_connection.php';
include 'session_check.php';
checkCustomerSession();

// Fetch packages and their prices to display
$packages_sql = "
    SELECT 
        p.package_id, p.package_name, p.description, p.duration, p.photo, p.activity,
        pp.adult_price, pp.child_price 
    FROM packages p
    JOIN package_prices pp ON p.package_id = pp.package_id
";
$packages_result = mysqli_query($conn, $packages_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Make Booking - TasikBiruCamps</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
        body {
            font-family: 'Arial', sans-serif;
            background-image: url('backgroundcamp.jpg');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
      margin: 0;
            color: #333;
        }
        .booking-header-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 20px;
            margin-bottom: 20px;
        }
        .booking-body-content {
            background-color: rgba(255, 255, 255, 0.9); /* White with 90% opacity */
            padding: 20px;
            border-radius: 20px;
        }
        .booking-page-container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
        }
        .selection-bar {
            display: flex;
            justify-content: center;
            gap: 20px;
            background-color: #8c6d52;
            padding: 20px;
            border-radius: 20px;
            margin-bottom: 30px;
        }
        .select-box {
            background-color: white;
            padding: 15px 25px;
            border-radius: 15px;
      display: flex;
      align-items: center;
            gap: 15px;
            cursor: pointer;
            min-width: 250px;
        }
        .select-box i { font-size: 1.2em; color: #8c6d52; }
        .select-box div { font-size: 0.9em; }
        .select-box span { display: block; color: #888; font-size: 0.8em; }
        .main-content { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; align-items: start; }
        .packages-container {
            background-color: transparent;
            padding: 0;
        }
        .package-card {
            background-color: #e0e0e0;
            padding: 20px;
            border-radius: 20px;
            margin-bottom: 20px;
            border: 3px solid transparent;
            transition: border 0.3s;
            display: flex;
            flex-direction: column;
        }
        .package-card.selected {
            border: 3px solid #8c6d52;
        }
        .package-title-main {
            font-size: 2em;
            margin-bottom: 15px;
    }
        .package-content-wrapper {
            display: flex;
            gap: 20px;
            flex-grow: 1;
        }
        .package-image {
            width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 15px;
            flex-shrink: 0;
    }
        .package-details {
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        .package-details p {
            margin: 0 0 10px;
    }
        .package-details strong {
      display: block;
            margin-bottom: 5px;
        }
        .package-details ul {
            margin: 0;
            padding-left: 20px;
            list-style: disc;
    }
        .package-pricing {
            margin-top: auto;
            font-weight: bold;
        }
        .select-btn {
            background-color: #8c6d52;
            color: white;
            padding: 10px 30px;
            border-radius: 15px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
            align-self: flex-end;
            margin-top: 15px;
    }
        .select-btn:hover { background-color: #735b43; }
        .summary-container {
            background-color: #8c6d52; /* Rich brown from your image */
            color: white;
            padding: 30px;
            border-radius: 20px;
            position: sticky;
            top: 20px; /* Adjusted sticky position */
        }
        .summary-container hr {
            border: none;
            border-top: 1px solid rgba(255, 255, 255, 0.3);
            margin: 20px 0;
        }
        .summary-total h3 {
            font-size: 2em;
            font-weight: bold;
            margin: 0 0 15px 0;
            text-align: left;
        }
        .summary-item, .summary-package-sub-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.95em;
        }
        .summary-package-item strong {
            font-size: 1.3em;
            margin-bottom: 10px;
            display: block;
        }
        .summary-package-total {
            display: flex;
            justify-content: space-between;
            font-size: 1.2em;
            font-weight: bold;
            margin-top: 15px;
        }
        .book-button { /* Renamed for clarity */
            width: 100%;
            background-color: #dcdcdc; /* Light grey for disabled state */
            color: #888;
            padding: 15px;
            border: none;
            border-radius: 15px;
            font-size: 1.2em;
            cursor: not-allowed;
            margin-top: 20px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .book-button.enabled {
            background-color: #ffffff; /* Clean white enabled state */
            color: #8c6d52; /* Brown text */
            cursor: pointer;
        }
        .book-button.enabled:hover {
            background-color: #f0f0f0;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
    }
        .modal-content {
            background-color: #fefefe;
            padding: 20px;
            border: 1px solid #888;
            border-radius: 15px;
            width: 80%;
            max-width: 500px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }
        .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        .modal-header h2 { margin: 0; }
        .close-btn {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
      cursor: pointer;
    }
        .close-btn:hover, .close-btn:focus { color: black; }
        /* Calendar styles from old code, adapted for modal */
        .calendar { padding: 1rem; }
        .calendar-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.5rem; text-align: center; }
        .calendar-day { padding: 0.5rem; border-radius: 5px; cursor: pointer; }
        .day-name { font-weight: bold; }
        .calendar-day.unavailable { background-color: #ffebee; color: #ccc; cursor: not-allowed; text-decoration: line-through; }
        .calendar-day.selected { background-color: #8c6d52; color: white; }
        .calendar-day:not(.unavailable):hover { background-color: #e0e0e0; }

        /* Guest Modal Styles */
        .guest-input {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
        }
        .guest-input button {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 1px solid #ddd;
            background-color: white;
            font-size: 20px;
      cursor: pointer;
        }
        .guest-input span {
            font-size: 18px;
    }
  </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="booking-page-container">
    <img src="summerCamp.jpg" alt="Beautiful campsite" class="booking-header-image">

    <form id="bookingForm" action="booking_complete.php" method="POST">
        <!-- Hidden fields for submission -->
      <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($_SESSION['customer_id']); ?>">
        <input type="hidden" name="package" id="package" required>
        <input type="hidden" name="arriveDate" id="arriveDate" value="">
        <input type="hidden" name="departDate" id="departDate" value="">
        <input type="hidden" name="adults" id="adults" value="">
        <input type="hidden" name="children" id="children" value="">
        <!-- These fields will need to be filled, perhaps with a modal or from user session -->
        <input type="hidden" name="fullName" value="Siti Aisyah">
        <input type="hidden" name="email" value="siti@example.com">
        <input type="hidden" name="phone" value="0123456789">
        
        <div class="selection-bar">
            <div class="select-box" id="date-selector">
                <i class="fas fa-calendar-alt"></i>
                <div>
                    <span>Select dates</span>
                    <span id="dates-display"></span>
                </div>
            </div>
            <div class="select-box" id="guest-selector">
                <i class="fas fa-user-friends"></i>
                <div>
                    <span>Select Adult and Kids</span>
                    <span id="guests-display"></span>
                </div>
            </div>
        </div>

        <div class="booking-body-content">
            <div class="main-content">
                <div class="packages-container" id="packages-container">
                    <?php if ($packages_result && mysqli_num_rows($packages_result) > 0): ?>
                        <?php while ($package = mysqli_fetch_assoc($packages_result)): ?>
                            <div class="package-card"
                                 data-package-id="<?php echo htmlspecialchars($package['package_id']); ?>"
                                 data-package-name="<?php echo htmlspecialchars($package['package_name']); ?>"
                                 data-adult-price="<?php echo htmlspecialchars($package['adult_price']); ?>"
                                 data-child-price="<?php echo htmlspecialchars($package['child_price']); ?>"
                                 data-duration="<?php echo htmlspecialchars($package['duration']); ?>">
                                <h2 class="package-title-main"><?php echo htmlspecialchars($package['package_name']); ?></h2>
                                <div class="package-content-wrapper">
                                     <?php if (!empty($package['photo']) && file_exists('Assets/' . $package['photo'])): ?>
                                        <img src="Assets/<?php echo htmlspecialchars($package['photo']); ?>" alt="<?php echo htmlspecialchars($package['package_name']); ?>" class="package-image">
                                    <?php else: ?>
                                        <img src="default_package.png" alt="No image" class="package-image">
                                    <?php endif; ?>
                                     <div class="package-details">
                                         <p><?php echo htmlspecialchars($package['description']); ?></p>
                                         <div>
                                             <strong>Package Details</strong>
                                             <p><?php echo htmlspecialchars($package['duration']); ?></p>
                                         </div>
                                         <div>
                                             <strong>Activity</strong>
                                             <ul>
                                                 <?php 
                                                 $activities = explode(',', $package['activity']);
                                                 foreach ($activities as $activity) {
                                                     echo '<li>' . htmlspecialchars(trim($activity)) . '</li>';
                                                 }
                                                 ?>
                                             </ul>
                                         </div>
                                         <div class="package-pricing">
                                             Price : Adult-RM<?php echo htmlspecialchars(number_format($package['adult_price'], 2)); ?> Kids-RM<?php echo htmlspecialchars(number_format($package['child_price'], 2)); ?>
                                         </div>
        </div>
      </div>
                                <button type="button" class="select-btn">Select</button>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                         <div class="package-card"><p>No packages available at the moment.</p></div>
                    <?php endif; ?>
                </div>

                <div class="summary-container">
                    <div id="summary-total-price" class="summary-total">
                        <h3>Select selections to see price</h3>
                    </div>
                    <hr>
                    <div class="summary-item">
                        <span>Dates:</span>
                        <span id="summary-dates"></span>
                    </div>
                    <div class="summary-item">
                        <span>Guests:</span>
                        <span id="summary-guests"></span>
                    </div>
                    <hr>
                    <div id="summary-package-details">
                        <p>Please select a package</p>
                    </div>
                    <button type="submit" id="bookBtn" class="book-button" disabled>BOOK</button>
                    <div id="duration-warning" style="display:none; max-width: 500px; margin: 18px auto 0 auto; padding: 14px 18px; border-radius: 14px; background: #fff6e9; color: #c0392b; font-weight: 500; text-align: center; box-shadow: 0 2px 8px rgba(255, 183, 77, 0.13); font-size: 1.08em; position: relative;">
                      <span id="duration-warning-icon" style="font-size:1.5em; vertical-align:middle; margin-right:8px;">🐻</span>
                      <span id="duration-warning-text"></span>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Date Picker Modal -->
<div id="dateModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Select Dates</h2>
            <span class="close-btn">&times;</span>
        </div>
    <div class="calendar">
      <div class="calendar-header">
        <button id="prevMonth">&lt;</button>
        <h4 id="monthYear">MONTH YEAR</h4>
        <button id="nextMonth">&gt;</button>
      </div>
            <div class="calendar-grid">
                <div class="day-name">SU</div><div class="day-name">MO</div><div class="day-name">TU</div><div class="day-name">WE</div><div class="day-name">TH</div><div class="day-name">FR</div><div class="day-name">SA</div>
            </div>
      </div>
        <button id="confirmDates">Confirm Dates</button>
    </div>
</div>

<!-- Guest Picker Modal -->
<div id="guestModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Select Guests</h2>
            <span class="close-btn">&times;</span>
        </div>
        <div class="guest-input">
            <label>Adults</label>
            <div>
                <button type="button" id="adults-decrement">-</button>
                <span id="adults-count">1</span>
                <button type="button" id="adults-increment">+</button>
            </div>
        </div>
        <div class="guest-input">
            <label>Children</label>
            <div>
                <button type="button" id="children-decrement">-</button>
                <span id="children-count">0</span>
                <button type="button" id="children-increment">+</button>
            </div>
        </div>
        <button id="confirmGuests">Confirm Guests</button>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Elements and State ---
    const bookBtn = document.getElementById('bookBtn');
    const hiddenPackageId = document.getElementById('package');
    const arriveDateInput = document.getElementById('arriveDate');
    const departDateInput = document.getElementById('departDate');
    const adultsInput = document.getElementById('adults');
    const childrenInput = document.getElementById('children');

    // --- New Summary Elements ---
    const summaryTotalPriceEl = document.getElementById('summary-total-price');
    const summaryPackageDetailsEl = document.getElementById('summary-package-details');

    // --- State Check ---
    function getDaysFromDuration(duration) {
        // Extract the number before 'D' (e.g., 3D2N -> 3)
        const match = duration.match(/(\d+)D/);
        return match ? parseInt(match[1]) : 1;
    }

    // Store package durations for validation
    const packageDurations = {};
    document.querySelectorAll('.package-card').forEach(card => {
        const packageId = card.dataset.packageId;
        const durationText = card.dataset.duration;
        packageDurations[packageId] = getDaysFromDuration(durationText);
    });

    function checkAllSelections() {
        const packageSelected = hiddenPackageId.value !== '';
        const datesSelected = arriveDateInput.value !== '' && departDateInput.value !== '';
        const guestsSelected = adultsInput.value !== '';
        let validDuration = true;
        let warningMsg = '';
        if (packageSelected && datesSelected) {
            const requiredDays = packageDurations[hiddenPackageId.value];
            const date1 = new Date(arriveDateInput.value);
            const date2 = new Date(departDateInput.value);
            const selectedDays = Math.ceil((date2 - date1) / (1000 * 3600 * 24)) + 1;
            if (selectedDays !== requiredDays) {
                validDuration = false;
                warningMsg = `The selected package requires ${requiredDays} day(s). Please select the correct dates.`;
            }
        }
        const durationWarningBox = document.getElementById('duration-warning');
        const durationWarningText = document.getElementById('duration-warning-text');
        if (warningMsg) {
            durationWarningText.textContent = warningMsg;
            durationWarningBox.style.display = '';
        } else {
            durationWarningBox.style.display = 'none';
        }
        if (packageSelected && datesSelected && guestsSelected && validDuration) {
            bookBtn.disabled = false;
            bookBtn.classList.add('enabled');
        } else {
            bookBtn.disabled = true;
            bookBtn.classList.remove('enabled');
        }
    }

    // --- AJAX to Update Packages ---
    function updatePackageAvailability(arrivalDate) {
        fetch(`get_available_packages.php?arrival_date=${arrivalDate}`)
      .then(response => response.json())
            .then(availabilityData => {
                const packageCards = document.querySelectorAll('.package-card');
                packageCards.forEach(card => {
                    const packageId = card.dataset.packageId;
                    const selectBtn = card.querySelector('.select-btn');
                    if (!selectBtn) return;

                    if (availabilityData[packageId] && availabilityData[packageId].is_available) {
                        selectBtn.disabled = false;
                        selectBtn.textContent = 'Select';
                    } else {
                        selectBtn.disabled = true;
                        selectBtn.textContent = 'Unavailable';
                    }
                });
                if (hiddenPackageId.value && (!availabilityData[hiddenPackageId.value] || !availabilityData[hiddenPackageId.value].is_available)) {
                    hiddenPackageId.value = '';
                    document.querySelector('.package-card.selected')?.classList.remove('selected');
                    document.getElementById('summary-package-details').innerHTML = '<p>Please select a package</p>';
                    checkAllSelections();
                }
            })
            .catch(error => console.error('Error fetching package availability:', error));
    }

    // --- Initial Package Listeners ---
    const packageCards = document.querySelectorAll('.package-card');
    packageCards.forEach(card => {
        const selectBtn = card.querySelector('.select-btn');
        if (selectBtn) {
            selectBtn.addEventListener('click', function() {
                const packageId = card.dataset.packageId;
                const packageName = card.dataset.packageName;
                hiddenPackageId.value = packageId;
                packageCards.forEach(c => c.classList.remove('selected'));
                card.classList.add('selected');

                const adultPrice = parseFloat(card.dataset.adultPrice);
                const childPrice = parseFloat(card.dataset.childPrice);
                const arriveDateStr = arriveDateInput.value;
                const departDateStr = departDateInput.value;
                const numAdults = parseInt(adultsInput.value) || 0;
                const numChildren = parseInt(childrenInput.value) || 0;

                if (arriveDateStr && departDateStr && (numAdults > 0 || numChildren > 0)) {
                    const date1 = new Date(arriveDateStr);
                    const date2 = new Date(departDateStr);
                    const timeDiff = date2.getTime() - date1.getTime();
                    
                    // Corrected formula: calculate days instead of nights. (A 1-night stay is 2 days)
                    const dayCount = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1;

                    const totalCost = (adultPrice * numAdults + childPrice * numChildren) * dayCount;
                    
                    summaryTotalPriceEl.innerHTML = `<h3>MYR ${totalCost.toFixed(2)} total</h3>`;
                    
                    summaryPackageDetailsEl.innerHTML = `
                        <div class="summary-package-item">
                            <strong>${packageName}</strong>
                        </div>
                        <div class="summary-package-sub-item">
                            <span>${numAdults} Adult(s), ${numChildren} Child(ren)</span>
                        </div>
                        <div class="summary-package-sub-item">
                            <span>${dayCount} day(s)</span>
                            <span>MYR ${(adultPrice * numAdults + childPrice * numChildren).toFixed(2)}</span>
                        </div>
                        <hr>
                        <div class="summary-package-total">
                            <strong>Total</strong>
                            <strong>MYR ${totalCost.toFixed(2)}</strong>
                        </div>
                    `;
                } else {
                    summaryPackageDetailsEl.innerHTML = `<p><strong>${packageName}</strong></p><p>Please select dates and guests to see price.</p>`;
                    summaryTotalPriceEl.innerHTML = `<h3>Price will be calculated after full selection</h3>`;
                }

                checkAllSelections();
            });
        }
    });

    // --- Form Submission Check ---
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        if (bookBtn.disabled) {
            e.preventDefault();
            const packageSelected = hiddenPackageId.value !== '';
            const datesSelected = arriveDateInput.value !== '' && departDateInput.value !== '';
            if (packageSelected && datesSelected) {
                const requiredDays = packageDurations[hiddenPackageId.value];
                const date1 = new Date(arriveDateInput.value);
                const date2 = new Date(departDateInput.value);
                const selectedDays = Math.ceil((date2 - date1) / (1000 * 3600 * 24)) + 1;
                if (selectedDays !== requiredDays) {
                    alert(`The selected package requires ${requiredDays} day(s). Please select the correct dates.`);
                    return;
                }
            }
            alert('Please select a package, date, and number of guests before booking.');
        }
    });

    // --- Date Picker Modal Logic ---
    const dateModal = document.getElementById('dateModal');
    const dateSelector = document.getElementById('date-selector');
    const closeModal = dateModal.querySelector('.close-btn');

    dateSelector.onclick = function() {
        dateModal.style.display = "flex";
    }
    closeModal.onclick = function() {
        dateModal.style.display = "none";
    }
    window.addEventListener('click', function(event) {
        if (event.target == dateModal) {
            dateModal.style.display = "none";
        }
    });

    // --- Calendar Logic ---
    const calendarGrid = dateModal.querySelector('.calendar-grid');
    const monthYearEl = dateModal.querySelector('#monthYear');
    const prevMonthBtn = dateModal.querySelector('#prevMonth');
    const nextMonthBtn = dateModal.querySelector('#nextMonth');
    const confirmDatesBtn = document.getElementById('confirmDates');

    const datesDisplay = document.getElementById('dates-display');
    const summaryDates = document.getElementById('summary-dates');

    let currentDate = new Date();
    let arriveDate = null;
    let departDate = null;
    
    renderCalendar(currentDate);

    function renderCalendar(date) {
      const year = date.getFullYear();
      const month = date.getMonth();
        monthYearEl.textContent = date.toLocaleString('default', { month: 'long', year: 'numeric' }).toUpperCase();
        
        const dayNameCells = calendarGrid.querySelectorAll('.day-name');
        calendarGrid.innerHTML = '';
        dayNameCells.forEach(cell => calendarGrid.appendChild(cell));

      const firstDay = new Date(year, month, 1);
      const lastDay = new Date(year, month + 1, 0);
      const startDay = firstDay.getDay();
      const totalDays = lastDay.getDate();
        const today = new Date();
        today.setHours(0, 0, 0, 0);

      for (let i = 0; i < startDay; i++) {
        calendarGrid.appendChild(document.createElement('div'));
      }

      for (let day = 1; day <= totalDays; day++) {
        const cell = document.createElement('div');
            cell.classList.add('calendar-day');
        cell.textContent = day;
            const cellDate = new Date(year, month, day);
            const dateString = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

            if (cellDate < today) {
                cell.classList.add('unavailable');
            } else {
                cell.dataset.date = dateString;
                cell.onclick = () => selectDate(cell);
            }

            if (arriveDate && dateString === arriveDate) cell.classList.add('selected');
            if (departDate && dateString === departDate) cell.classList.add('selected');
            if (arriveDate && departDate && cellDate > new Date(arriveDate) && cellDate < new Date(departDate)) {
                 cell.classList.add('selected');
            }
        calendarGrid.appendChild(cell);
      }
    }

    function selectDate(cell) {
        const selectedDate = cell.dataset.date;
        if (!arriveDate || (arriveDate && departDate)) {
            arriveDate = selectedDate;
            departDate = null;
        } else if (new Date(selectedDate) < new Date(arriveDate)) {
            arriveDate = selectedDate;
        } else {
            departDate = selectedDate;
        }
        renderCalendar(currentDate);
    }

    confirmDatesBtn.onclick = function() {
        if (arriveDate && departDate) {
            arriveDateInput.value = arriveDate;
            departDateInput.value = departDate;
            const options = { weekday: 'short', month: 'long', day: 'numeric' };
            const d1 = new Date(arriveDate).toLocaleDateString('en-US', options);
            const d2 = new Date(departDate).toLocaleDateString('en-US', options);
            const displayString = `${d1} → ${d2}`;
            datesDisplay.textContent = displayString;
            summaryDates.textContent = displayString;
            
            updatePackageAvailability(arriveDate);

            const selectedPackageCard = document.querySelector('.package-card.selected');
            if (selectedPackageCard) {
                selectedPackageCard.querySelector('.select-btn').click();
            }

            checkAllSelections();
            dateModal.style.display = 'none';
        } else {
            alert("Please select both an arrival and departure date.");
        }
    };

    prevMonthBtn.onclick = () => {
      currentDate.setMonth(currentDate.getMonth() - 1);
      renderCalendar(currentDate);
    };

    nextMonthBtn.onclick = () => {
      currentDate.setMonth(currentDate.getMonth() + 1);
      renderCalendar(currentDate);
    };

    // --- Guest Picker Modal Logic ---
    const guestModal = document.getElementById('guestModal');
    const guestSelector = document.getElementById('guest-selector');
    const closeGuestModal = guestModal.querySelector('.close-btn');

    guestSelector.onclick = () => guestModal.style.display = 'flex';
    closeGuestModal.onclick = () => guestModal.style.display = 'none';
    
    window.addEventListener('click', function(event) {
        if (event.target == guestModal) {
            guestModal.style.display = "none";
        }
    });

    const adultsCount = document.getElementById('adults-count');
    const childrenCount = document.getElementById('children-count');

    guestModal.querySelector('#adults-increment').onclick = () => {
        adultsCount.textContent = parseInt(adultsCount.textContent) + 1;
    };
    guestModal.querySelector('#adults-decrement').onclick = () => {
        const count = parseInt(adultsCount.textContent);
        if (count > 1) adultsCount.textContent = count - 1;
    };
    guestModal.querySelector('#children-increment').onclick = () => {
        childrenCount.textContent = parseInt(childrenCount.textContent) + 1;
    };
    guestModal.querySelector('#children-decrement').onclick = () => {
        const count = parseInt(childrenCount.textContent);
        if (count > 0) childrenCount.textContent = count - 1;
    };

    document.getElementById('confirmGuests').onclick = function() {
        const numAdults = parseInt(adultsCount.textContent);
        const numChildren = parseInt(childrenCount.textContent);

        adultsInput.value = numAdults;
        childrenInput.value = numChildren;

        const guestsDisplayString = `${numAdults} Adult, ${numChildren} kids`;
        document.getElementById('guests-display').textContent = guestsDisplayString;
        document.getElementById('summary-guests').textContent = guestsDisplayString;
        
        checkAllSelections();
        guestModal.style.display = 'none';
    };
});
</script>

</body>
</html>
