CREATE DATABASE IF NOT EXISTS CRAVERUSH_DB
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE CRAVERUSH_DB;


CREATE TABLE IF NOT EXISTS Area (
    Area_ID   INT PRIMARY KEY AUTO_INCREMENT,
    Area_Name VARCHAR(100) NOT NULL
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS Area_Adjacency (
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


CREATE TABLE IF NOT EXISTS Admin (
    Admin_ID INT PRIMARY KEY AUTO_INCREMENT,
    Name     VARCHAR(100) NOT NULL,
    Email    VARCHAR(150) NOT NULL UNIQUE,
    Username VARCHAR(50)  NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS Customer (
    Customer_ID  INT PRIMARY KEY AUTO_INCREMENT,
    Name         VARCHAR(100) NOT NULL,
    Phone_Number VARCHAR(20)  NOT NULL,
    Email        VARCHAR(150) NOT NULL UNIQUE,
    Password     VARCHAR(255) NOT NULL,
    Area_ID      INT NOT NULL,

    CONSTRAINT FK_Customer_Area
        FOREIGN KEY (Area_ID)
        REFERENCES Area(Area_ID)
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS Restaurant (
    Restaurant_ID        INT PRIMARY KEY AUTO_INCREMENT,
    Name                 VARCHAR(150) NOT NULL,
    Phone_Number         VARCHAR(20)  NOT NULL,
    Email                VARCHAR(150) NOT NULL UNIQUE,
    Username             VARCHAR(50)  NOT NULL UNIQUE,
    Password             VARCHAR(255) NOT NULL,
    Area_ID              INT NOT NULL,
    Availability_Status  VARCHAR(20)  NOT NULL,

    CONSTRAINT FK_Restaurant_Area
        FOREIGN KEY (Area_ID)
        REFERENCES Area(Area_ID)
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS Deliveryman (
    Deliveryman_ID       INT PRIMARY KEY AUTO_INCREMENT,
    Name                 VARCHAR(100) NOT NULL,
    Phone_Number         VARCHAR(20)  NOT NULL,
    Email                VARCHAR(150) NOT NULL UNIQUE,
    Password             VARCHAR(255) NOT NULL,
    Vehicle_Type         VARCHAR(50)  NOT NULL,
    Area_ID              INT NOT NULL,
    Online_Status        VARCHAR(20)  NOT NULL,
    Availability_Status  VARCHAR(20)  NOT NULL,

    CONSTRAINT FK_Deliveryman_Area
        FOREIGN KEY (Area_ID)
        REFERENCES Area(Area_ID)
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS Food_Item (
    Food_ID              INT PRIMARY KEY AUTO_INCREMENT,
    Restaurant_ID        INT NOT NULL,
    Name                 VARCHAR(150) NOT NULL,
    Description          TEXT,
    Price                DECIMAL(10,2) NOT NULL,
    Category             VARCHAR(100) NOT NULL,
    Availability_Status  VARCHAR(20)  NOT NULL,

    CONSTRAINT FK_FoodItem_Restaurant
        FOREIGN KEY (Restaurant_ID)
        REFERENCES Restaurant(Restaurant_ID)
        ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS Cart (
    Cart_ID     INT PRIMARY KEY AUTO_INCREMENT,
    Customer_ID INT NOT NULL UNIQUE,

    CONSTRAINT FK_Cart_Customer
        FOREIGN KEY (Customer_ID)
        REFERENCES Customer(Customer_ID)
        ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS Cart_Item (
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


CREATE TABLE IF NOT EXISTS `Order` (
    Order_ID          INT PRIMARY KEY AUTO_INCREMENT,
    Customer_ID       INT NULL,
    Restaurant_ID     INT NOT NULL,
    Order_Date        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    Delivery_Area_ID  INT NOT NULL,
    Food_Subtotal     DECIMAL(10,2) NOT NULL,
    Delivery_Fee      DECIMAL(10,2) NOT NULL,
    Total_Amount      DECIMAL(10,2) NOT NULL,
    Payment_Method    VARCHAR(30) NOT NULL,
    Payment_Status    VARCHAR(20) NOT NULL,
    Order_Status      VARCHAR(30) NOT NULL,

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


CREATE TABLE IF NOT EXISTS Order_Item (
    Order_Item_ID          INT PRIMARY KEY AUTO_INCREMENT,
    Order_ID               INT NOT NULL,
    Food_ID                INT NOT NULL,
    Food_Name_At_Purchase  VARCHAR(150) NOT NULL,
    Price_At_Purchase      DECIMAL(10,2) NOT NULL,
    Quantity               INT NOT NULL,
    Customization          TEXT,

    CONSTRAINT FK_OrderItem_Order
        FOREIGN KEY (Order_ID)
        REFERENCES `Order`(Order_ID)
        ON DELETE CASCADE,

    CONSTRAINT FK_OrderItem_Food
        FOREIGN KEY (Food_ID)
        REFERENCES Food_Item(Food_ID)
        ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS Delivery (
    Delivery_ID       INT PRIMARY KEY AUTO_INCREMENT,
    Order_ID          INT NOT NULL UNIQUE,
    Deliveryman_ID    INT NULL,
    Delivery_Status   VARCHAR(30) NOT NULL,

    CONSTRAINT FK_Delivery_Order
        FOREIGN KEY (Order_ID)
        REFERENCES `Order`(Order_ID)
        ON DELETE CASCADE,

    CONSTRAINT FK_Delivery_Deliveryman
        FOREIGN KEY (Deliveryman_ID)
        REFERENCES Deliveryman(Deliveryman_ID)
        ON DELETE SET NULL
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS Cancellation_Request (
    Cancellation_ID  INT PRIMARY KEY AUTO_INCREMENT,
    Order_ID         INT NOT NULL UNIQUE,
    Customer_ID      INT NOT NULL,
    Reason           TEXT NOT NULL,
    Status           VARCHAR(20) NOT NULL,

    CONSTRAINT FK_Cancellation_Order
        FOREIGN KEY (Order_ID)
        REFERENCES `Order`(Order_ID)
        ON DELETE CASCADE,

    CONSTRAINT FK_Cancellation_Customer
        FOREIGN KEY (Customer_ID)
        REFERENCES Customer(Customer_ID)
        ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS Review (
    Review_ID       INT PRIMARY KEY AUTO_INCREMENT,
    Order_ID        INT NOT NULL UNIQUE,
    Customer_ID     INT NOT NULL,
    Restaurant_ID   INT NOT NULL,
    Rating          INT NOT NULL,
    Comment         TEXT,
    Review_Date     DATE NOT NULL,

    CONSTRAINT FK_Review_Order
        FOREIGN KEY (Order_ID)
        REFERENCES `Order`(Order_ID)
        ON DELETE CASCADE,

    CONSTRAINT FK_Review_Customer
        FOREIGN KEY (Customer_ID)
        REFERENCES Customer(Customer_ID)
        ON DELETE CASCADE,

    CONSTRAINT FK_Review_Restaurant
        FOREIGN KEY (Restaurant_ID)
        REFERENCES Restaurant(Restaurant_ID)
        ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS Area_Change_Request (
    Request_ID          INT PRIMARY KEY AUTO_INCREMENT,
    Restaurant_ID       INT NOT NULL,
    Current_Area_ID     INT NOT NULL,
    Requested_Area_ID   INT NOT NULL,
    Request_Status      VARCHAR(20) NOT NULL,

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


INSERT INTO Area (Area_Name) VALUES
('Dhanmondi'), ('Gulshan'), ('Banani'), ('Mirpur'), ('Uttara'), ('Mohammadpur');


INSERT INTO Area_Adjacency (Area_ID_1, Area_ID_2) VALUES
(1,6),(6,1),
(2,3),(3,2),
(4,5),(5,4);


INSERT INTO Admin (Name, Email, Username, Password) VALUES
('Admin',        'admin@craverush.com',  'admin',        'admin'),
('Nusrat Jahan', 'nusrat@craverush.com', 'admin_nusrat', '$2y$10$hash2abcdefghijklmno');


INSERT INTO Customer (Name, Phone_Number, Email, Password, Area_ID) VALUES
('Customer',          '01711000001', 'customer@example.com', 'customer',         1),
('Mehjabin Chowdhury','01711000002', 'mehjabin@example.com', '$2y$10$hashcust2', 2),
('Shafiul Bari',      '01711000003', 'shafiul@example.com',  '$2y$10$hashcust3', 3),
('Farzana Akter',     '01711000004', 'farzana@example.com',  '$2y$10$hashcust4', 4),
('Imran Kabir',       '01711000005', 'imran@example.com',    '$2y$10$hashcust5', 5),
('Ruma Sultana',      '01711000006', 'ruma@example.com',     '$2y$10$hashcust6', 6);


INSERT INTO Restaurant (Name, Phone_Number, Email, Username, Password, Area_ID, Availability_Status) VALUES
('Dhanmondi Delight',    '02911000001', 'dhanmondidelight@craverush.com', 'r_dhanmondi', '$2y$10$hashr1', 1, 'Open'),
('Gulshan Grill House',  '02911000002', 'gulshangrill@craverush.com',     'r_gulshan',   '$2y$10$hashr2', 2, 'Open'),
('Banani Bites',         '02911000003', 'bananibites@craverush.com',      'r_banani',    '$2y$10$hashr3', 3, 'Closed'),
('Mirpur Masala',        '02911000004', 'mirpurmasala@craverush.com',     'r_mirpur',    '$2y$10$hashr4', 4, 'Open'),
('Uttara Urban Kitchen', '02911000005', 'uttaraurban@craverush.com',      'r_uttara',    '$2y$10$hashr5', 5, 'Open');


INSERT INTO Deliveryman (Name, Phone_Number, Email, Password, Vehicle_Type, Area_ID, Online_Status, Availability_Status) VALUES
('Deliveryman',  '01611000001', 'deliveryman@craverush.com', 'deliveryman',    'Motorcycle', 1, 'Online',  'Available'),
('Sumon Mia',    '01611000002', 'sumon@craverush.com',       '$2y$10$hashd2',  'Bicycle',    2, 'Online',  'Busy'),
('Jahid Hasan',  '01611000003', 'jahid@craverush.com',       '$2y$10$hashd3',  'Motorcycle', 3, 'Offline', 'Available'),
('Rakibul Islam','01611000004', 'rakibul@craverush.com',     '$2y$10$hashd4',  'Car',        4, 'Online',  'Available'),
('Anisur Rahman','01611000005', 'anisur@craverush.com',      '$2y$10$hashd5',  'Motorcycle', 5, 'Offline', 'Available');


INSERT INTO Food_Item (Restaurant_ID, Name, Description, Price, Category, Availability_Status) VALUES
(1, 'Chicken Biryani',         'Fragrant rice with tender chicken',   250.00, 'Rice',      'Available'),
(1, 'Beef Tehari',             'Spiced beef rice',                    220.00, 'Rice',      'Available'),
(1, 'Mango Lassi',             'Chilled yogurt mango drink',           80.00, 'Beverage',  'Available'),
(2, 'Grilled Chicken Platter', 'Charcoal-grilled chicken with sides', 450.00, 'Grill',     'Available'),
(2, 'BBQ Wings',               'Smoky barbecue wings',                350.00, 'Grill',     'Available'),
(2, 'Caesar Salad',            'Fresh romaine with dressing',         280.00, 'Salad',     'Available'),
(3, 'Beef Burger',             'Juicy beef patty burger',             320.00, 'Fast Food', 'Unavailable'),
(3, 'French Fries',            'Crispy golden fries',                 150.00, 'Fast Food', 'Unavailable'),
(4, 'Kacchi Biryani',          'Slow-cooked mutton biryani',          300.00, 'Rice',      'Available'),
(4, 'Chicken Roast',           'Traditional festive chicken roast',   280.00, 'Curry',     'Available'),
(4, 'Firni',                   'Sweet rice pudding',                   90.00, 'Dessert',   'Available'),
(5, 'Margherita Pizza',        'Classic cheese and tomato pizza',     400.00, 'Pizza',     'Available'),
(5, 'Pasta Alfredo',           'Creamy white sauce pasta',            380.00, 'Pasta',     'Available'),
(5, 'Chocolate Brownie',       'Rich fudgy brownie',                  150.00, 'Dessert',   'Available');


INSERT INTO Cart (Customer_ID) VALUES (1), (2), (4);


INSERT INTO Cart_Item (Cart_ID, Food_ID, Quantity, Customization) VALUES
(1, 1, 2, 'Extra spicy'),
(1, 3, 1, ''),
(2, 4, 1, 'No onions'),
(3, 9, 3, '');


INSERT INTO `Order`
(Customer_ID, Restaurant_ID, Delivery_Area_ID, Food_Subtotal, Delivery_Fee, Total_Amount, Payment_Method, Payment_Status, Order_Status) VALUES
(1,    1, 1, 500.00, 40.00, 540.00, 'Cash on Delivery', 'Paid',    'Delivered'),
(2,    2, 2, 450.00, 50.00, 500.00, 'Card',             'Paid',    'On The Way'),
(3,    3, 3, 320.00, 40.00, 360.00, 'Mobile Banking',   'Paid',    'Cancelled'),
(4,    4, 4, 300.00, 30.00, 330.00, 'Cash on Delivery', 'Pending', 'Preparing'),
(5,    5, 5, 400.00, 45.00, 445.00, 'Card',             'Paid',    'Delivered'),
(1, 1, 6, 250.00, 60.00, 310.00, 'Cash on Delivery', 'Paid',    'Delivered');


INSERT INTO Order_Item (Order_ID, Food_ID, Food_Name_At_Purchase, Price_At_Purchase, Quantity, Customization) VALUES
(1, 1,  'Chicken Biryani',         250.00, 2, 'Extra spicy'),
(2, 4,  'Grilled Chicken Platter', 450.00, 1, 'No onions'),
(3, 7,  'Beef Burger',             320.00, 1, ''),
(4, 9,  'Kacchi Biryani',          300.00, 1, ''),
(5, 12, 'Margherita Pizza',        400.00, 1, ''),
(6, 1,  'Chicken Biryani',         250.00, 1, '');


INSERT INTO Delivery (Order_ID, Deliveryman_ID, Delivery_Status) VALUES
(1, 1,    'Delivered'),
(2, 2,    'On The Way'),
(3, NULL, 'Cancelled'),
(4, 4,    'Preparing'),
(5, 5,    'Delivered'),
(6, NULL, 'Delivered');


INSERT INTO Cancellation_Request (Order_ID, Customer_ID, Reason, Status) VALUES
(3, 3, 'Changed my mind about the order.', 'Approved');


INSERT INTO Review (Order_ID, Customer_ID, Restaurant_ID, Rating, Comment, Review_Date) VALUES
(1, 1, 1, 5, 'Amazing biryani, will order again!',      '2026-08-20'),
(5, 5, 5, 4, 'Good pizza but delivery was a bit late.', '2026-08-25');


INSERT INTO Area_Change_Request (Restaurant_ID, Current_Area_ID, Requested_Area_ID, Request_Status) VALUES
(3, 3, 2, 'Pending');
