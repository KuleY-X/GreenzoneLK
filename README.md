# GreenzoneLk eCommerce Platform

A complete full-stack eCommerce web application for eco-friendly plants and gardening products, built with PHP, MySQL, HTML, and CSS.

## 🌱 Features

### User Management
- ✅ User registration with input validation
- ✅ Secure login/logout system
- ✅ Password hashing (PHP's password_hash())
- ✅ Session management with timeout
- ✅ CSRF protection

### Product Management
- ✅ Product categories system
- ✅ Product listing with category filters
- ✅ Search functionality
- ✅ Stock quantity tracking
- ✅ Product images and descriptions

### Shopping Cart
- ✅ Add products to cart
- ✅ Update quantities
- ✅ Remove items
- ✅ Clear entire cart
- ✅ Real-time cart count display
- ✅ Stock validation

### Security Features
- ✅ Input sanitization and validation
- ✅ SQL injection prevention (PDO prepared statements)
- ✅ XSS protection
- ✅ CSRF token validation
- ✅ Session security

### Design
- ✅ Responsive design for all devices
- ✅ Modern CSS with gradients and animations
- ✅ Clean, eco-friendly green theme
- ✅ Intuitive user interface

## 🚀 Installation & Setup

### Prerequisites
- **Web Server** (Apache/Nginx)
- **PHP 7.4+** with PDO MySQL extension
- **MySQL 5.7+** or **MariaDB 10.2+**

### Step 1: Download Files
Clone or download all project files to your web server directory:
```
GreenzoneLK/
├── config.php          # Database configuration
├── setup.php           # Database setup script
├── database.sql        # Database structure and sample data
├── index.php           # Main product listing page
├── register.php        # User registration
├── login.php           # User login
├── logout.php          # User logout
├── cart.php            # Shopping cart
├── add_to_cart.php     # Add to cart handler
├── img/                # Images directory
│   ├── core-img/       # Core images (logo, icons)
│   └── bg-img/         # Background images
└── README.md           # This file
```

### Step 2: Configure Database
1. Edit `config.php` and update database settings:
```php
define('DB_HOST', 'localhost');     // Your database host
define('DB_USER', 'root');          // Your database username
define('DB_PASS', '');              // Your database password
define('DB_NAME', 'greenzone_ecommerce');
```

2. Update site URL if needed:
```php
define('SITE_URL', 'http://localhost/GreenzoneLK');
```

### Step 3: Run Setup
1. Navigate to `http://your-domain/GreenzoneLK/setup.php`
2. The setup script will:
   - Check system requirements
   - Create the database and tables
   - Insert sample data
   - Display success confirmation

### Step 4: Start Using
After setup, you can:
- **Browse Products**: Visit `index.php`
- **Create Account**: Visit `register.php`
- **Login**: Visit `login.php`

## 📊 Database Structure

### Tables Created:
1. **users** - User accounts and profiles
2. **categories** - Product categories
3. **products** - Product catalog
4. **cart** - Shopping cart items

### Sample Data Included:
- **5 Categories**: Indoor Plants, Outdoor Plants, Fertilizers, Pots & Containers, Gardening Tools
- **11 Products**: Aloe Vera, Snake Plant, Monstera, Rose, Hibiscus, Organic Compost, etc.
- All products have realistic prices in Sri Lankan Rupees (Rs.)

## 🔐 Security Features

### Authentication & Authorization
- Secure password hashing using `password_hash()`
- Session-based authentication
- Session timeout protection
- Login attempt protection

### Data Protection
- All inputs sanitized with `htmlspecialchars()`
- SQL injection prevention with PDO prepared statements
- CSRF tokens on all forms
- XSS protection

### Validation
- Email format validation
- Password strength requirements
- Username uniqueness checks
- Stock quantity validation

## 🎨 Design Features

### Responsive Design
- Mobile-first CSS approach
- Flexible grid layouts
- Touch-friendly buttons
- Optimized for all screen sizes

### Visual Elements
- Green eco-friendly color scheme
- Smooth hover animations
- Modern card-based layouts
- Clean typography (Poppins font)

## 🛍️ User Journey

### For New Users:
1. **Register** → Create account with personal details
2. **Browse** → Explore products by category
3. **Search** → Find specific products
4. **Add to Cart** → Select desired items
5. **Manage Cart** → Update quantities, remove items
6. **Checkout** → (Ready for future implementation)

### For Returning Users:
1. **Login** → Access existing account
2. **Continue Shopping** → Add more items to cart
3. **Manage Cart** → Review and modify cart contents

## 🔧 Technical Implementation

### Backend (PHP)
- **MVC-like structure** with separated concerns
- **PDO database abstraction** for security and portability
- **Session management** with security best practices
- **Error handling** with user-friendly messages
- **Input validation** at multiple levels

### Frontend (HTML/CSS)
- **Semantic HTML5** structure
- **Pure CSS** (no frameworks) for lightweight performance
- **CSS Grid & Flexbox** for responsive layouts
- **CSS animations** for enhanced user experience

### Database (MySQL)
- **Normalized structure** to reduce redundancy
- **Foreign key constraints** for data integrity
- **Indexes** for optimal query performance
- **Default values** and constraints for data consistency

## 📱 Responsive Breakpoints

- **Desktop**: 1200px+ (Full layout with all features)
- **Tablet**: 768px - 1199px (Adjusted grid and navigation)
- **Mobile**: 480px - 767px (Single column, simplified layout)
- **Small Mobile**: <480px (Minimal spacing, larger touch targets)

## 🔄 Future Enhancements

### Ready for Implementation:
- Order management system
- Payment gateway integration
- User profile management
- Product reviews and ratings
- Email notifications
- Admin panel for product management
- Order history and tracking
- Wishlist functionality

### Suggested Improvements:
- AJAX cart updates for better UX
- Product image gallery
- Advanced search with filters
- Inventory management alerts
- Customer support chat
- Multi-language support

## 🐛 Troubleshooting

### Common Issues:

**"Database connection failed"**
- Check MySQL service is running
- Verify database credentials in `config.php`
- Ensure database exists

**"Headers already sent" error**
- Check for spaces/content before `<?php` tags
- Ensure no echo/print before redirects

**Images not displaying**
- Verify `img/` directory exists
- Check file permissions
- Ensure image paths are correct

**Session issues**
- Check PHP session configuration
- Verify write permissions for session directory
- Clear browser cookies and cache

## 📄 License

This project is open source and available under the MIT License.

## 🤝 Contributing

Contributions are welcome! Please feel free to submit pull requests or open issues for bugs and feature requests.

## 📧 Support

For technical support or questions, please open an issue in the project repository.

---

**Happy Gardening with GreenzoneLk! 🌿**
