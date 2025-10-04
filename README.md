# Tasik Biru Camps Reservation System

This repository contains the complete source code for the **Tasik Biru Camps Reservation System**, developed as part of a **UiTM ISP250 and CSC264 group project**.

While this repository includes all modules (Customer, Staff, Admin), my personal contribution to the project was the **development of the Admin Module** — including both backend logic and frontend interface — as well as implementing the **staff-side email sending functionality**.

---

## 📌 Project Overview

Tasik Biru Camps Reservation System is a web-based platform that streamlines campsite booking and management.  
It allows customers to book packages, staff to manage operations, and administrators to oversee the entire system.

---

## 👤 My Role & Contributions

As part of the group collaboration, my responsibilities included:

### **Admin Module Development**
- **Admin Authentication** – Secure login for administrators.
- **Booking Management** – View, search, edit, and delete reservations.
- **Package Management** – Add, edit, delete packages; manage slots and images.
- **Staff Management** – Manage staff details, schedules, and facility assignments.
- **Feedback Management** – Review customer feedback with optional media.
- **Payment Management** – Track and filter payment records; generate PDF summaries.
- **Analytics** – Visual charts using Chart.js for revenue and booking distribution.

### **Staff Module Contribution**
- **Email Sending Feature** – Implemented automated email notifications for staff operations using PHPMailer.

---

## 🛠 Technology Stack

- **Backend:** PHP (MySQLi)
- **Frontend:** HTML5, CSS3, JavaScript
- **Database:** MySQL (via XAMPP)
- **Additional Tools:** Chart.js, AJAX for live search and dynamic updates, PHPMailer for email functionality
- **Dependency Management:** Composer (PHP)

---

## 📥 Download & Install Composer

This project uses **Composer** to manage PHP libraries and mainly used to send emails using PHPMailer.  
If you don’t already have Composer installed, follow these steps:

1. Go to the official Composer download page:  
   👉 [https://getcomposer.org/download/](https://getcomposer.org/download/)

2. Download the **Composer-Setup.exe** for Windows.

3. Run the installer and follow the prompts:
   - Select your PHP installation path (e.g., `C:\xampp\php\php.exe`).
   - Keep default settings for the rest.

4. After installation, verify Composer is installed by running in Command Prompt:
   ```bash
   composer -V


   
## 📂 Installation & Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/RidhwanHazian/camp.git
