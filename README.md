# Online Computer Store E-commerce Project (Final Project Submission)

## Project Description
This is a complete, functional e-commerce web application built using the LAMP stack (PHP/MySQL). The system includes user authentication, a shopping cart utilizing sessions/database storage, secure checkout using PDO transactions, and a full admin dashboard for managing products and orders.

## Key Features
* User Registration and Login (with password hashing).
* Product Browsing, Searching, and Detail Viewing.
* Dynamic Shopping Cart (Add/Update/Remove items).
* Checkout Process (implemented securely using PDO transactions).
* Customer Order History view for logged-in users.
* Admin Dashboard: Add, Edit, Delete Products; View and Update Order Status.

## Setup and Running Instructions (MAMP Environment)

This project must be run locally using a web server environment like MAMP/XAMPP/WAMP.

## 1. Database Setup
1.  **Start MAMP:** Ensure your Apache and MySQL servers are running.
2.  **Create Database:** Open phpMyAdmin and create a new, empty database (e.g., `online_store`).
3.  **Import Schema:** Select the new database and click the **Import** tab.
4.  Import the SQL file located at **`sql/online_store.sql`** to create all tables and populate initial data.

## 2. Code Configuration
1.  Update the database connection settings in **`config.php`** to match your MAMP credentials (default Host: `localhost:8889`, User: `root`, Password: `root`).

## 3. Access Site
1.  Place the project folder (`online_computer_store`) inside your MAMP's `htdocs` directory.
2.  Navigate to the following URL in your browser:
    `http://localhost:8888/online_computer_store/index.php`

## Project Developer Information
**Name:** Sophia Alphanso
**Student ID:** 229530880