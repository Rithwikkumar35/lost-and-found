# TraceBack - Lost & Found LFIS

A secure web-based Lost and Found Item Search (LFIS) system that helps users report and claim lost items using AI-powered verification questions.

## Features

### Core Functionality
- 🔐 **Secure User Authentication** - Registration and login with password hashing
- 📝 **Post Lost Items** - Users can report lost items with descriptions and images
- 🔍 **Post Found Items** - Users can report found items
- 🎯 **Browse Items** - Search and filter lost/found items by category or keywords
- 📋 **Item Claims** - Claim items with AI-powered verification questions
- 💬 **Messaging System** - Communicate between users
- 📲 **Notifications** - Get notified about item claims and messages
- 👤 **User Profiles** - Manage personal information and settings

### Security Features
- ✅ Prepared statements (SQL injection prevention)
- ✅ CSRF token protection
- ✅ Input validation and sanitization
- ✅ File upload validation (type, size, MIME)
- ✅ Password strength requirements
- ✅ Session security with ID regeneration
- ✅ Output escaping (XSS prevention)
- ✅ Authorization checks
- ✅ Environment-based configuration
- ✅ Security logging

### AI-Powered Verification
- 🤖 Automatic question generation based on item type
- ❓ 4 security questions per item
- ✔️ Answer verification with scoring (requires 3/4 correct)

## Technology Stack

- **Backend:** PHP 7.4+
- **Database:** MySQL/MariaDB
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Architecture:** Server-side rendered (No frameworks)

## Installation

### Requirements
- PHP 7.4 or higher
- MySQL/MariaDB 5.7 or higher
- Web server (Apache, Nginx, etc.)
- cURL and GD libraries

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/Rithwikkumar35/lost-and-found.git
   cd lost-and-found
   ```

2. **Configure environment variables**
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials
   nano .env
   ```

3. **Create uploads directory**
   ```bash
   mkdir uploads
   chmod 755 uploads
   ```

4. **Import database schema**
   ```bash
   mysql -u your_username -p your_database < database.sql
   ```

5. **Set proper file permissions**
   ```bash
   chmod 755 includes/
   chmod 644 includes/*.php
   ```

## Configuration

### .env File

Create `.env` file in root directory:

```env
# Database Configuration
DB_HOST=localhost
DB_USER=root
DB_PASS=your_password
DB_NAME=lost_found_db

# Application Settings
APP_ENV=production
APP_DEBUG=false

# Security
SESSION_TIMEOUT=3600
```

## Usage

### For Users

1. **Register an Account**
   - Go to `/register.php`
   - Enter valid email and strong password
   - Password must have 8+ chars, uppercase, lowercase, and numbers

2. **Post a Lost Item**
   - Login and click "Post Lost Item"
   - Enter item details and upload image
   - AI will generate 4 security questions
   - Answer the questions to post

3. **Claim an Item**
   - Browse items on `/browse.php`
   - Click on an item
   - Fill claim message
   - Answer verification questions
   - Need 3/4 correct answers to proceed

4. **Manage Items**
   - View your posted items in "My Items"
   - View your claims in "My Claims"
   - Accept or reject claims

## API Endpoints

### Authentication
- `POST /login.php` - User login
- `POST /register.php` - User registration
- `GET /logout.php` - User logout
- `POST /forgot_password.php` - Password recovery

### Items
- `GET /browse.php` - Browse all items
- `GET /item.php?id={id}` - View item details
- `POST /post_lost.php` - Post lost item
- `POST /post_found.php` - Post found item
- `GET /my_items.php` - View user's items

### Claims
- `POST /claim.php?id={id}` - Submit item claim
- `GET /my_claims.php` - View user's claims
- `POST /approve_claim.php` - Approve claim
- `POST /reject_claim.php` - Reject claim

## Database Schema

### Users Table
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Items Table
```sql
CREATE TABLE items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(50) NOT NULL,
    item_type ENUM('lost', 'found') NOT NULL,
    image VARCHAR(255),
    user_id INT NOT NULL,
    verify_q1 TEXT, verify_q2 TEXT, verify_q3 TEXT, verify_q4 TEXT,
    answer1 TEXT, answer2 TEXT, answer3 TEXT, answer4 TEXT,
    status ENUM('active', 'claimed', 'archived') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Claims Table
```sql
CREATE TABLE claims (
    id INT PRIMARY KEY AUTO_INCREMENT,
    item_id INT NOT NULL,
    claimant_id INT NOT NULL,
    message TEXT NOT NULL,
    claim_answer1 TEXT, claim_answer2 TEXT, claim_answer3 TEXT, claim_answer4 TEXT,
    answers_verified BOOLEAN DEFAULT 0,
    verification_score INT DEFAULT 0,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES items(id),
    FOREIGN KEY (claimant_id) REFERENCES users(id)
);
```

## Security Best Practices

### Implemented
✅ SQL injection prevention (prepared statements)
✅ CSRF protection (token validation)
✅ XSS prevention (output escaping)
✅ Password security (bcrypt hashing)
✅ File upload validation
✅ Session security
✅ Input validation
✅ Error logging

### Recommended Future Improvements
- [ ] Two-factor authentication (2FA)
- [ ] Rate limiting on login attempts
- [ ] Email verification on registration
- [ ] OAuth social login
- [ ] HTTPS enforcement
- [ ] Security headers (CSP, X-Frame-Options)
- [ ] API rate limiting
- [ ] Database encryption

## Troubleshooting

### Database Connection Error
- Check `.env` file credentials
- Ensure database server is running
- Verify database user has correct privileges
- Check MySQL error logs

### File Upload Issues
- Verify `uploads/` directory exists and is writable
- Check file size and type restrictions
- Ensure proper file permissions (755)
- Check disk space availability

### Session Issues
- Clear browser cookies
- Check session storage directory permissions
- Verify PHP session settings
- Check session timeout in `.env`

## Contributing

Contributions are welcome! Please:
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is open source and available under the MIT License.

## Support

For issues, questions, or suggestions:
- Create an GitHub issue
- Contact: kodarikumar978@gmail.com
- Check SECURITY_FIXES.md for security-related changes

## Credits

Developed by Rithwik Kumar Kodari

---

**Last Updated:** June 1, 2026
**Version:** 2.0.0 (Security Enhanced)
**Status:** Production Ready ✅
