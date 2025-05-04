-- Database: tap_swap_db
CREATE DATABASE IF NOT EXISTS tap_swap_db;
USE tap_swap_db;

-- User Authentication Table
CREATE TABLE users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL, -- Store hashed passwords
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Warehouse Table
CREATE TABLE warehouses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  location_code VARCHAR(20) UNIQUE,
  address TEXT,
  contact_person VARCHAR(100),
  contact_email VARCHAR(100),
  contact_phone VARCHAR(20)
);

-- Transporters Table
CREATE TABLE transporters (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  contact_person VARCHAR(100),
  contact_email VARCHAR(100),
  contact_phone VARCHAR(20)
);

-- Vehicles Table
CREATE TABLE vehicles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vehicle_number VARCHAR(20) UNIQUE NOT NULL,
  type ENUM('Truck', 'Mini Truck', 'Container', 'Van', 'Tanker', 'Other') DEFAULT 'Other',
  transporter_id INT NOT NULL,
  capacity_tons DECIMAL(10, 2) DEFAULT NULL, -- Optional capacity
  insurance_expiry DATE DEFAULT NULL,      -- Optional compliance data
  last_maintenance DATE DEFAULT NULL,      -- Optional compliance data
  is_active BOOLEAN DEFAULT TRUE,
  FOREIGN KEY (transporter_id) REFERENCES transporters(id) ON DELETE RESTRICT -- Prevent deleting transporter if vehicles exist
);

-- Drivers Table
CREATE TABLE drivers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  contact_number VARCHAR(15) UNIQUE NOT NULL,
  license_number VARCHAR(30) UNIQUE, -- Optional, but good for compliance
  license_expiry DATE DEFAULT NULL,   -- Optional compliance data
  transporter_id INT, -- A driver might work for a specific transporter
  is_active BOOLEAN DEFAULT TRUE,
  FOREIGN KEY (transporter_id) REFERENCES transporters(id) ON DELETE SET NULL -- Driver can exist without transporter
);

-- Cargo Types Table
CREATE TABLE cargo_types (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type_name VARCHAR(50) UNIQUE NOT NULL,
  description TEXT,
  requires_special_handling BOOLEAN DEFAULT FALSE
);

-- Purchase Orders Table
CREATE TABLE purchase_orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  po_number VARCHAR(50) UNIQUE NOT NULL,
  vendor_name VARCHAR(100),
  order_date DATE,
  expected_delivery_date DATE,
  status ENUM('Open', 'Partial', 'Closed', 'Cancelled') DEFAULT 'Open'
);

-- Appointments Table
CREATE TABLE appointments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  appointment_uid VARCHAR(20) UNIQUE NOT NULL, -- Unique human-readable ID
  vehicle_id INT NOT NULL,
  driver_id INT NOT NULL,
  transporter_id INT NOT NULL,
  warehouse_id INT NOT NULL,
  cargo_type_id INT NOT NULL,
  po_id INT NULL, -- Can be nullable if appointment is not tied to a PO
  unloading_bay_no VARCHAR(10) NULL, -- Assigned later perhaps
  gate_pass_number VARCHAR(50) NULL, -- Assigned on arrival
  appointment_datetime DATETIME NOT NULL, -- Changed from DATE to DATETIME for time slots
  estimated_duration_mins INT DEFAULT 60, -- Estimated time needed
  arrival_datetime DATETIME NULL,
  unloading_start_datetime DATETIME NULL,
  unloading_end_datetime DATETIME NULL,
  departure_datetime DATETIME NULL,
  cargo_details TEXT, -- E.g., item list, quantity, weight
  special_instructions TEXT NULL,
  status ENUM('Scheduled', 'Arrived', 'Unloading', 'Delayed', 'Completed', 'Cancelled') DEFAULT 'Scheduled',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  created_by INT NULL, -- Link to users table
  last_updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (vehicle_id) REFERENCES vehicles(id),
  FOREIGN KEY (driver_id) REFERENCES drivers(id),
  FOREIGN KEY (transporter_id) REFERENCES transporters(id),
  FOREIGN KEY (warehouse_id) REFERENCES warehouses(id),
  FOREIGN KEY (cargo_type_id) REFERENCES cargo_types(id),
  FOREIGN KEY (po_id) REFERENCES purchase_orders(id),
  FOREIGN KEY (created_by) REFERENCES users(user_id)
);

-- Compliance Log Table (Example)
CREATE TABLE compliance_log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NULL, -- Can be linked to an appointment
    vehicle_id INT NULL, -- Or just a vehicle check
    driver_id INT NULL, -- Or just a driver check
    check_type VARCHAR(100), -- e.g., 'Insurance Valid', 'License Valid', 'Vehicle Condition'
    check_result ENUM('Pass', 'Fail', 'Warning'),
    notes TEXT NULL,
    checked_by INT NULL, -- Link to users table
    checked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL,
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE SET NULL,
    FOREIGN KEY (checked_by) REFERENCES users(user_id)
);
