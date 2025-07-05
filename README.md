# GPG Store Management System

A comprehensive web-based inventory and sales management solution designed for small to medium-sized retail businesses. Built with PHP, MySQL, and modern web technologies, this system provides a complete suite of tools for managing products, sales, inventory, and business operations.

## 🚀 Features

### Core Functionality
- **📊 Dashboard Analytics**: Real-time overview of sales metrics, inventory status, and business performance
- **🔐 Secure Authentication**: Role-based access control with session management
- **📦 Product Management**: Complete product lifecycle management with categorization and tracking
- **💰 Sales & Order Processing**: End-to-end order management with payment tracking
- **🏪 Warehouse Operations**: Inventory tracking, stock management, and supplier relationships
- **📈 Reporting & Analytics**: Comprehensive business intelligence and reporting tools

### Technical Features
- **🌐 Responsive Design**: Mobile-friendly interface that works across all devices
- **🎨 Modern UI/UX**: Clean, intuitive interface with Lao language support
- **⚡ Performance Optimized**: Fast loading times and efficient database queries
- **🔒 Security Focused**: Session-based authentication and data protection

## 📋 Requirements

- **Web Server**: Apache/Nginx with PHP support
- **PHP**: Version 7.4 or higher
- **Database**: MySQL 5.7+ or MariaDB 10.2+
- **Browser**: Modern web browser with JavaScript enabled

## 🛠️ Installation

### Prerequisites
1. Install a local web server environment (XAMPP, WAMP, or MAMP)
2. Ensure PHP and MySQL are properly configured
3. Create a MySQL database for the application

### Setup Steps

1. **Clone or Download the Project**
   ```bash
   # If using Git
   git clone [repository-url]
   
   # Or download and extract to your web server directory
   # Example: C:\xampp\htdocs\Store system GPG\
   ```

2. **Database Setup**
   - Open your MySQL administration tool (phpMyAdmin)
   - Create a new database named `gpg_store_system`
   - Import the database schema from `sql/create_database.sql`

3. **Configuration**
   - Navigate to `db/connection.php`
   - Update database credentials if needed:
     ```php
     $host = 'localhost';
     $dbname = 'gpg_store_system';
     $username = 'your_username';
     $password = 'your_password';
     ```

4. **Access the Application**
   - Start your web server
   - Open your browser and navigate to:
     ```
     http://localhost/Store system GPG/login.php
     ```

5. **Default Login Credentials**
   - **Username**: Admin
   - **Password**: password123

## 📁 Project Structure

```
Store system GPG/
├── 📄 Core Application Files
│   ├── index.php              # Main dashboard
│   ├── login.php              # Authentication
│   ├── logout.php             # Session termination
│   ├── add_product.php        # Product creation
│   ├── edit_product.php       # Product modification
│   ├── storage.php            # Inventory management
│   ├── edit_order.php         # Order processing
│   ├── ordering_payment.php   # Payment management
│   ├── warehouse.php          # Warehouse operations
│   └── report.php             # Business reporting
│
├── 📁 includes/
│   ├── auth.php               # Authentication middleware
│   └── navbar.php             # Navigation component
│
├── 📁 db/
│   └── connection.php         # Database configuration
│
├── 📁 assets/
│   ├── css/
│   │   └── style.css          # Main stylesheet
│   └── js/
│       └── scripts.js         # Interactive functionality
│
├── 📁 sql/
│   └── create_database.sql    # Database schema
│
└── 📄 README.md               # This file
```

## 🗄️ Database Schema

The system uses a relational database with the following key entities:

| Entity | Purpose |
|--------|---------|
| **Customer** | Customer information and contact details |
| **Employee** | Staff accounts and authentication |
| **Supplier** | Vendor and supplier management |
| **Product** | Product catalog with specifications |
| **PurchaseOrder** | Supplier purchase tracking |
| **Sell** | Customer sales records |
| **Payment** | Payment status and tracking |

For detailed schema information, refer to `sql/create_database.sql`.

## 🎯 Usage Guide

### Getting Started
1. **Login**: Use your employee credentials to access the system
2. **Dashboard**: Review key metrics and recent activities
3. **Navigation**: Use the top navigation bar to access different modules

### Key Operations

#### Product Management
- **Add Products**: Navigate to Storage → Add Product
- **Edit Products**: Use the edit button in the product list
- **Inventory Tracking**: Monitor stock levels and low-stock alerts

#### Sales Operations
- **Create Orders**: Process customer orders through the ordering system
- **Payment Tracking**: Update payment status and track outstanding amounts
- **Order History**: View and manage previous transactions

#### Warehouse Management
- **Stock Management**: Track product quantities and locations
- **Supplier Relations**: Manage supplier information and purchase orders
- **Import Tracking**: Monitor product imports and costs

#### Reporting
- **Sales Reports**: Analyze sales performance and trends
- **Financial Reports**: Track income, expenses, and profitability
- **Inventory Reports**: Monitor stock levels and movement

## 🔧 Customization

### Styling
- Modify `assets/css/style.css` to customize the visual appearance
- Update color schemes, fonts, and layout as needed

### Localization
- The interface supports Lao language
- Add additional language support by modifying text strings

### Functionality
- Extend the database schema for additional features
- Add new modules by following the existing code structure
- Implement additional security measures as needed

## 🔒 Security Considerations

### Current Implementation
- Session-based authentication
- Role-based access control
- SQL injection protection through prepared statements

### Production Recommendations
- **Password Security**: Implement password hashing (bcrypt/Argon2)
- **HTTPS**: Use SSL/TLS encryption for all communications
- **Input Validation**: Add comprehensive input sanitization
- **Rate Limiting**: Implement login attempt restrictions
- **Regular Updates**: Keep PHP and dependencies updated

## 🐛 Troubleshooting

### Common Issues

**Database Connection Error**
- Verify database credentials in `db/connection.php`
- Ensure MySQL service is running
- Check database name and permissions

**Page Not Found**
- Confirm web server is running
- Verify file paths and permissions
- Check URL configuration

**Login Issues**
- Verify default credentials or reset password in database
- Check session configuration
- Clear browser cache and cookies

## 📞 Support

For technical support or feature requests:
- Review the code documentation
- Check the database schema for data structure
- Ensure all requirements are met

## 📄 License

This project is developed for educational and demonstration purposes. Please ensure compliance with your local regulations when using this system in production environments.

## 🤝 Contributing

Contributions are welcome! Please ensure:
- Code follows existing patterns and conventions
- Database changes are documented
- Security best practices are maintained
- Testing is performed before submission

---

**Version**: 1.0.0  
**Last Updated**: 2024  
**Compatibility**: PHP 7.4+, MySQL 5.7+ 