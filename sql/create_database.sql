DROP DATABASE IF EXISTS store_db;

CREATE DATABASE store_db;

USE store_db;

-- Customer table
CREATE TABLE Customer (
    c_id INT AUTO_INCREMENT PRIMARY KEY,
    c_name VARCHAR(255) NOT NULL,
    tel VARCHAR(20),
    address TEXT
);

-- Employee table
CREATE TABLE Employee (
    emp_id INT AUTO_INCREMENT PRIMARY KEY,
    emp_name VARCHAR(255) NOT NULL,
    tel VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE,
    address TEXT,
    role ENUM('admin', 'employee') NOT NULL DEFAULT 'employee'
);

-- Supplier table
CREATE TABLE Supplier (
    sup_id INT AUTO_INCREMENT PRIMARY KEY,
    sup_name VARCHAR(255) NOT NULL,
    address TEXT,
    tel VARCHAR(20)
);

-- Product table (type, brand, unit, shelf as columns)
CREATE TABLE product (
    p_id INT AUTO_INCREMENT PRIMARY KEY,
    p_name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    qty INT DEFAULT 0,
    unit VARCHAR(50),
    shelf VARCHAR(100),
    type VARCHAR(100),
    image_path VARCHAR(255) -- New column to store the image file path
);

-- Purchase Order (from suppliers)
CREATE TABLE PurchaseOrder (
    po_id INT AUTO_INCREMENT PRIMARY KEY,
    sup_id INT,
    emp_id INT,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sup_id) REFERENCES Supplier (sup_id),
    FOREIGN KEY (emp_id) REFERENCES Employee (emp_id)
);

-- Purchase Order Details
CREATE TABLE PurchaseOrderDetail (
    pod_id INT AUTO_INCREMENT PRIMARY KEY,
    po_id INT,
    p_id INT,
    qty INT,
    price DECIMAL(10, 2),
    FOREIGN KEY (po_id) REFERENCES PurchaseOrder (po_id),
    FOREIGN KEY (p_id) REFERENCES Product (p_id)
);

-- Sell (sales to customers)
CREATE TABLE Sell (
    s_id INT AUTO_INCREMENT PRIMARY KEY,
    c_id INT,
    emp_id INT,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('unpaid', 'paid') DEFAULT 'unpaid',
    FOREIGN KEY (c_id) REFERENCES Customer (c_id),
    FOREIGN KEY (emp_id) REFERENCES Employee (emp_id)
);

-- Sell Details
CREATE TABLE SellDetail (
    sd_id INT AUTO_INCREMENT PRIMARY KEY,
    s_id INT,
    p_id INT,
    qty INT,
    price DECIMAL(10, 2),
    total_price DECIMAL(10, 2),
    FOREIGN KEY (s_id) REFERENCES Sell (s_id),
    FOREIGN KEY (p_id) REFERENCES Product (p_id)
);

-- Payment table
CREATE TABLE Payment (
    pm_id INT AUTO_INCREMENT PRIMARY KEY,
    s_id INT,
    amount DECIMAL(10, 2),
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('unpaid', 'paid') DEFAULT 'unpaid',
    FOREIGN KEY (s_id) REFERENCES Sell (s_id)
);
-- Product Type
CREATE TABLE ProductType (
    pt_id INT AUTO_INCREMENT PRIMARY KEY,
    pt_name VARCHAR(255) NOT NULL
);

-- Product Brand
CREATE TABLE ProductBrand (
    pb_id INT AUTO_INCREMENT PRIMARY KEY,
    pb_name VARCHAR(255) NOT NULL
);

-- Product Shelf
CREATE TABLE ProductShelf (
    pslf_id INT AUTO_INCREMENT PRIMARY KEY,
    pslf_location VARCHAR(255) NOT NULL
);

-- Product Unit
CREATE TABLE ProductUnit (
    punit_id INT AUTO_INCREMENT PRIMARY KEY,
    punit_name VARCHAR(100) NOT NULL
);

-- Default admin and employee
INSERT INTO
    Employee (
        emp_name,
        tel,
        password,
        email,
        address,
        role
    )
VALUES (
        'Admin',
        '123456789',
        'password123',
        'admin@example.com',
        '123 Main St',
        'admin'
    );

INSERT INTO
    Employee (
        emp_name,
        tel,
        password,
        email,
        address,
        role
    )
VALUES (
        'Employee',
        '123456789',
        'password123',
        'employee@example.com', -- ← You missed this comma
        '456 Elm St',
        'employee'
    );

ALTER TABLE Product ADD COLUMN category VARCHAR(100);