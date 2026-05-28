-- =========================================
-- JEWELLERY SHOP MANAGEMENT SYSTEM
-- SQL QUERIES
-- =========================================


-- =========================================
-- CREATE TABLE QUERIES
-- =========================================

CREATE TABLE customers (
    customer_id VARCHAR(50) PRIMARY KEY,
    customer_name VARCHAR(100),
    phone VARCHAR(20),
    address TEXT
);

CREATE TABLE goldsmiths (
    goldsmith_id VARCHAR(50) PRIMARY KEY,
    goldsmith_name VARCHAR(100),
    phone VARCHAR(20)
);

CREATE TABLE orders (
    order_id VARCHAR(50) PRIMARY KEY,
    customer_id VARCHAR(50),
    goldsmith_id VARCHAR(50),
    ornament_name VARCHAR(100),
    order_date DATE,
    weight VARCHAR(50),
    status VARCHAR(50),

    FOREIGN KEY (customer_id)
    REFERENCES customers(customer_id),

    FOREIGN KEY (goldsmith_id)
    REFERENCES goldsmiths(goldsmith_id)
);

CREATE TABLE payments (
    payment_id VARCHAR(50) PRIMARY KEY,
    order_id VARCHAR(50),
    amount DECIMAL(10,2),

    FOREIGN KEY (order_id)
    REFERENCES orders(order_id)
);

CREATE TABLE deliveries (
    delivery_id VARCHAR(50) PRIMARY KEY,
    order_id VARCHAR(50),
    delivery_status VARCHAR(50),

    FOREIGN KEY (order_id)
    REFERENCES orders(order_id)
);


-- =========================================
-- INSERT QUERIES
-- =========================================

INSERT INTO customers
VALUES ('C101', 'Shreya', '9876543210', 'Udupi');

INSERT INTO goldsmiths
VALUES ('G101', 'Ramesh', '9998887776');

INSERT INTO orders
VALUES (
'O101',
'C101',
'G101',
'Gold Ring',
'2026-05-28',
'10g',
'Pending'
);

INSERT INTO payments
VALUES ('P101', 'O101', 5000);

INSERT INTO deliveries
VALUES ('D101', 'O101', 'Delivered');


-- =========================================
-- SELECT QUERIES
-- =========================================

SELECT * FROM customers;

SELECT * FROM orders;

SELECT * FROM payments;

SELECT * FROM deliveries;

SELECT * FROM goldsmiths;


-- =========================================
-- UPDATE QUERY
-- =========================================

UPDATE orders
SET status = 'Completed'
WHERE order_id = 'O101';


-- =========================================
-- DELETE QUERY
-- =========================================

DELETE FROM deliveries
WHERE delivery_id = 'D101';


-- =========================================
-- JOIN QUERY
-- =========================================

SELECT
    customers.customer_name,
    orders.ornament_name,
    payments.amount
FROM customers
JOIN orders
ON customers.customer_id = orders.customer_id
JOIN payments
ON orders.order_id = payments.order_id;


-- =========================================
-- SEARCH QUERY
-- =========================================

SELECT *
FROM customers
WHERE customer_name = 'Shreya';


-- =========================================
-- SORT QUERY
-- =========================================

SELECT *
FROM payments
ORDER BY amount DESC;