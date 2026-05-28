<!DOCTYPE html>
<html>

<head>

    <title>Payments</title>

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

    <h1>Payment Management</h1>

    <hr>

    <form method="POST" action="">

        <label>Payment ID</label><br><br>
        <input type="text" name="payment_id"><br><br>

        <label>Order ID</label><br><br>
        <input type="text" name="order_id"><br><br>

        <label>Amount</label><br><br>
        <input type="text" name="amount"><br><br>

        <button type="submit" name="submit">Add Payment</button>

    </form>

    <?php

    include("../config.php");

    if (isset($_POST['submit'])) {
        $payment_id = $_POST['payment_id'];
        $order_id = $_POST['order_id'];
        $amount = $_POST['amount'];

        $query = "INSERT INTO payments (payment_id, order_id, amount) 
                  VALUES ('$payment_id', '$order_id', '$amount')";

        try {
            mysqli_query($conn, $query);
            echo "<p class='message success'>✓ Payment Added Successfully</p>";
        } catch (mysqli_sql_exception $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                echo "<p class='message error'>⚠ Error: A payment record with ID '$payment_id' already exists!</p>";
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