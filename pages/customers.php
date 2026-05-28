<!DOCTYPE html>
<html>

<head>
    <title>Customers</title>

    <link rel="stylesheet" type="text/css" href="../css/style.css?v=<?php echo time(); ?>">
</head>

<body>

    <div class="navbar">

        <a href="../index.php">Home</a>

        <a href="customers.php">Customers</a>

        <a href="orders.php">Orders</a>

        <a href="goldsmiths.php">Goldsmiths</a>

        <a href="payments.php">Payments</a>

        <a href="deliveries.php">Deliveries</a>

    </div>

    <div class="container">

        <h1>Customer Management</h1>

        <hr>

        <form method="POST" action="">

            <label>Customer ID</label><br><br>
            <input type="text" name="customer_id"><br><br>

            <label>Customer Name</label><br><br>
            <input type="text" name="customer_name"><br><br>

            <label>Phone Number</label><br><br>
            <input type="text" name="phone"><br><br>

            <label>Address</label><br><br>
            <textarea name="address"></textarea><br><br>

            <button type="submit" name="submit">
                Add Customer
            </button>

        </form>

        <?php

        include("../config.php");

        if (isset($_POST['submit'])) {
            $id = $_POST['customer_id'];
            $name = $_POST['customer_name'];
            $phone = $_POST['phone'];
            $address = $_POST['address'];

            $query = "INSERT INTO customers
(customer_id, customer_name, phone, address)

VALUES

('$id', '$name', '$phone', '$address')";

            try {
                mysqli_query($conn, $query);
                echo "<p class='message success'>✓ Customer Added Successfully</p>";
            } catch (mysqli_sql_exception $e) {
                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    echo "<p class='message error'>⚠ Error: A customer with ID '$id' already exists!</p>";
                } else {
                    echo "<p class='message error'>⚠ Error: " . $e->getMessage() . "</p>";
                }
            }
        }

        ?>

    </div>

</body>

</html>