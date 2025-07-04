CREATE DATABASE IF NOT EXISTS store_db;
USE store_db;

CREATE TABLE Customer (
    c_id INT AUTO_INCREMENT PRIMARY KEY,
    c_name VARCHAR(255) NOT NULL,
    tel VARCHAR(20),
    address TEXT
);

CREATE TABLE Employee (
    emp_id INT AUTO_INCREMENT PRIMARY KEY,
    emp_name VARCHAR(255) NOT NULL,
    tel VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE,
    address TEXT
);

-- Default employee for login
INSERT INTO `employee` (`emp_id`, `emp_name`, `tel`, `password`, `email`, `address`) VALUES
(1, 'Admin', '123456789', 'password123', 'admin@example.com', '123 Main St');


CREATE TABLE Supplier (
    sup_id INT AUTO_INCREMENT PRIMARY KEY,
    sup_name VARCHAR(255) NOT NULL,
    address TEXT,
    tel VARCHAR(20)
);

CREATE TABLE `Order` (
    od_id INT AUTO_INCREMENT PRIMARY KEY,
    od_name VARCHAR(255),
    sup_id INT,
    emp_id INT,
    FOREIGN KEY (sup_id) REFERENCES Supplier(sup_id),
    FOREIGN KEY (emp_id) REFERENCES Employee(emp_id)
);

CREATE TABLE ProductType (
    pt_id INT AUTO_INCREMENT PRIMARY KEY,
    pt_name VARCHAR(255) NOT NULL
);

CREATE TABLE ProductBrand (
    pb_id INT AUTO_INCREMENT PRIMARY KEY,
    pb_name VARCHAR(255) NOT NULL
);

CREATE TABLE ProductShelf (
    pslf_id INT AUTO_INCREMENT PRIMARY KEY,
    pslf_location VARCHAR(255)
);

CREATE TABLE ProductUnit (
    punit_id INT AUTO_INCREMENT PRIMARY KEY,
    punit_name VARCHAR(50)
);

CREATE TABLE Product (
    p_id INT AUTO_INCREMENT PRIMARY KEY,
    p_name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2),
    pt_id INT,
    pb_id INT,
    pslf_id INT,
    punit_id INT,
    FOREIGN KEY (pt_id) REFERENCES ProductType(pt_id),
    FOREIGN KEY (pb_id) REFERENCES ProductBrand(pb_id),
    FOREIGN KEY (pslf_id) REFERENCES ProductShelf(pslf_id),
    FOREIGN KEY (punit_id) REFERENCES ProductUnit(punit_id)
);

CREATE TABLE OrderDetail (
    odd_id INT AUTO_INCREMENT PRIMARY KEY,
    od_id INT,
    qty INT,
    p_id INT,
    FOREIGN KEY (od_id) REFERENCES `Order`(od_id),
    FOREIGN KEY (p_id) REFERENCES Product(p_id)
);

CREATE TABLE Sell (
    s_id INT AUTO_INCREMENT PRIMARY KEY,
    `date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    c_id INT,
    FOREIGN KEY (c_id) REFERENCES Customer(c_id)
);

CREATE TABLE SellDetail (
    sd_id INT AUTO_INCREMENT PRIMARY KEY,
    total_price DECIMAL(10, 2),
    qty INT,
    p_id INT,
    s_id INT,
    FOREIGN KEY (p_id) REFERENCES Product(p_id),
    FOREIGN KEY (s_id) REFERENCES Sell(s_id)
);

CREATE TABLE Payment (
    pm_id INT AUTO_INCREMENT PRIMARY KEY,
    qty INT,
    `date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    s_id INT,
    FOREIGN KEY (s_id) REFERENCES Sell(s_id)
);

CREATE TABLE Import (
    ip_id INT AUTO_INCREMENT PRIMARY KEY,
    `date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    od_id INT,
    FOREIGN KEY (od_id) REFERENCES `Order`(od_id)
);

CREATE TABLE ImportDetail (
    ipd_id INT AUTO_INCREMENT PRIMARY KEY,
    ip_id INT,
    p_id INT,
    qty INT,
    price DECIMAL(10, 2),
    FOREIGN KEY (ip_id) REFERENCES Import(ip_id),
    FOREIGN KEY (p_id) REFERENCES Product(p_id)
);