# Filament Manager

## Overview

**Filament Manager** is a web-based tool for organizing 3D printer filaments. It allows users to catalog filaments by brand, type, color, ideal print temperatures, and spool size. Users can also track their filament inventory and store temperature tower images for easy reference.

This application is built with PHP 8.3 and MySQL, and it uses Bootstrap for its user interface.

## Features

- Add, edit, and delete filaments with brand, material, color, and ideal print temperatures.
- Track filament inventory with multiple spool sizes (250g, 500g, 750g, 1000g, 2000g).
- Manage users with different access levels (admin and regular users).
- Profile page for users to change their password.
- Admin control panel to add or remove users.
- Secure login system with password hashing.
- Fully responsive UI with Bootstrap.

## Setup Instructions

### 1. Clone the Repository

```bash
git clone https://github.com/engren/filamentmanager.git
cd filamentmanager
```

### 2. Install Dependencies

Make sure you have **composer** installed on your system. Run the following command to install dependencies:

```bash
composer install
```

### 3. Configure the `.env` File

Create a `.env` file in the root directory of the project and add your database credentials. This file stores sensitive configuration data like database connection details.

#### Example `.env` file:

```ini
DB_HOST=localhost
DB_USER=root
DB_PASS=your_password
DB_NAME=filament_manager
```

- **DB_HOST**: The database host, usually `localhost` if it's running locally.
- **DB_USER**: The MySQL username.
- **DB_PASS**: The MySQL password.
- **DB_NAME**: The name of your database.

### 4. Create the Database and Tables

You need to create a MySQL database and set up the necessary tables for this application. Below is the SQL script to create the required tables.

#### SQL Script:

```sql
CREATE DATABASE IF NOT EXISTS filament_manager;

USE filament_manager;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE filaments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    brand VARCHAR(100) NOT NULL,
    material VARCHAR(100) NOT NULL,
    color VARCHAR(50) NOT NULL,
    unique_id VARCHAR(50) NOT NULL UNIQUE,
    ideal_nozzle_temp INT NOT NULL,
    ideal_bed_temp INT NOT NULL,
    rolls_250g INT DEFAULT 0,
    rolls_500g INT DEFAULT 0,
    rolls_750g INT DEFAULT 0,
    rolls_1000g INT DEFAULT 0,
    rolls_2000g INT DEFAULT 0,
    purchase_url VARCHAR(255),
    notes TEXT,
    image_url VARCHAR(255)
);
```

- The **`users`** table stores user login information, including hashed passwords.
- The **`filaments`** table stores detailed information about each filament, including brand, material, color, spool size, and more.

### 5. Upload the Filament Manager Logo

Make sure to upload the **Filament Manager** logo in `.webp` format to the `/images/` directory:

- Place the logo as `filament-manager-logo.png` (or `webp`) in the `/images/` folder.

### 6. Run the Application

Once the database and `.env` file are configured, you can serve the application using your web server (Apache, Nginx, etc.). Ensure that PHP 8.3 and MySQL are properly installed and configured.

Visit the application in your browser:

```bash
http://localhost/filamentmanager
```

### 7. Default Admin User

To log in as an admin, you can manually insert an admin user into the **users** table:

```sql
INSERT INTO users (username, password) VALUES ('admin', 'hashed_password');
```

Make sure to hash the password before inserting. You can use tools like PHP's `password_hash()` to generate a hashed password:

```php
<?php
echo password_hash('your_password', PASSWORD_DEFAULT);
?>
```

## Usage

- **Admin Users**: Admin users have access to the **Manage Users** section and can add, edit, or delete users.
- **Regular Users**: Regular users can update their profile and manage filaments but cannot manage users.
- **Profile Page**: Users can change their password on the profile page.

## Practical details?

I personally add the filament to the database and print a temperature tower. Once printed, I write the ID beneath the temperature tower with a permanent marker and put it in my inventory box.

This allows me to view what the filament looks like when printed, both feel, colour, texture etc when selecting a filament for a particular print and it also helps me enter the optimal temperature for each filament model/brand in the database.

## Support

Provided as is, but happy to assist with any questions using issues.

