# ⚡ VoltGrid — EV Charger Allocation System

A web-based EV charger allocation system that provides real-time station status, online slot booking, transparent cost estimation, and user dashboards for 3 charging stations across Mumbai.

> **WPL Mini Project 2025–26** — K.J. Somaiya School of Engineering  
> Parth Sharma (16010124138) · Ryshaan Shah (16010124134)

---

## SDG Alignment

| SDG | Goal | How VoltGrid Contributes |
|-----|------|--------------------------|
| **SDG 7** | Affordable & Clean Energy | Makes EV charging accessible with transparent pricing |
| **SDG 11** | Sustainable Cities | Reduces urban congestion from EV owners searching for chargers |
| **SDG 13** | Climate Action | Encourages EV adoption by simplifying the charging experience |

---

## Features

- **Multi-step Booking Wizard** — Select station → pick time slot → review & confirm, with a live cost calculator (session fee + 18% GST + ₹5 platform fee)
- **Station Catalogue with Search** — Browse all 3 stations, search by name/location/connector, filter by status, connector type, and power
- **User Authentication** — Registration with bcrypt password hashing, login with sessions, 30-day remember-me cookies (SHA-256 tokens)
- **User Dashboard** — View recent bookings, session info, and account details
- **Feedback System** — 3-tab support form (Feedback with star rating, Report Issue with file upload, General Contact) + FAQ accordion
- **Guest Bookings** — Users can book without creating an account
---

## Tech Stack

| Layer | Technologies |
|-------|-------------|
| **Frontend** | HTML5, CSS3, JavaScript (ES6+) |
| **Styling** | Outfit (Google Fonts), Custom CSS, Scroll-Snap, Backdrop-filter |
| **Backend** | PHP 8.x |
| **Database** | MySQL (InnoDB) |
| **Security** | bcrypt, Prepared Statements, httpOnly Cookies, Input Sanitisation |
| **Testing** | PHPUnit |

---

## Pages

| File | Description |
|------|-------------|
| `index.html` | Landing page — Tesla-inspired dark theme with scroll-snap hero and 3 station sections |
| `login.html` | Standalone login page — split-layout with dark feature panel + form |
| `register.html` | Registration — split-layout with personal info, vehicle details, password strength meter |
| `catalogue.html` | Station catalogue — search bar, status chips, connector/power filters, station cards |
| `booking.html` | 3-step booking wizard — station select → schedule → confirm, with live cost sidebar |
| `feedback.html` | Support — feedback/issue/contact tabs, star rating, FAQ accordion, contact sidebar |
| `dashboard.php` | User dashboard — session info, recent bookings table (auth required) |

---

## Database Schema

4 normalised tables with foreign keys:

```
┌──────────┐       ┌──────────┐
│ stations │       │  users   │
│──────────│       │──────────│
│ id (PK)  │       │ id (PK)  │
│ code     │       │ email    │
│ name     │       │ password │
│ location │       │ ...      │
│ power_kw │       └────┬─────┘
│ rate     │            │
└────┬─────┘            │
     │              1:N │ 1:N
     │ 1:N              │
┌────┴─────┐       ┌────┴─────┐
│ bookings │       │ feedback │
│──────────│       │──────────│
│ id (PK)  │       │ id (PK)  │
│ user_id  │←──────│ user_id  │
│station_id│       │station_id│
│ date     │       │ rating   │
│ amount   │       │ message  │
└──────────┘       └──────────┘
```

Full schema with constraints: [`voltgrid.sql`](voltgrid.sql)

---

## Setup

### Prerequisites
- PHP 8.x with MySQLi extension
- MySQL / MariaDB
- Apache or any PHP-capable server (XAMPP, WAMP, MAMP)

### Installation

```bash
# 1. Clone the repo
git clone https://github.com/parth-sharma-10/wpl-project.git
cd wpl-project

# 2. Import the database
mysql -u root -p < voltgrid.sql

# 3. Configure database connection (edit db.php if needed)
# Default: localhost / root / no password / voltgrid

# 4. Start your local server and open index.html
```

---

## Project Structure

```
wpl-project/
├── index.html              # Landing page
├── login.html              # Login page
├── register.html           # Registration page
├── catalogue.html          # Station catalogue + search
├── booking.html            # Booking wizard
├── feedback.html           # Support / feedback
├── dashboard.php           # User dashboard (auth)
├── process.php             # Login handler
├── post_registration.php   # Registration handler
├── save_booking.php        # Booking API endpoint
├── save_feedback.php       # Feedback API endpoint
├── session.php             # Session + cookie management
├── db.php                  # Database connection
├── logout.php              # Session teardown
├── voltgrid.sql            # Database schema + seed data
├── migrate_sessions.sql    # Migration for existing DBs
├── compare/                # Experiment 9 — Manual vs AI code
│   ├── manual/
│   ├── ai_generated/
│   └── COMPARISON_REPORT.md
└── tests/
    ├── VoltGridTest.php
    ├── phpunit.xml
    └── composer.json
```

---


---

## License

This project was built as an academic submission for the Web Programming Lab course at K.J. Somaiya School of Engineering, Mumbai.
