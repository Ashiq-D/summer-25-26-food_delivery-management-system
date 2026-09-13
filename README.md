# CraveRush - Food Delivery Management System

A project for a 4-role food delivery management system: **admin, customer, restaurant, deliveryman**.

**GitHub Repository:** `summer-25-26-food_delivery-management-system`

Written in plain PHP with procedural `mysqli` and prepared statements. No framework, no Composer, and no build step. Copy it into XAMPP, import the included database, and run it locally.

---

## 1. Install (XAMPP)

1. Copy the `summer-25-26-food_delivery-management-system` project folder into:

   `C:\xampp\htdocs\`

2. Start **Apache** and **MySQL** from the XAMPP Control Panel.

3. Open:

   `http://localhost/phpmyadmin`

   Go to **Import** and choose the project's `database.sql` file, then click **Go**.

   The SQL file creates `CRAVERUSH_DB` and inserts the included Bangladesh-flavoured sample data.

4. Open:

   `http://localhost/summer-25-26-food_delivery-management-system/`

5. You can also open the project root through:

   `http://localhost/<your-project-folder>/`

   depending on the folder name you place inside `htdocs`.

### Database settings

The database connection is defined in `config/config.php`:

```php
define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_NAME", "CRAVERUSH_DB");
```

If your MySQL/MariaDB installation uses a password, change `DB_PASS` accordingly.

---

## 2. Folder structure

```text
summer-25-26-food_delivery-management-system/

├── index.php                         Landing / home page
├── database.sql                      Database schema + sample data
│
├── config/
│   └── config.php                    Database connection + session setup
│
├── helpers/
│   └── helpers.php                   Input cleaning + image upload/path helpers
│
├── models/
│   ├── user_model.php                Customer / Restaurant / Deliveryman / Admin queries
│   ├── food_model.php                Food item queries
│   ├── order_model.php               Cart / order / delivery / cancellation queries
│   ├── admin_model.php               Admin-side management queries
│   ├── restaurant_model.php          Restaurant-side queries
│   ├── deliveryman_model.php         Deliveryman-side queries
│   └── review_model.php              Restaurant / deliveryman review queries
│
├── controllers/
│   ├── login_controller.php           Login and role detection
│   ├── auth_controller.php            Customer / Restaurant / Deliveryman registration
│   ├── admin_controller.php           Admin actions
│   ├── customer_controller.php        Customer data loading
│   ├── customer_ajax_controller.php   Customer AJAX endpoints
│   ├── customer_profile_controller.php Customer profile actions
│   ├── restaurant_controller.php      Restaurant actions
│   ├── restaurant_ajax_controller.php Restaurant AJAX endpoints
│   ├── deliveryman_controller.php     Deliveryman actions
│   └── ajax_controller.php             General AJAX endpoints
│
├── views/
│   ├── auth/
│   │   ├── login.php
│   │   └── register.php
│   │
│   ├── admin/
│   │   ├── dashboard.php
│   │   ├── users.php
│   │   ├── food_items.php
│   │   ├── orders.php
│   │   ├── areas.php
│   │   ├── requests.php
│   │   ├── reports.php
│   │   └── profile.php
│   │
│   ├── customer/
│   │   ├── home.php
│   │   ├── profile.php
│   │   └── partials/
│   │
│   ├── deliveryman/
│   │   ├── dashboard.php
│   │   ├── current.php
│   │   ├── orders.php
│   │   ├── history.php
│   │   ├── earnings.php
│   │   ├── profile.php
│   │   └── partials/
│   │
│   ├── restaurant_dashboard/
│   │   ├── restaurant_dashboard.php
│   │   ├── profile.php
│   │   └── partials/
│   │
│   └── partials/
│       ├── header.php
│       └── footer.php
│
└── assets/
    ├── css/
    ├── js/
    ├── images/
    └── uploads/
```

The project follows an **MVC-style separation**:

- **Models** contain database operations.
- **Controllers** process requests, validate input, perform business actions, and redirect/load views.
- **Views** contain the user interface.
- **Helpers** contain reusable utility functions.
- AJAX controllers provide JSON-based operations used by the customer/restaurant interfaces.

---

## 3. How the application works

The project does not use a framework router. Different views load their corresponding controllers, and controllers call the required model functions.

The main flow is:

```text
Browser
   ↓
View / Controller
   ↓
Model
   ↓
MySQL Database
   ↓
Model result
   ↓
Controller
   ↓
View / JSON response
```

### Important entry points

| Page | Purpose |
| --- | --- |
| `index.php` | Public CraveRush landing page |
| `views/auth/login.php` | Login for all four roles |
| `views/auth/register.php` | Registration for customer, restaurant and deliveryman |
| `views/admin/dashboard.php` | Admin dashboard |
| `views/customer/home.php` | Customer food-ordering interface |
| `views/restaurant_dashboard/restaurant_dashboard.php` | Restaurant dashboard |
| `views/deliveryman/dashboard.php` | Deliveryman dashboard |

---

# 4. The four roles

## Admin

The Admin controls the overall CraveRush platform.

### Main responsibilities

- Manage customers
- Manage restaurants
- Manage deliverymen
- Manage food items
- Manage areas
- Manage area adjacency
- View/manage orders
- Approve or reject cancellation requests
- Approve or reject restaurant area-change requests
- Manage delivery fee
- View reports
- View revenue/profit information
- Manage admin profile
- Create/manage additional admin accounts

The admin interface is mainly located under:

```text
views/admin/
```

and handled by:

```text
controllers/admin_controller.php
models/admin_model.php
```

---

## Customer

The Customer can:

- Register and log in
- Select an area
- Browse restaurants
- Search for restaurants/food
- View restaurant menus
- Add food to cart
- Set food quantity
- Add customization/instructions
- Place an order
- Select payment method
- Track an order
- View order information
- Request cancellation where allowed
- Review restaurants
- Review deliverymen
- Update profile information
- Upload a profile picture
- Delete their account

Customer AJAX functionality is handled by:

```text
controllers/customer_ajax_controller.php
```

---

## Restaurant

The Restaurant can:

- Register and log in
- Manage its profile
- Change restaurant availability
- Manage food items
- Add food
- Edit food
- Remove/deactivate food
- Change food availability
- View incoming orders
- Process orders
- Update order status
- Request an area change
- Manage restaurant profile information
- Upload a profile picture

Restaurant functionality is handled mainly through:

```text
controllers/restaurant_controller.php
controllers/restaurant_ajax_controller.php
models/restaurant_model.php
```

---

## Deliveryman

The Deliveryman can:

- Register and log in
- View assigned orders
- Claim/handle available orders
- Update delivery status
- View current delivery
- View delivery history
- View earnings
- Update profile
- Change vehicle information
- Change availability/online status
- Upload a profile picture
- Delete their account

Relevant files include:

```text
controllers/deliveryman_controller.php
models/deliveryman_model.php
views/deliveryman/
```

---

# 5. How the four roles connect

The basic business flow is:

```text
Customer
   │
   │ selects food
   ↓
Restaurant
   │
   │ prepares order
   ↓
Deliveryman
   │
   │ delivers order
   ↓
Customer
   │
   ├── Restaurant Review
   └── Deliveryman Review
```

### Example order flow

1. Customer selects a restaurant.
2. Customer adds food to the cart.
3. Customer places the order.
4. Restaurant receives the order.
5. Restaurant processes/prepares the order.
6. Deliveryman handles the delivery.
7. Delivery status changes as the order progresses.
8. Customer receives the order.
9. Customer can review the restaurant and deliveryman.

---

# 6. Database

The project uses MySQL/MariaDB through procedural `mysqli`.

Database name:

```text
CRAVERUSH_DB
```

The included `database.sql` creates **17 tables**.

### Main tables

| # | Table | Purpose |
| --- | --- | --- |
| 1 | `Area` | Delivery/service areas |
| 2 | `Area_Adjacency` | Relationships between nearby areas |
| 3 | `Admin` | Admin accounts |
| 4 | `Customer` | Customer accounts |
| 5 | `Restaurant` | Restaurant accounts |
| 6 | `Deliveryman` | Deliveryman accounts |
| 7 | `Food_Item` | Restaurant food catalogue |
| 8 | `Cart` | Customer active carts |
| 9 | `Cart_Item` | Items inside carts |
| 10 | `Order` | Customer orders |
| 11 | `Order_Item` | Food purchased in each order |
| 12 | `Delivery` | Delivery assignment/status |
| 13 | `Cancellation_Request` | Customer cancellation requests |
| 14 | `Restaurant_Review` | Customer restaurant reviews |
| 15 | `Deliveryman_Review` | Customer deliveryman reviews |
| 16 | `Area_Change_Request` | Restaurant area-change requests |
| 17 | `Settings` | Configurable site settings such as delivery fee |

---

# 7. Important database design decisions

### Customer deletion

The `Order.Customer_ID` field uses:

```text
ON DELETE SET NULL
```

This allows historical orders to remain even if the customer account is deleted.

### Deliveryman deletion

The `Delivery.Deliveryman_ID` field also uses:

```text
ON DELETE SET NULL
```

Therefore, an existing delivery record can remain after the associated deliveryman account is removed.

### Order history

`Order_Item` stores:

- Food ID
- Food name at purchase
- Price at purchase
- Quantity
- Customization

This preserves the purchase information even if the food item's current name or price changes later.

### Reviews

Restaurant and deliveryman reviews are linked to the relevant order and customer.

---

# 8. Sample data

The included `database.sql` contains Bangladesh-flavoured sample data.

Examples of areas include:

```text
Dhanmondi
Gulshan
Banani
Mirpur
Uttara
Mohammadpur
Bashundhara
Motijheel
Badda
Farmgate
```

The database also contains sample:

- Admin accounts
- Customers
- Restaurants
- Deliverymen
- Food items
- Carts
- Cart items
- Orders
- Order items
- Deliveries
- Cancellation requests
- Restaurant reviews
- Deliveryman reviews
- Area-change requests
- Settings

The sample orders include different states such as:

```text
Delivered
Preparing
On the Way
Cancelled
```

This makes the database useful for demonstrating different dashboard scenarios.

---

# 9. Test accounts

The supplied `database.sql` contains the following demo credentials.

| Role | Email / Username | Password |
| --- | --- | --- |
| Admin | `admin@craverush.com` / `admin` | `admin` |
| Admin | `nusrat@craverush.com` / `admin_nusrat` | `nusrat123` |
| Customer | `tanvir@example.com` | `customer123` |
| Restaurant | `dhanmondidelight@craverush.com` / `r_dhanmondi` | `restaurant123` |
| Deliveryman | `sumon@craverush.com` | `deliveryman123` |

Additional demo accounts are included in the SQL file.

### Password storage

Customer, Restaurant and Deliveryman passwords in the sample data use PHP-compatible bcrypt hashes and are checked with:

```php
password_verify()
```

The seeded Admin passwords are stored as plain text and the current login controller compares the submitted password directly.

---

# 10. Food ordering and pricing

An order contains:

```text
Food Subtotal
+ Delivery Fee
= Total Amount
```

The database stores these values separately:

```text
Food_Subtotal
Delivery_Fee
Total_Amount
```

Payment methods supported by the sample database include:

```text
Cash
Card
Mobile Banking
```

Payment status can be:

```text
Paid
Pending
```

---

# 11. Delivery system

Each delivery is connected to an order.

The `Delivery` table stores:

```text
Delivery_ID
Order_ID
Deliveryman_ID
Delivery_Status
```

Example delivery statuses used by the project include:

```text
Preparing
Out for Delivery
Delivered
Cancelled
```

A deliveryman can have an online status and an availability status.

Example values:

```text
Online / Offline
Available / Busy
```

---

# 12. Area system

Every Customer, Restaurant and Deliveryman belongs to an `Area`.

The project also contains `Area_Adjacency`, which stores relationships between nearby areas.

Example:

```text
Dhanmondi <-> Mohammadpur
Gulshan <-> Banani
Gulshan <-> Badda
Mirpur <-> Uttara
Bashundhara <-> Badda
```

Restaurants can submit an area-change request, which can then be approved or rejected by an Admin.

---

# 13. AJAX / dynamic features

The project uses AJAX controllers for dynamic operations.

Relevant files:

```text
controllers/ajax_controller.php
controllers/customer_ajax_controller.php
controllers/restaurant_ajax_controller.php
```

Customer-side AJAX functionality includes operations such as:

- Restaurant loading
- Searching
- Creating orders
- Tracking orders
- Submitting reviews

Responses are returned as JSON where appropriate.

---

# 14. Validation

The project performs server-side validation before inserting/updating important data.

Examples include:

- Required-field validation
- Email validation
- Bangladesh phone-number validation
- Area validation
- Password length requirements
- Password confirmation
- Restaurant username validation
- Vehicle-type validation
- Account-type validation

Registration supports:

```text
Customer
Restaurant
Deliveryman
```

Admin accounts are not created through the public registration form.

---

# 15. Security-related implementation

The project includes several basic security practices.

| Area | Implementation |
| --- | --- |
| SQL queries | Prepared statements are used throughout the models |
| Passwords | `password_hash()` for customer/restaurant/deliveryman registration |
| Login | `password_verify()` for hashed passwords |
| Output | `htmlspecialchars()` is used in views |
| Input | `cleanInput()` is used for basic input cleaning |
| File uploads | MIME type and 2 MB size checks |
| Session | PHP sessions are used for authentication |
| Role separation | Each dashboard checks the logged-in role |

### Important note

This is a teaching/project application rather than a production-ready food-delivery platform. Security features should be reviewed and strengthened before deploying it publicly.

---

# 16. Profile image system

The project supports profile-picture uploads.

Supported image types:

```text
JPEG
PNG
WebP
```

Maximum file size:

```text
2 MB
```

Uploaded files are stored under:

```text
assets/uploads/
```

The helper function:

```php
handleProfileImageUpload()
```

handles validation and storage.

The project also contains fallback image handling through:

```php
resolveImagePath()
```

This prevents a missing/stale image path from automatically producing a broken image.

---

# 17. Configurable settings

The current database stores site-wide settings in:

```text
Settings
```

The included sample data contains:

```text
delivery_fee = 50
```

The Admin can update the fixed delivery fee from the Reports/settings area.

---

# 18. Main technologies

| Technology | Usage |
| --- | --- |
| PHP | Backend/application logic |
| MySQL / MariaDB | Database |
| MySQLi | Database connection and queries |
| HTML5 | Page structure |
| CSS3 | Styling |
| JavaScript | Client-side interactions |
| AJAX / JSON | Dynamic requests |
| XAMPP | Local development environment |

No framework or Composer dependency is required.

---

# 19. Recommended project workflow

For development:

```text
1. Start Apache + MySQL
2. Make sure CRAVERUSH_DB exists
3. Open the project through localhost
4. Login/register
5. Test the required role
6. Check the relevant controller
7. Check the model for database operations
8. Check the view for UI changes
```

For database changes:

```text
database.sql
     ↓
phpMyAdmin
     ↓
CRAVERUSH_DB
     ↓
PHP Models
     ↓
Controllers
     ↓
Views / AJAX
```

---

## Copyright

Copyright (c) 2026 Md. Ashiq Bin Hamid | MD. KHAIRUL ALAM | Zerin Hoque | Mashfia Huda Mim. All rights reserved.
