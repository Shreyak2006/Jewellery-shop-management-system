<!DOCTYPE html>
<html>

<head>

    <title>Deliveries</title>

    <link rel="stylesheet" type="text/css"
    href="../css/style.css?v=<?php echo time(); ?>">

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

    <h1>Delivery Management</h1>

    <hr>

    <form method="POST" action="">

        <label>Delivery ID</label><br><br>
        <input type="text" name="delivery_id"><br><br>

        <label>Order ID</label><br><br>
        <input type="text" name="order_id"><br><br>

        <label>Delivery Status</label><br><br>
        <input type="text" name="delivery_status"><br><br>

        <button type="submit" name="submit">Add Delivery</button>

    </form>

    <?php

    include("../config.php");

    if (isset($_POST['submit'])) {
        $delivery_id = $_POST['delivery_id'];
        $order_id = $_POST['order_id'];
        $delivery_status = $_POST['delivery_status'];

        $query = "INSERT INTO deliveries (delivery_id, order_id, delivery_status) 
                  VALUES ('$delivery_id', '$order_id', '$delivery_status')";

        try {
            mysqli_query($conn, $query);
            echo "<p class='message success'>✓ Delivery Status Added Successfully</p>";
        } catch (mysqli_sql_exception $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                echo "<p class='message error'>⚠ Error: A delivery record with ID '$delivery_id' already exists!</p>";
            } else if (strpos($e->getMessage(), 'a foreign key constraint fails') !== false) {
                echo "<p class='message error'>⚠ Error: Order ID '$order_id' does not exist!</p>";
            } else {
                echo "<p class='message error'>⚠ Error: " . $e->getMessage() . "</p>";
            }
        }
    }

    ?>

</div>

</body>
</html>