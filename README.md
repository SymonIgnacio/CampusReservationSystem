# Campus Reservation System Database Update

## Database Update Instructions

1. Open phpMyAdmin by navigating to http://localhost/phpmyadmin in your browser
2. Select the `campus_db` database (or create it if it doesn't exist)
3. Go to the SQL tab
4. Copy and paste the contents of the `database_update.sql` file
5. Click "Go" to execute the SQL commands

## Changes Made

- Created a new `request` table with the specified structure
- Updated the API endpoints to work with the new database structure
- Modified the frontend components to use the new API endpoints

## New Database Structure

```sql
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
    status ENUM('pending', 'approved', 'declined') DEFAULT 'pending'
);
```