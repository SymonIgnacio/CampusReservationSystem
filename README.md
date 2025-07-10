# Campus Reservation System

A comprehensive web-based system for managing campus facility reservations with a two-tier approval workflow (GSO → VPO).

## Features

- **User Management**: Firebase authentication with role-based access
- **Request Management**: Create, track, and manage facility reservation requests
- **Two-Tier Approval**: GSO (General Services Office) and VPO (Vice President Office) approval workflow
- **Calendar Integration**: Visual calendar showing upcoming and finished events
- **Conflict Detection**: Automatic venue and date conflict checking
- **Equipment Management**: Track and reserve equipment with availability checking
- **Dashboard Analytics**: Real-time statistics and reporting
- **Responsive Design**: Works on desktop and mobile devices

## Tech Stack

### Frontend
- **React.js** (v18+)
- **React Router** for navigation
- **Context API** for state management
- **CSS3** for styling
- **Boxicons** for icons

### Backend
- **PHP** (v7.4+)
- **MySQL** (v8.0+)
- **Firebase Authentication**

### Development Tools
- **XAMPP** (Apache + MySQL + PHP)
- **Node.js** (v16+)
- **npm** or **yarn**

## Prerequisites

1. **XAMPP** - Download and install from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. **Node.js** - Download from [https://nodejs.org/](https://nodejs.org/)
3. **Git** - For version control
4. **Firebase Account** - For authentication setup

## Installation

### 1. Clone the Repository
```bash
git clone <repository-url>
cd CampusReservationSystem
```

### 2. Frontend Setup
```bash
# Install dependencies
npm install

# Required packages (if not automatically installed)
npm install react react-dom react-router-dom
npm install firebase
npm install boxicons
```

### 3. Backend Setup

1. **Start XAMPP**
   - Start Apache and MySQL services

2. **Database Setup**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Create database: `campus_db`
   - Import the database structure (see Database Schema section)

3. **File Placement**
   - Copy the entire project to `C:\xampp\htdocs\CampusReservationSystem`

### 4. Firebase Configuration

1. Create a Firebase project at [https://console.firebase.google.com/](https://console.firebase.google.com/)
2. Enable Authentication with Email/Password
3. Get your Firebase config
4. Create `src/firebase.js`:

```javascript
import { initializeApp } from 'firebase/app';
import { getAuth } from 'firebase/auth';

const firebaseConfig = {
  apiKey: "your-api-key",
  authDomain: "your-auth-domain",
  projectId: "your-project-id",
  storageBucket: "your-storage-bucket",
  messagingSenderId: "your-sender-id",
  appId: "your-app-id"
};

const app = initializeApp(firebaseConfig);
export const auth = getAuth(app);
export default app;
```

## Database Schema

Execute these SQL commands in phpMyAdmin:

```sql
-- Main request table
CREATE TABLE request (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reference_number VARCHAR(50) UNIQUE,
    date_created DATETIME DEFAULT CURRENT_TIMESTAMP,
    request_by VARCHAR(100) NOT NULL,
    department_organization VARCHAR(100) NOT NULL,
    activity VARCHAR(255) NOT NULL,
    purpose TEXT NOT NULL,
    nature_of_activity ENUM('curricular', 'co-curricular') NOT NULL,
    date_need_from DATE NOT NULL,
    date_need_until DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    participants TEXT,
    total_male_attendees INT DEFAULT 0,
    total_female_attendees INT DEFAULT 0,
    total_attendees INT GENERATED ALWAYS AS (total_male_attendees + total_female_attendees) STORED,
    venue VARCHAR(100) NOT NULL,
    equipments_needed TEXT,
    status ENUM('pending_gso', 'pending_vpo', 'approved', 'declined') DEFAULT 'pending_gso'
);

-- Approved requests table
CREATE TABLE approved_request (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reference_number VARCHAR(50),
    request_by VARCHAR(100),
    department_organization VARCHAR(100),
    activity VARCHAR(255),
    purpose TEXT,
    nature_of_activity VARCHAR(50),
    date_need_from DATE,
    date_need_until DATE,
    start_time TIME,
    end_time TIME,
    participants TEXT,
    total_male_attendees INT DEFAULT 0,
    total_female_attendees INT DEFAULT 0,
    venue VARCHAR(100),
    equipments_needed TEXT,
    approved_by VARCHAR(100),
    approved_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Users table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    firebase_uid VARCHAR(255) UNIQUE,
    username VARCHAR(100),
    firstname VARCHAR(100),
    lastname VARCHAR(100),
    email VARCHAR(255),
    department VARCHAR(100),
    role ENUM('student', 'faculty', 'admin', 'sysadmin', 'vpo') DEFAULT 'student',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Venues table
CREATE TABLE venues (
    id INT PRIMARY KEY AUTO_INCREMENT,
    venue VARCHAR(255) NOT NULL,
    capacity INT,
    description TEXT
);

-- Equipment table
CREATE TABLE equipment (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    total_quantity INT DEFAULT 0,
    available_quantity INT DEFAULT 0,
    description TEXT
);
```

## Running the Application

### 1. Start Backend Services
```bash
# Start XAMPP (Apache + MySQL)
# Ensure services are running on:
# - Apache: http://localhost
# - MySQL: localhost:3306
```

### 2. Start Frontend Development Server
```bash
npm start
# Application will open at http://localhost:3000
```

## Default Accounts

Create these accounts in Firebase Authentication:

- **Admin/GSO**: `admin@example.com` / `password123`
- **System Admin**: `systemadmin@example.com` / `password123`
- **VPO**: `VPO@example.com` / `password123`

## Project Structure

```
CampusReservationSystem/
├── public/
├── src/
│   ├── components/
│   ├── context/
│   │   ├── AuthContext.jsx
│   │   └── EventContext.jsx
│   ├── Pages/
│   │   ├── Admin/
│   │   ├── Dashboard/
│   │   ├── Request Event/
│   │   ├── SysAdmin/
│   │   └── VPO/
│   ├── api/
│   │   ├── create_request.php
│   │   ├── approve_request.php
│   │   ├── get_requests.php
│   │   └── [other PHP files]
│   ├── firebase.js
│   └── App.js
├── package.json
└── README.md
```

## API Endpoints

- `POST /api/create_request.php` - Create new request
- `GET /api/get_requests.php` - Get pending requests (Admin)
- `GET /api/get_vpo_requests.php` - Get VPO pending requests
- `POST /api/approve_request.php` - Approve request
- `POST /api/decline_request.php` - Decline request
- `GET /api/admin_dashboard_approved_events.php` - Get approved events
- `POST /api/check_venue_conflict.php` - Check venue availability

## Workflow

1. **User** creates a reservation request
2. **GSO** reviews and approves/declines (status: `pending_gso` → `pending_vpo`)
3. **VPO** gives final approval/decline (status: `pending_vpo` → `approved_request` table)
4. **Approved events** appear in calendars as upcoming events

## Troubleshooting

### Common Issues

1. **CORS Errors**
   - Ensure `cors_fix.php` is included in API files
   - Check Apache configuration

2. **Database Connection**
   - Verify MySQL is running
   - Check database credentials in PHP files

3. **Firebase Authentication**
   - Verify Firebase config in `firebase.js`
   - Check Firebase project settings

4. **Missing Dependencies**
   ```bash
   npm install --force
   ```

### Development Tips

- Use browser developer tools for debugging
- Check PHP error logs in XAMPP
- Monitor network requests for API issues
- Use `console.log()` for frontend debugging

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is licensed under the MIT License.