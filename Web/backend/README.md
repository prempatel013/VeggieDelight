# FoodExpress Backend

Welcome to the backend of FoodExpress! This is the engine that powers the food delivery app, handling everything from user accounts to order processing.

## Getting Started

Here's how to get the backend running on your local machine.

### Prerequisites

You'll need a basic web server setup:
- A web server like Apache or Nginx
- PHP (7.4+)
- MySQL (5.7+)

### Setup Steps

1.  **Web Server:** Drop the `mmm-main` folder into your web server's main directory (like `htdocs` in XAMPP).
2.  **Database:**
    *   Create a new MySQL database (e.g., `food_delivery`).
    *   Import the `database.sql` file into your new database. This file has the table structure and some sample data to get you started.
3.  **Configuration:**
    *   In `backend/config/`, you'll need a `database.php` file.
    *   If it's not there, create it and add your database details like this:
        ```php
        <?php
        $host = 'localhost';
        $dbname = 'food_delivery';
        $username = 'root';
        $password = '';
        ?>
        ```

That's it! The backend should now be connected and running.

## What's Inside?

-   `/admin`: The admin panel for managing the restaurant.
-   `/api`: All the API endpoints that the frontend talks to.
-   `/config`: Holds the database connection details.
-   `*.php` files: The main logic for login, registration, cart, and checkout.

## Admin Panel

You can manage the restaurant's data through the admin panel.
-   **URL:** `http://localhost/mmm-main/backend/admin/`
-   **Login:**
    -   **Email:** `admin@fooddelivery.com`
    -   **Password:** `admin123`

From the admin panel, you can manage food items, categories, and customer orders.
 ** go to **`http://localhost/e-com/`