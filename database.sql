-- ================================================================
-- CRAVERUSH - FOOD DELIVERY MANAGEMENT SYSTEM - DATABASE
--
-- HOW TO USE (every teammate runs this the same way):
--   phpMyAdmin -> SQL tab -> paste this whole file -> Go
--   (or: mysql -u root -p < database.sql)
--
-- This file DROPS the existing CRAVERUSH_DB (if any) and recreates
-- it from scratch, so everyone who runs it ends up with the exact
-- same schema + sample data, no matter what was in their local DB
-- before. Safe to re-run any time you want a clean slate.
-- ================================================================

DROP DATABASE IF EXISTS CRAVERUSH_DB;

CREATE DATABASE CRAVERUSH_DB
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE CRAVERUSH_DB;


-- =========================================================
-- 1. AREA
--     Delivery zones. Every Customer, Restaurant and
--     Deliveryman belongs to exactly one Area.
-- =========================================================

CREATE TABLE Area (
    Area_ID   INT PRIMARY KEY AUTO_INCREMENT,
    Area_Name VARCHAR(100) NOT NULL
) ENGINE=InnoDB;


-- =========================================================
-- 2. AREA_ADJACENCY
--     Which areas border which. Used to widen the search
--     for available restaurants/deliverymen near a customer.
-- =========================================================

CREATE TABLE Area_Adjacency (
    Area_ID_1 INT NOT NULL,
    Area_ID_2 INT NOT NULL,

    PRIMARY KEY (Area_ID_1, Area_ID_2),

    CONSTRAINT FK_AreaAdjacency_Area1
        FOREIGN KEY (Area_ID_1)
        REFERENCES Area(Area_ID)
        ON DELETE CASCADE,

    CONSTRAINT FK_AreaAdjacency_Area2
        FOREIGN KEY (Area_ID_2)
        REFERENCES Area(Area_ID)
        ON DELETE CASCADE
) ENGINE=InnoDB;


-- =========================================================
-- 3. ADMIN
--     Platform staff. Separate table from Customer/Restaurant/
--     Deliveryman since an admin does not place or fulfil orders.
--     NOTE: Password is stored (and checked) as PLAIN TEXT by
--     design (see login_controller.php), so admins can be
--     inserted manually here without needing password_hash().
-- =========================================================

CREATE TABLE Admin (
    Admin_ID            INT PRIMARY KEY AUTO_INCREMENT,
    Name                VARCHAR(100) NOT NULL,
    Email               VARCHAR(150) NOT NULL UNIQUE,
    Username            VARCHAR(50)  NOT NULL UNIQUE,
    Password            VARCHAR(255) NOT NULL,   -- PLAIN TEXT (matches login_controller.php's check)
    Profile_Image_Path  VARCHAR(255) NULL        -- path to the Admin's profile photo
) ENGINE=InnoDB;


-- =========================================================
-- 4. CUSTOMER
-- =========================================================

CREATE TABLE Customer (
    Customer_ID  INT PRIMARY KEY AUTO_INCREMENT,
    Name         VARCHAR(100) NOT NULL,
    Phone_Number VARCHAR(20)  NOT NULL,
    Email        VARCHAR(150) NOT NULL UNIQUE,
    Password     VARCHAR(255) NOT NULL,     -- stored as a password_hash()
    Area_ID      INT NOT NULL,
    Profile_Image_Path VARCHAR(255) NULL,   -- path to the Customer's profile photo

    CONSTRAINT FK_Customer_Area
        FOREIGN KEY (Area_ID)
        REFERENCES Area(Area_ID)
) ENGINE=InnoDB;


-- =========================================================
-- 5. RESTAURANT
-- =========================================================

CREATE TABLE Restaurant (
    Restaurant_ID        INT PRIMARY KEY AUTO_INCREMENT,
    Name                 VARCHAR(150) NOT NULL,
    Phone_Number         VARCHAR(20)  NOT NULL,
    Email                VARCHAR(150) NOT NULL UNIQUE,
    Username              VARCHAR(50)  NOT NULL UNIQUE,
    Password             VARCHAR(255) NOT NULL,   -- stored as a password_hash()
    Area_ID              INT NOT NULL,
    Availability_Status  VARCHAR(20)  NOT NULL,   -- 'Open' / 'Closed'
    Profile_Image_Path   VARCHAR(255) NULL,       -- path to the Restaurant's profile photo

    CONSTRAINT FK_Restaurant_Area
        FOREIGN KEY (Area_ID)
        REFERENCES Area(Area_ID)
) ENGINE=InnoDB;


-- =========================================================
-- 6. DELIVERYMAN
-- =========================================================

CREATE TABLE Deliveryman (
    Deliveryman_ID       INT PRIMARY KEY AUTO_INCREMENT,
    Name                 VARCHAR(100) NOT NULL,
    Phone_Number         VARCHAR(20)  NOT NULL,
    Email                VARCHAR(150) NOT NULL UNIQUE,
    Password             VARCHAR(255) NOT NULL,   -- stored as a password_hash()
    Vehicle_Type         VARCHAR(50)  NOT NULL,   -- 'Bike' / 'Bicycle' / 'Car'
    Area_ID              INT NOT NULL,
    Online_Status        VARCHAR(20)  NOT NULL,   -- 'Online' / 'Offline'
    Availability_Status  VARCHAR(20)  NOT NULL,   -- 'Available' / 'Busy'
    Profile_Image_Path   VARCHAR(255) NULL,       -- path to the Deliveryman's profile photo

    CONSTRAINT FK_Deliveryman_Area
        FOREIGN KEY (Area_ID)
        REFERENCES Area(Area_ID)
) ENGINE=InnoDB;


-- =========================================================
-- 7. FOOD_ITEM
--     Catalogue owned by a Restaurant.
--     NOTE: when Image_Path is NULL/empty, the app falls back
--     to the bundled assets/images/food<N>.jpg set (see
--     models/food_model.php + customer_controller.php), so
--     sample rows are seeded with NULL rather than a made-up
--     path that doesn't exist on disk.
-- =========================================================

CREATE TABLE Food_Item (
    Food_ID              INT PRIMARY KEY AUTO_INCREMENT,
    Restaurant_ID        INT NOT NULL,
    Name                 VARCHAR(150) NOT NULL,
    Description          TEXT,
    Price                DECIMAL(10,2) NOT NULL,
    Category             VARCHAR(100) NOT NULL,
    Availability_Status  VARCHAR(20)  NOT NULL,   -- 'Available' / 'Unavailable'
    Image_Path           VARCHAR(255) NULL,       -- path to the Food Item's photo

    CONSTRAINT FK_FoodItem_Restaurant
        FOREIGN KEY (Restaurant_ID)
        REFERENCES Restaurant(Restaurant_ID)
        ON DELETE CASCADE
) ENGINE=InnoDB;


-- =========================================================
-- 8. CART
--     One active cart per customer.
-- =========================================================

CREATE TABLE Cart (
    Cart_ID     INT PRIMARY KEY AUTO_INCREMENT,
    Customer_ID INT NOT NULL UNIQUE,

    CONSTRAINT FK_Cart_Customer
        FOREIGN KEY (Customer_ID)
        REFERENCES Customer(Customer_ID)
        ON DELETE CASCADE
) ENGINE=InnoDB;


-- =========================================================
-- 9. CART_ITEM
-- =========================================================

CREATE TABLE Cart_Item (
    Cart_Item_ID   INT PRIMARY KEY AUTO_INCREMENT,
    Cart_ID        INT NOT NULL,
    Food_ID        INT NOT NULL,
    Quantity       INT NOT NULL,
    Customization  TEXT,

    CONSTRAINT FK_CartItem_Cart
        FOREIGN KEY (Cart_ID)
        REFERENCES Cart(Cart_ID)
        ON DELETE CASCADE,

    CONSTRAINT FK_CartItem_Food
        FOREIGN KEY (Food_ID)
        REFERENCES Food_Item(Food_ID)
        ON DELETE CASCADE
) ENGINE=InnoDB;


-- =========================================================
-- 10. ORDER
--     NOTE: Customer_ID is nullable. If the Customer is deleted,
--     the Order is NOT deleted -- Customer_ID is set to NULL instead
--     (ON DELETE SET NULL), so the Order's Food, Restaurant and
--     pricing history survive.
-- =========================================================

CREATE TABLE `Order` (
    Order_ID          INT PRIMARY KEY AUTO_INCREMENT,
    Customer_ID       INT NULL,
    Restaurant_ID     INT NOT NULL,
    Order_Date        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    Delivery_Area_ID  INT NOT NULL,
    Food_Subtotal     DECIMAL(10,2) NOT NULL,
    Delivery_Fee      DECIMAL(10,2) NOT NULL,
    Total_Amount      DECIMAL(10,2) NOT NULL,
    Payment_Method    VARCHAR(30) NOT NULL,   -- 'Cash' / 'Card' / 'Mobile Banking'
    Payment_Status    VARCHAR(20) NOT NULL,   -- 'Paid' / 'Pending'
    Order_Status      VARCHAR(30) NOT NULL,   -- 'Preparing' / 'On the Way' / 'Delivered' / 'Cancelled'
    Note              VARCHAR(500) NULL,      -- optional special instructions from the Customer

    CONSTRAINT FK_Order_Customer
        FOREIGN KEY (Customer_ID)
        REFERENCES Customer(Customer_ID)
        ON DELETE SET NULL,

    CONSTRAINT FK_Order_Restaurant
        FOREIGN KEY (Restaurant_ID)
        REFERENCES Restaurant(Restaurant_ID)
        ON DELETE CASCADE,

    CONSTRAINT FK_Order_Area
        FOREIGN KEY (Delivery_Area_ID)
        REFERENCES Area(Area_ID)
) ENGINE=InnoDB;


-- =========================================================
-- 11. ORDER_ITEM
--     Snapshot of Food name/price at the moment of purchase,
--     so later price changes never rewrite order history.
-- =========================================================

CREATE TABLE Order_Item (
    Order_Item_ID           INT PRIMARY KEY AUTO_INCREMENT,
    Order_ID                INT NOT NULL,
    Food_ID                 INT NULL,
    Food_Name_At_Purchase   VARCHAR(150) NOT NULL,
    Price_At_Purchase       DECIMAL(10,2) NOT NULL,
    Quantity                INT NOT NULL,
    Customization            TEXT,

    CONSTRAINT FK_OrderItem_Order
        FOREIGN KEY (Order_ID)
        REFERENCES `Order`(Order_ID)
        ON DELETE CASCADE,

    CONSTRAINT FK_OrderItem_Food
        FOREIGN KEY (Food_ID)
        REFERENCES Food_Item(Food_ID)
        ON DELETE SET NULL
) ENGINE=InnoDB;


-- =========================================================
-- 12. DELIVERY
--     NOTE: Deliveryman_ID is nullable. If the Deliveryman is
--     deleted, the Delivery record is NOT deleted -- Deliveryman_ID
--     is set to NULL instead (ON DELETE SET NULL), so the Delivery
--     and its linked Order survive.
-- =========================================================

CREATE TABLE Delivery (
    Delivery_ID       INT PRIMARY KEY AUTO_INCREMENT,
    Order_ID          INT NOT NULL UNIQUE,
    Deliveryman_ID    INT NULL,
    Delivery_Status   VARCHAR(30) NOT NULL,   -- 'Preparing' / 'Out for Delivery' / 'Delivered' / 'Cancelled'

    CONSTRAINT FK_Delivery_Order
        FOREIGN KEY (Order_ID)
        REFERENCES `Order`(Order_ID)
        ON DELETE CASCADE,

    CONSTRAINT FK_Delivery_Deliveryman
        FOREIGN KEY (Deliveryman_ID)
        REFERENCES Deliveryman(Deliveryman_ID)
        ON DELETE SET NULL
) ENGINE=InnoDB;


-- =========================================================
-- 13. CANCELLATION_REQUEST
-- =========================================================

CREATE TABLE Cancellation_Request (
    Cancellation_ID  INT PRIMARY KEY AUTO_INCREMENT,
    Order_ID         INT NOT NULL UNIQUE,
    Customer_ID      INT NOT NULL,
    Reason           TEXT NOT NULL,
    Status           VARCHAR(20) NOT NULL,   -- 'Pending' / 'Approved' / 'Rejected'

    CONSTRAINT FK_Cancellation_Order
        FOREIGN KEY (Order_ID)
        REFERENCES `Order`(Order_ID)
        ON DELETE CASCADE,

    CONSTRAINT FK_Cancellation_Customer
        FOREIGN KEY (Customer_ID)
        REFERENCES Customer(Customer_ID)
        ON DELETE CASCADE
) ENGINE=InnoDB;


-- =========================================================
-- 14. RESTAURANT_REVIEW
--     A Customer's review of the Restaurant for a Delivered
--     Order. At most one per Order (Order_ID is UNIQUE).
-- =========================================================

CREATE TABLE Restaurant_Review (
    Restaurant_Review_ID  INT PRIMARY KEY AUTO_INCREMENT,
    Order_ID               INT NOT NULL UNIQUE,
    Customer_ID            INT NOT NULL,
    Restaurant_ID          INT NOT NULL,
    Rating                 INT NOT NULL,
    Comment                TEXT,
    Review_Date            DATE NOT NULL,

    CONSTRAINT FK_RestaurantReview_Order
        FOREIGN KEY (Order_ID)
        REFERENCES `Order`(Order_ID)
        ON DELETE CASCADE,

    CONSTRAINT FK_RestaurantReview_Customer
        FOREIGN KEY (Customer_ID)
        REFERENCES Customer(Customer_ID)
        ON DELETE CASCADE,

    CONSTRAINT FK_RestaurantReview_Restaurant
        FOREIGN KEY (Restaurant_ID)
        REFERENCES Restaurant(Restaurant_ID)
        ON DELETE CASCADE
) ENGINE=InnoDB;


-- =========================================================
-- 15. DELIVERYMAN_REVIEW
--     A Customer's review of the Deliveryman for a Delivered
--     Order. At most one per Order (Order_ID is UNIQUE).
--     Unlike Delivery.Deliveryman_ID, this FK is NOT nullable:
--     a review with no identifiable Deliveryman has no
--     meaningful subject, so it is hard-deleted along with
--     the Deliveryman instead of being preserved.
-- =========================================================

CREATE TABLE Deliveryman_Review (
    Deliveryman_Review_ID  INT PRIMARY KEY AUTO_INCREMENT,
    Order_ID                 INT NOT NULL UNIQUE,
    Customer_ID              INT NOT NULL,
    Deliveryman_ID           INT NOT NULL,
    Rating                   INT NOT NULL,
    Comment                  TEXT,
    Review_Date              DATE NOT NULL,

    CONSTRAINT FK_DeliverymanReview_Order
        FOREIGN KEY (Order_ID)
        REFERENCES `Order`(Order_ID)
        ON DELETE CASCADE,

    CONSTRAINT FK_DeliverymanReview_Customer
        FOREIGN KEY (Customer_ID)
        REFERENCES Customer(Customer_ID)
        ON DELETE CASCADE,

    CONSTRAINT FK_DeliverymanReview_Deliveryman
        FOREIGN KEY (Deliveryman_ID)
        REFERENCES Deliveryman(Deliveryman_ID)
        ON DELETE CASCADE
) ENGINE=InnoDB;


-- =========================================================
-- 16. AREA_CHANGE_REQUEST
--     A restaurant asking the admin to move it to a different Area.
-- =========================================================

CREATE TABLE Area_Change_Request (
    Request_ID          INT PRIMARY KEY AUTO_INCREMENT,
    Restaurant_ID        INT NOT NULL,
    Current_Area_ID      INT NOT NULL,
    Requested_Area_ID   INT NOT NULL,
    Request_Status       VARCHAR(20) NOT NULL,   -- 'Pending' / 'Approved' / 'Rejected'

    CONSTRAINT FK_AreaChange_Restaurant
        FOREIGN KEY (Restaurant_ID)
        REFERENCES Restaurant(Restaurant_ID)
        ON DELETE CASCADE,

    CONSTRAINT FK_AreaChange_CurrentArea
        FOREIGN KEY (Current_Area_ID)
        REFERENCES Area(Area_ID),

    CONSTRAINT FK_AreaChange_RequestedArea
        FOREIGN KEY (Requested_Area_ID)
        REFERENCES Area(Area_ID)

) ENGINE=InnoDB;


-- =========================================================
-- 17. SETTINGS
--     Single key/value store for site-wide configurable
--     values. Currently used for the fixed Delivery Fee,
--     which the Admin can update from the Reports page.
-- =========================================================

CREATE TABLE Settings (
    Setting_Key   VARCHAR(50) PRIMARY KEY,
    Setting_Value VARCHAR(255) NOT NULL
) ENGINE=InnoDB;


-- ================================================================
-- SAMPLE DATA (Bangladesh-flavoured)
--     Inserted strictly in FK order: parents before children.
--
--     LOGIN CREDENTIALS FOR EVERY SEEDED ACCOUNT
--     -------------------------------------------------
--     Admin         : admin@craverush.com   (user "admin")   / admin
--     Admin (2nd)   : nusrat@craverush.com  (user "admin_nusrat") / nusrat123
--     Any Customer  : <see emails below>    / customer123
--     Any Restaurant: <see emails below>    / restaurant123
--     Any Deliveryman: <see emails below>   / deliveryman123
--
--     The Customer/Restaurant/Deliveryman passwords below are REAL
--     bcrypt hashes (PHP-compatible $2b$, cost 10) of the plaintext
--     passwords above, so password_verify() actually succeeds --
--     the old sample data used fake placeholder strings like
--     '$2y$10$hashcust2' that were not valid bcrypt hashes at all,
--     so none of those demo accounts could ever log in.
-- ================================================================

-- ---- 1. AREA ----
INSERT INTO Area (Area_Name) VALUES
('Dhanmondi'), ('Gulshan'), ('Banani'), ('Mirpur'), ('Uttara'),
('Mohammadpur'), ('Bashundhara'), ('Motijheel'), ('Badda'), ('Farmgate');

-- ---- 2. AREA_ADJACENCY ----
INSERT INTO Area_Adjacency (Area_ID_1, Area_ID_2) VALUES
(1,6),(6,1),    -- Dhanmondi <-> Mohammadpur
(1,10),(10,1),  -- Dhanmondi <-> Farmgate
(2,3),(3,2),    -- Gulshan <-> Banani
(2,9),(9,2),    -- Gulshan <-> Badda
(3,7),(7,3),    -- Banani <-> Bashundhara
(4,6),(6,4),    -- Mirpur <-> Mohammadpur
(4,5),(5,4),    -- Mirpur <-> Uttara
(7,9),(9,7),    -- Bashundhara <-> Badda
(8,10),(10,8);  -- Motijheel <-> Farmgate

-- ---- 3. ADMIN (Password column is plain text by design) ----
INSERT INTO Admin (Name, Email, Username, Password) VALUES
('Admin',        'admin@craverush.com',  'admin',        'admin'),
('Nusrat Jahan', 'nusrat@craverush.com', 'admin_nusrat', 'nusrat123');

-- ---- 4. CUSTOMER (password for all: customer123) ----
INSERT INTO Customer (Name, Phone_Number, Email, Password, Area_ID) VALUES
('Tanvir Ahmed',       '01711000001', 'tanvir@example.com',   '$2b$10$Dz17AEwx9Hbs8N9SAMJl0OI5fwKbDLGUmQ.EygzTfPCFtjbXK3z6e', 1),
('Mehjabin Chowdhury', '01711000002', 'mehjabin@example.com', '$2b$10$Dz17AEwx9Hbs8N9SAMJl0OI5fwKbDLGUmQ.EygzTfPCFtjbXK3z6e', 2),
('Shafiul Bari',       '01711000003', 'shafiul@example.com',  '$2b$10$Dz17AEwx9Hbs8N9SAMJl0OI5fwKbDLGUmQ.EygzTfPCFtjbXK3z6e', 3),
('Farzana Akter',      '01711000004', 'farzana@example.com',  '$2b$10$Dz17AEwx9Hbs8N9SAMJl0OI5fwKbDLGUmQ.EygzTfPCFtjbXK3z6e', 4),
('Imran Kabir',        '01711000005', 'imran@example.com',    '$2b$10$Dz17AEwx9Hbs8N9SAMJl0OI5fwKbDLGUmQ.EygzTfPCFtjbXK3z6e', 5),
('Ruma Sultana',       '01711000006', 'ruma@example.com',     '$2b$10$Dz17AEwx9Hbs8N9SAMJl0OI5fwKbDLGUmQ.EygzTfPCFtjbXK3z6e', 6),
('Nafisa Islam',       '01711000007', 'nafisa@example.com',   '$2b$10$Dz17AEwx9Hbs8N9SAMJl0OI5fwKbDLGUmQ.EygzTfPCFtjbXK3z6e', 7),
('Kamrul Hasan',       '01711000008', 'kamrul@example.com',   '$2b$10$Dz17AEwx9Hbs8N9SAMJl0OI5fwKbDLGUmQ.EygzTfPCFtjbXK3z6e', 9);

-- ---- 5. RESTAURANT (password for all: restaurant123) ----
INSERT INTO Restaurant (Name, Phone_Number, Email, Username, Password, Area_ID, Availability_Status) VALUES
('Dhanmondi Delight',        '02911000001', 'dhanmondidelight@craverush.com',   'r_dhanmondi',   '$2b$10$JogxB8UrdH74AtHEOwB/1eTXNYid96Hmjd3T4N7pveT9zavRaFZFC', 1, 'Open'),
('Gulshan Grill House',      '02911000002', 'gulshangrill@craverush.com',       'r_gulshan',     '$2b$10$JogxB8UrdH74AtHEOwB/1eTXNYid96Hmjd3T4N7pveT9zavRaFZFC', 2, 'Open'),
('Banani Bites',             '02911000003', 'bananibites@craverush.com',        'r_banani',      '$2b$10$JogxB8UrdH74AtHEOwB/1eTXNYid96Hmjd3T4N7pveT9zavRaFZFC', 3, 'Closed'),
('Mirpur Masala',            '02911000004', 'mirpurmasala@craverush.com',       'r_mirpur',      '$2b$10$JogxB8UrdH74AtHEOwB/1eTXNYid96Hmjd3T4N7pveT9zavRaFZFC', 4, 'Open'),
('Uttara Urban Kitchen',     '02911000005', 'uttaraurban@craverush.com',        'r_uttara',      '$2b$10$JogxB8UrdH74AtHEOwB/1eTXNYid96Hmjd3T4N7pveT9zavRaFZFC', 5, 'Open'),
('Bashundhara Biryani House','02911000006', 'bashundharabiryani@craverush.com', 'r_bashundhara', '$2b$10$JogxB8UrdH74AtHEOwB/1eTXNYid96Hmjd3T4N7pveT9zavRaFZFC', 7, 'Open');

-- ---- 6. DELIVERYMAN (password for all: deliveryman123) ----
-- Profile_Image_Path left NULL: unlike Restaurant, this column IS
-- rendered (views/deliveryman/profile.php), but with no fallback
-- image logic -- a non-existent path shows a broken image icon.
-- NULL is handled cleanly (the <img> tag is simply skipped).
INSERT INTO Deliveryman (Name, Phone_Number, Email, Password, Vehicle_Type, Area_ID, Online_Status, Availability_Status, Profile_Image_Path) VALUES
('Sumon Mia',      '01611000001', 'sumon@craverush.com',    '$2b$10$j/3Zo1/pSNZO9z7w05G66OU7GZ2gC6XtLU1/0eYwhntC7TtSGb3Gu', 'Bike',    1, 'Online',  'Available', NULL),
('Jahid Hasan',    '01611000002', 'jahid@craverush.com',    '$2b$10$j/3Zo1/pSNZO9z7w05G66OU7GZ2gC6XtLU1/0eYwhntC7TtSGb3Gu', 'Bicycle', 2, 'Online',  'Busy',      NULL),
('Rakibul Islam',  '01611000003', 'rakibul@craverush.com',  '$2b$10$j/3Zo1/pSNZO9z7w05G66OU7GZ2gC6XtLU1/0eYwhntC7TtSGb3Gu', 'Car',     3, 'Offline', 'Available', NULL),
('Anisur Rahman',  '01611000004', 'anisur@craverush.com',   '$2b$10$j/3Zo1/pSNZO9z7w05G66OU7GZ2gC6XtLU1/0eYwhntC7TtSGb3Gu', 'Bike',    4, 'Online',  'Available', NULL),
('Delwar Hossain', '01611000005', 'delwar@craverush.com',   '$2b$10$j/3Zo1/pSNZO9z7w05G66OU7GZ2gC6XtLU1/0eYwhntC7TtSGb3Gu', 'Bike',    5, 'Offline', 'Available', NULL),
('Mostafa Kamal',  '01611000006', 'mostafa@craverush.com',  '$2b$10$j/3Zo1/pSNZO9z7w05G66OU7GZ2gC6XtLU1/0eYwhntC7TtSGb3Gu', 'Bicycle', 7, 'Online',  'Available', NULL);

-- ---- 7. FOOD_ITEM ----
-- Image_Path left NULL on purpose -- see the CREATE TABLE note
-- above: NULL/empty lets the app fall back to its bundled
-- assets/images/food<N>.jpg images, which actually exist on disk.
INSERT INTO Food_Item (Restaurant_ID, Name, Description, Price, Category, Availability_Status, Image_Path) VALUES
(1, 'Chicken Biryani',          'Fragrant rice with tender chicken',           250.00, 'Rice',      'Available',   NULL),
(1, 'Beef Tehari',              'Spiced beef rice',                            220.00, 'Rice',      'Available',   NULL),
(1, 'Mango Lassi',              'Chilled yogurt mango drink',                   80.00, 'Beverage',  'Available',   NULL),
(2, 'Grilled Chicken Platter',  'Charcoal-grilled chicken with sides',         450.00, 'Grill',     'Available',   NULL),
(2, 'BBQ Wings',                'Smoky barbecue wings',                        350.00, 'Grill',     'Available',   NULL),
(2, 'Caesar Salad',             'Fresh romaine with dressing',                 280.00, 'Salad',     'Available',   NULL),
(3, 'Beef Burger',              'Juicy beef patty burger',                     320.00, 'Fast Food', 'Unavailable', NULL),
(3, 'French Fries',             'Crispy golden fries',                         150.00, 'Fast Food', 'Unavailable', NULL),
(3, 'Chicken Shawarma',         'Rolled flatbread with grilled chicken',       300.00, 'Fast Food', 'Available',   NULL),
(4, 'Kacchi Biryani',           'Slow-cooked mutton biryani',                  300.00, 'Rice',      'Available',   NULL),
(4, 'Chicken Roast',            'Traditional festive chicken roast',           280.00, 'Curry',     'Available',   NULL),
(4, 'Firni',                    'Sweet rice pudding',                           90.00, 'Dessert',   'Available',   NULL),
(5, 'Margherita Pizza',         'Classic cheese and tomato pizza',             400.00, 'Pizza',     'Available',   NULL),
(5, 'Pasta Alfredo',            'Creamy white sauce pasta',                    380.00, 'Pasta',     'Available',   NULL),
(5, 'Chocolate Brownie',        'Rich fudgy brownie',                          150.00, 'Dessert',   'Available',   NULL),
(6, 'Morog Polao',              'Bangladeshi chicken pilaf with fragrant rice',320.00, 'Rice',      'Available',   NULL),
(6, 'Beef Rezala',              'Mild, creamy beef curry',                     350.00, 'Curry',     'Available',   NULL),
(6, 'Borhani',                  'Spiced savoury yogurt drink',                  60.00, 'Beverage',  'Available',   NULL);

-- ---- 8. CART ----
INSERT INTO Cart (Customer_ID) VALUES (1), (2), (4);

-- ---- 9. CART_ITEM ----
INSERT INTO Cart_Item (Cart_ID, Food_ID, Quantity, Customization) VALUES
(1, 1,  2, 'Extra spicy'),
(1, 3,  1, ''),
(2, 4,  1, 'No onions'),
(3, 10, 3, '');

-- ---- 10. ORDER ----
-- Row 6 has Customer_ID = NULL: simulates a customer account that was
-- later deleted, while the order history is preserved (ON DELETE SET NULL).
INSERT INTO `Order`
(Customer_ID, Restaurant_ID, Delivery_Area_ID, Food_Subtotal, Delivery_Fee, Total_Amount, Payment_Method, Payment_Status, Order_Status, Note) VALUES
(1,    1, 1, 500.00, 40.00, 540.00, 'Cash',           'Paid',    'Delivered',   'Extra spicy, please'),
(2,    2, 2, 450.00, 50.00, 500.00, 'Card',           'Paid',    'On the Way',  NULL),
(3,    3, 3, 320.00, 40.00, 360.00, 'Mobile Banking', 'Paid',    'Cancelled',   NULL),
(4,    4, 4, 300.00, 30.00, 330.00, 'Cash',           'Pending', 'Preparing',   'Leave at the gate'),
(5,    5, 5, 400.00, 45.00, 445.00, 'Card',           'Paid',    'Delivered',   NULL),
(NULL, 1, 6, 250.00, 60.00, 310.00, 'Cash',           'Paid',    'Delivered',   NULL),
(7,    6, 7, 320.00, 35.00, 355.00, 'Mobile Banking', 'Paid',    'Delivered',   NULL),
(8,    2, 9, 450.00, 55.00, 505.00, 'Card',           'Pending', 'Preparing',   'Call upon arrival');

-- ---- 11. ORDER_ITEM ----
INSERT INTO Order_Item (Order_ID, Food_ID, Food_Name_At_Purchase, Price_At_Purchase, Quantity, Customization) VALUES
(1, 1,  'Chicken Biryani',         250.00, 2, 'Extra spicy'),
(2, 4,  'Grilled Chicken Platter', 450.00, 1, 'No onions'),
(3, 7,  'Beef Burger',             320.00, 1, ''),
(4, 10, 'Kacchi Biryani',          300.00, 1, ''),
(5, 13, 'Margherita Pizza',        400.00, 1, ''),
(6, 1,  'Chicken Biryani',         250.00, 1, ''),
(7, 16, 'Morog Polao',             320.00, 1, ''),
(8, 4,  'Grilled Chicken Platter', 450.00, 1, 'Call upon arrival');

-- ---- 12. DELIVERY ----
-- Row 3 has Deliveryman_ID = NULL: order was cancelled before assignment.
-- Row 6 has Deliveryman_ID = NULL: simulates a deliveryman account later
-- deleted, while the delivery record is preserved (ON DELETE SET NULL).
INSERT INTO Delivery (Order_ID, Deliveryman_ID, Delivery_Status) VALUES
(1, 1,    'Delivered'),
(2, 2,    'Out for Delivery'),
(3, NULL, 'Cancelled'),
(4, 4,    'Preparing'),
(5, 5,    'Delivered'),
(6, NULL, 'Delivered'),
(7, 6,    'Delivered'),
(8, 2,    'Preparing');

-- ---- 13. CANCELLATION_REQUEST ----
INSERT INTO Cancellation_Request (Order_ID, Customer_ID, Reason, Status) VALUES
(3, 3, 'Changed my mind about the order.', 'Approved');

-- ---- 14. RESTAURANT_REVIEW ----
-- (Only Delivered orders that still have a Customer_ID can be reviewed --
-- Order 6's customer was deleted, so it has no reviewer.)
INSERT INTO Restaurant_Review (Order_ID, Customer_ID, Restaurant_ID, Rating, Comment, Review_Date) VALUES
(1, 1, 1, 5, 'Amazing biryani, will order again!',           '2026-08-20'),
(5, 5, 5, 4, 'Good pizza but delivery was a bit late.',      '2026-08-25'),
(7, 7, 6, 5, 'Loved the Morog Polao, very authentic taste.', '2026-08-27');

-- ---- 15. DELIVERYMAN_REVIEW ----
INSERT INTO Deliveryman_Review (Order_ID, Customer_ID, Deliveryman_ID, Rating, Comment, Review_Date) VALUES
(1, 1, 1, 5, 'Fast and courteous delivery.',        '2026-08-20'),
(5, 5, 5, 5, 'Very polite, arrived quickly.',        '2026-08-25'),
(7, 7, 6, 4, 'Delivered on time, friendly rider.',   '2026-08-27');

-- ---- 16. AREA_CHANGE_REQUEST ----
INSERT INTO Area_Change_Request (Restaurant_ID, Current_Area_ID, Requested_Area_ID, Request_Status) VALUES
(3, 3, 2, 'Pending');

-- ---- 17. SETTINGS ----
INSERT INTO Settings (Setting_Key, Setting_Value) VALUES
('delivery_fee', '50');
