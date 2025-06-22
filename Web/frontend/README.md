# FoodExpress Frontend

A modern, responsive HTML frontend for the FoodExpress food delivery system, integrated with the PHP backend.

## Features

### 🎨 Modern UI/UX
- Clean, modern design with smooth animations
- Fully responsive layout for all devices
- Interactive elements with hover effects
- Loading states and toast notifications

### 🛒 Shopping Cart
- Real-time cart management
- Add/remove items with quantity controls
- Cart persistence across sessions
- Integrated with PHP backend database

### 👤 User Authentication
- Login and registration forms
- Form validation and error handling
- Session management
- Secure password handling

### 📱 Responsive Design
- Mobile-first approach
- Tablet and desktop optimized
- Touch-friendly interface
- Cross-browser compatibility

## File Structure

```
frontend/
├── index.html          # Homepage with menu and features
├── login.html          # User login page
├── register.html       # User registration page
├── cart.html           # Shopping cart page
├── css/
│   └── style.css       # Main stylesheet
├── js/
│   ├── main.js         # Main JavaScript functionality
│   └── cart.js         # Cart management
└── images/             # Image assets
```

## Backend Integration

### API Endpoints

The frontend communicates with the PHP backend through these API endpoints:

#### Menu Management
- `GET /api/get_menu.php` - Fetch all menu items
- Returns: JSON with menu items and categories

#### Cart Management
- `GET /api/get_cart.php` - Get user's cart items
- `POST /api/update_cart.php` - Update cart item quantity
- `POST /api/remove_from_cart.php` - Remove item from cart
- `POST /api/clear_cart.php` - Clear entire cart
- `GET /api/cart_count.php` - Get cart item count

#### Authentication
- `POST /login.php` - User login
- `POST /register.php` - User registration
- `GET /api/check_auth.php` - Check authentication status

#### Contact
- `POST /api/contact.php` - Submit contact form

### Database Integration

The frontend integrates with the existing MySQL database:

- **Users Table**: User registration and authentication
- **Foods Table**: Menu items with categories
- **Cart Table**: Shopping cart items per user
- **Categories Table**: Food categories
- **Contact Messages Table**: Contact form submissions

## Setup Instructions

### 1. Database Setup
Ensure your MySQL database is running and the tables are created:

```sql
-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cart table
CREATE TABLE cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    food_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (food_id) REFERENCES foods(id)
);

-- Contact messages table
CREATE TABLE contact_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 2. File Structure
Ensure your project structure looks like this:

```
mmm-main/
├── frontend/           # HTML frontend files
├── api/               # API endpoints
├── config/            # Database configuration
├── uploads/           # Food images
├── login.php          # Login handler
├── register.php       # Registration handler
├── cart_add.php       # Cart add handler
└── checkout.php       # Checkout page
```

### 3. Configuration
Update `config/database.php` with your database credentials:

```php
<?php
$host = 'localhost';
$dbname = 'fooddelivery';
$username = 'your_username';
$password = 'your_password';
?>
```

### 4. Web Server Setup
- Place the entire `mmm-main` folder in your web server directory
- Ensure PHP and MySQL are installed and configured
- Set proper file permissions for uploads directory

## Usage

### Accessing the Frontend
- **Homepage**: `http://your-domain/mmm-main/frontend/index.html`
- **Login**: `http://your-domain/mmm-main/frontend/login.html`
- **Register**: `http://your-domain/mmm-main/frontend/register.html`
- **Cart**: `http://your-domain/mmm-main/frontend/cart.html`

### User Flow
1. **Browse Menu**: Users can view all available food items
2. **Add to Cart**: Click "Add to Cart" to add items
3. **Manage Cart**: View cart, update quantities, remove items
4. **Checkout**: Proceed to checkout (redirects to PHP checkout page)
5. **Authentication**: Login/register as needed

### Admin Access
- **Admin Email**: admin@fooddelivery.com
- **Admin Password**: admin123
- **Admin Panel**: Access through the original PHP interface

## Features in Detail

### Menu System
- **Dynamic Loading**: Menu items loaded from database
- **Category Filtering**: Filter by food categories
- **Search Functionality**: Search through menu items
- **Responsive Grid**: Adapts to screen size

### Cart System
- **Real-time Updates**: Cart updates without page refresh
- **Quantity Controls**: Increase/decrease item quantities
- **Remove Items**: Remove items from cart
- **Cart Summary**: Total calculation with tax
- **Persistent Cart**: Cart saved to database

### Authentication
- **Form Validation**: Client-side and server-side validation
- **Error Handling**: User-friendly error messages
- **Session Management**: Secure session handling
- **Password Security**: Hashed passwords

### Responsive Design
- **Mobile First**: Optimized for mobile devices
- **Flexible Layout**: Adapts to different screen sizes
- **Touch Friendly**: Large touch targets for mobile
- **Fast Loading**: Optimized images and code

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Security Features

- **CSRF Protection**: Built-in CSRF token validation
- **SQL Injection Prevention**: Prepared statements
- **XSS Protection**: Input sanitization
- **Session Security**: Secure session handling
- **Password Hashing**: Bcrypt password hashing

## Performance Optimization

- **Minified CSS/JS**: Optimized file sizes
- **Image Optimization**: Compressed images
- **Lazy Loading**: Images load as needed
- **Caching**: Browser caching enabled
- **CDN Integration**: Font Awesome from CDN

## Troubleshooting

### Common Issues

1. **API Endpoints Not Working**
   - Check database connection
   - Verify file permissions
   - Check PHP error logs

2. **Images Not Loading**
   - Ensure uploads directory exists
   - Check file permissions
   - Verify image paths

3. **Cart Not Updating**
   - Check user authentication
   - Verify database tables
   - Check JavaScript console for errors

4. **Login Issues**
   - Verify database credentials
   - Check user table structure
   - Ensure password hashing is working

### Debug Mode
Enable debug mode by checking browser console for detailed error messages.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is licensed under the MIT License.

## Support

For support and questions:
- Check the troubleshooting section
- Review the code comments
- Contact the development team

---

**Note**: This frontend is designed to work seamlessly with the existing PHP backend. Make sure all backend files are properly configured and the database is set up correctly before using the frontend. 