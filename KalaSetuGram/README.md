# 🏵️ KalaSetuGram - AI-Powered Digital Craft Village

**Bridging Tradition with Technology** - A comprehensive digital platform connecting traditional Andhra Pradesh artisans with global markets through cutting-edge technology including AI recommendations and AR experiences.

## 🌟 Project Overview

KalaSetuGram is a full-featured e-commerce platform designed to preserve and promote traditional crafts from Andhra Pradesh, India. The platform combines modern web technologies with cultural heritage preservation, featuring AI-powered recommendations, AR craft viewing, and direct artisan support programs.

## ✨ Key Features

### 🔐 User Authentication & Management
- **Multi-role Registration**: Buyer, Artisan, Tourist, Admin roles
- **OTP Email Verification**: Secure account verification system
- **Forgot Password**: 3-step password recovery process
- **Profile Management**: Complete user profile system

### 🏪 E-Commerce Platform
- **Craft Marketplace**: Advanced filtering by category, price, GI tag status
- **Smart Search**: AI-powered search suggestions and results
- **Product Details**: Rich product pages with image galleries
- **Shopping Cart**: Full cart management with quantity controls
- **Secure Checkout**: Multi-step checkout with address management
- **Payment Integration**: Razorpay, PayPal, and Cash on Delivery support

### 🤖 AI-Powered Features
- **Recommendation Engine**: 5 types of intelligent recommendations
  - Similar products (content-based filtering)
  - Collaborative filtering (users who bought this also bought)
  - Personalized recommendations (based on user history)
  - Trending crafts (based on recent sales and popularity)
  - Category-based suggestions
- **Smart Search**: AI-enhanced search with auto-suggestions

### 🥽 AR/VR Integration
- **AR Craft Viewer**: Full AR experience using AR.js and A-Frame
- **3D Model Support**: GLTF model integration for realistic viewing
- **Interactive Controls**: Scale, rotate, and capture AR experiences
- **Mobile Optimized**: Touch gestures and mobile-friendly AR interface

### 🎨 Cultural Heritage Features
- **Heritage Stories**: Rich storytelling platform for craft histories
- **Adopt-an-Artisan Program**: Direct monthly support system for artisans
- **Cultural Narratives**: Detailed stories behind each craft tradition
- **GI Tag Integration**: Geographical Indication tag highlighting

### 👨‍💼 Admin Dashboard
- **Comprehensive Analytics**: Sales, revenue, and user statistics
- **Order Management**: Complete order tracking and management
- **User & Artisan Management**: User verification and profile management
- **Content Management**: Craft, category, and story management
- **Coupon System**: Discount code creation and management

## 🛠️ Technology Stack

### Frontend
- **PHP**: Server-side scripting
- **HTML5/CSS3**: Modern responsive design
- **Bootstrap 5**: UI framework with custom styling
- **JavaScript (ES6+)**: Interactive functionality
- **AR.js & A-Frame**: Augmented Reality integration
- **Chart.js**: Data visualization for admin dashboard

### Backend
- **PHP 8+**: Core backend logic
- **MySQL**: Relational database with 12+ tables
- **PDO**: Secure database connections with prepared statements
- **RESTful APIs**: JSON-based API endpoints

### External Integrations
- **Razorpay**: Payment gateway for Indian market
- **PayPal**: International payment processing
- **Email System**: OTP and notification system
- **AR Model Support**: 3D model rendering and interaction

## 📁 Project Structure

```
KalaSetuGram/
├── admin/                  # Admin dashboard and management
│   └── dashboard.php      # Main admin dashboard
├── api/                   # RESTful API endpoints
│   ├── cart.php          # Cart management API
│   ├── recommendations.php # AI recommendation engine
│   ├── search.php        # Search functionality
│   └── validate-coupon.php # Coupon validation
├── assets/               # Static assets
│   ├── css/
│   │   └── style.css     # Custom styling with cultural theme
│   ├── js/
│   │   └── main.js       # Core JavaScript functionality
│   └── images/           # Image assets and placeholders
├── auth/                 # Authentication system
│   ├── login.php         # User login
│   ├── register.php      # User registration
│   ├── verify-otp.php    # OTP verification
│   ├── forgot-password.php # Password recovery
│   └── logout.php        # Session termination
├── config/               # Configuration files
│   └── database.php      # Database connection and schema
├── includes/             # Reusable components
│   ├── functions.php     # Core PHP functions
│   ├── navbar.php        # Navigation component
│   └── footer.php        # Footer component
├── index.php             # Landing page
├── crafts.php            # Marketplace listing
├── craft-detail.php      # Product detail page
├── cart.php              # Shopping cart
├── checkout.php          # Checkout process
├── payment.php           # Payment processing
├── order-success.php     # Order confirmation
├── ar-viewer.php         # AR experience page
├── heritage-stories.php  # Cultural stories
├── adopt-artisan.php     # Artisan adoption program
└── README.md             # Project documentation
```

## 🗄️ Database Schema

The platform uses a comprehensive MySQL database with 12+ interconnected tables:

- **users**: User accounts and authentication
- **artisans**: Artisan profiles and verification
- **craft_categories**: Craft categorization with GI tag support
- **crafts**: Product catalog with AR model support
- **craft_images**: Product image gallery
- **orders & order_items**: Complete order management
- **cart**: Shopping cart functionality
- **coupons**: Discount system
- **heritage_stories**: Cultural storytelling
- **adopt_artisan**: Artisan support program
- **reviews**: Product review system

## 🚀 Installation & Setup

### Prerequisites
- **XAMPP/WAMP/LAMP**: Local server environment
- **PHP 8.0+**: Server-side scripting
- **MySQL 5.7+**: Database management
- **Modern Web Browser**: Chrome, Firefox, Safari, Edge

### Installation Steps

1. **Clone/Download Project**
   ```bash
   # Place files in your web server directory
   # For XAMPP: C:\xampp\htdocs\KalaSetuGram\
   ```

2. **Start Services**
   ```bash
   # Start Apache and MySQL services
   # Via XAMPP Control Panel or command line
   ```

3. **Database Setup**
   ```bash
   # Database auto-initializes on first visit
   # Default admin credentials:
   # Email: admin@kalasetugramdb.com
   # Password: admin123
   ```

4. **Access Application**
   ```
   http://localhost/KalaSetuGram/
   ```

### Configuration

1. **Database Settings** (config/database.php):
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USERNAME', 'root');
   define('DB_PASSWORD', '');
   define('DB_NAME', 'kalasetugramdb');
   ```

2. **Payment Gateway Setup**:
   - Add Razorpay API keys in payment.php
   - Configure PayPal client ID for PayPal integration

3. **Email Configuration**:
   - Set up SMTP settings for OTP delivery
   - Configure email templates for notifications

## 🎯 User Roles & Permissions

### 👤 Buyer
- Browse and search crafts
- Add items to cart and checkout
- View order history and track orders
- Leave reviews and ratings
- Participate in adopt-artisan program

### 🎨 Artisan
- Create and manage craft listings
- Upload product images and AR models
- View sales analytics and earnings
- Communicate with customers
- Manage profile and verification status

### 🏛️ Tourist
- Explore heritage stories and cultural content
- Browse crafts without purchasing
- Access AR experiences and virtual tours
- Learn about traditional craft techniques

### 👨‍💼 Admin
- Complete platform management
- User and artisan verification
- Order and payment management
- Content moderation and curation
- Analytics and reporting dashboard

## 🌈 Design Philosophy

### Cultural Aesthetics
- **Color Palette**: Terracotta (#d4a574), Sandalwood (#8b4513), Ivory (#faf8f5)
- **Typography**: Clean, readable fonts with cultural sensitivity
- **Imagery**: Focus on traditional crafts and artisan photography
- **Layout**: Balanced design respecting both tradition and modernity

### User Experience
- **Mobile-First**: Responsive design for all devices
- **Accessibility**: WCAG compliance for inclusive design
- **Performance**: Optimized loading and smooth interactions
- **Intuitive Navigation**: Clear user flows and logical structure

## 🔧 API Endpoints

### Cart Management
```
POST /api/cart.php
GET /api/cart.php?action=list
GET /api/cart.php?action=count
```

### AI Recommendations
```
GET /api/recommendations.php?type=similar&craft_id=123
GET /api/recommendations.php?type=personalized&user_id=456
GET /api/recommendations.php?type=trending&limit=6
```

### Search Functionality
```
GET /api/search.php?q=kalamkari&suggestions=true
GET /api/search.php?q=kondapalli&page=1&limit=12
```

### Coupon Validation
```
POST /api/validate-coupon.php
Body: {"coupon_code": "FESTIVAL20", "cart_total": 2500}
```

## 🎨 AR/VR Features

### AR Craft Viewer
- **Marker-based AR**: Uses Hiro marker for precise tracking
- **Markerless AR**: Fallback mode for devices without marker support
- **Interactive Controls**: Scale, rotate, reset, and capture functions
- **Mobile Gestures**: Pinch-to-zoom and touch interactions
- **Social Sharing**: Share AR experiences directly from the viewer

### 3D Model Support
- **GLTF Format**: Industry-standard 3D model format
- **Realistic Rendering**: Proper lighting and material support
- **Animation Support**: Animated models with loop controls
- **Optimization**: Compressed models for fast loading

## 🤖 AI Recommendation Engine

### Algorithm Types

1. **Content-Based Filtering**
   - Analyzes craft attributes (category, price, artisan)
   - Similarity scoring based on multiple factors
   - Handles cold start problem for new users

2. **Collaborative Filtering**
   - "Users who bought this also bought" recommendations
   - Purchase history analysis and pattern recognition
   - Cross-selling optimization

3. **Personalized Recommendations**
   - User behavior and preference learning
   - Purchase history and browsing patterns
   - Dynamic scoring based on user profile

4. **Trending Analysis**
   - Real-time sales data processing
   - Popularity scoring with time decay
   - Featured content promotion

5. **Category-Based Suggestions**
   - Craft category exploration
   - Related category recommendations
   - GI tag and regional preferences

## 📊 Analytics & Reporting

### Dashboard Metrics
- **User Analytics**: Registration trends, active users, demographics
- **Sales Analytics**: Revenue tracking, order patterns, conversion rates
- **Product Analytics**: Popular crafts, category performance, inventory
- **Artisan Analytics**: Top performers, earnings distribution, activity

### Visualization
- **Interactive Charts**: Monthly sales, revenue trends, user growth
- **Real-time Data**: Live order tracking and payment status
- **Export Capabilities**: PDF reports and CSV data export
- **Custom Filters**: Date ranges, categories, regions

## 🔒 Security Features

### Data Protection
- **SQL Injection Prevention**: Prepared statements and input validation
- **XSS Protection**: Output sanitization and CSP headers
- **CSRF Protection**: Token-based form validation
- **Password Security**: Bcrypt hashing with salt

### Authentication Security
- **OTP Verification**: Time-limited email verification codes
- **Session Management**: Secure session handling and timeout
- **Role-Based Access**: Granular permission system
- **Admin Protection**: Two-factor authentication for admin accounts

## 🌐 Deployment Considerations

### Production Setup
- **SSL Certificate**: HTTPS encryption for secure transactions
- **Database Optimization**: Indexing and query optimization
- **CDN Integration**: Static asset delivery optimization
- **Backup Strategy**: Automated database and file backups

### Performance Optimization
- **Image Compression**: Optimized product images and thumbnails
- **Caching Strategy**: Browser caching and server-side optimization
- **Database Indexing**: Query performance optimization
- **Minification**: CSS and JavaScript compression

## 🤝 Contributing

### Development Guidelines
- Follow PSR-12 coding standards for PHP
- Use semantic HTML5 and accessible CSS
- Implement responsive design principles
- Write clean, documented JavaScript

### Cultural Sensitivity
- Respect traditional craft representations
- Accurate cultural information and stories
- Appropriate imagery and language use
- Artisan consent for profile information

## 📄 License & Credits

### Open Source Components
- **Bootstrap**: MIT License
- **Font Awesome**: Font Awesome Free License
- **AR.js**: MIT License
- **A-Frame**: MIT License
- **Chart.js**: MIT License

### Cultural Attribution
- Traditional craft information sourced from verified artisans
- Heritage stories collected with community permission
- GI tag information from official government sources
- Artisan profiles created with explicit consent

## 🎯 Future Enhancements

### Planned Features
- **Mobile App**: Native iOS and Android applications
- **Voice Search**: Multi-language voice search capability
- **Blockchain**: Craft authenticity verification using blockchain
- **IoT Integration**: Smart workshop monitoring for artisans
- **VR Experiences**: Virtual craft workshop tours

### AI Improvements
- **Computer Vision**: Automatic craft categorization from images
- **Natural Language Processing**: Enhanced search and chatbot
- **Predictive Analytics**: Demand forecasting and inventory optimization
- **Sentiment Analysis**: Review and feedback analysis

---

## 📞 Support & Contact

For technical support, feature requests, or cultural collaboration opportunities:

- **Email**: support@kalasetugramdb.com
- **Documentation**: [Project Wiki](link-to-wiki)
- **Issues**: [GitHub Issues](link-to-issues)
- **Community**: [Discord Server](link-to-discord)

---

**KalaSetuGram** - Preserving tradition through technology, one craft at a time. 🏵️
