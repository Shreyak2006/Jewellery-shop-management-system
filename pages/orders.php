<!DOCTYPE html>
<html>

<head>

    <title>Orders</title>

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

    <h1>Order Management</h1>

    <hr>

    <form method="post" action="">

        <label>Order ID</label><br><br>
<input type="text" name="order_id"><br><br>

<label>Customer ID</label><br><br>
<input type="text" name="customer_id"><br><br>

<label>Ornament Name</label><br><br>
<input type="text" name="ornament_name"><br><br>

<label>Weight</label><br><br>
<input type="text" name="weight"><br><br>

<button type="submit" name="submit">
    Add Order
</button>

    </form>
    <?php

include("../config.php");

if(isset($_POST['submit']))
{
    $order_id = $_POST['order_id'];
    $customer_id = $_POST['customer_id'];
    $ornament_name = $_POST['ornament_name'];
    $weight = $_POST['weight'];

    $query = "INSERT INTO orders
    VALUES('$order_id',
    '$customer_id',
    NULL,
    '$ornament_name',
    CURDATE(),
    '$weight',
    'Pending')";

    try {
        mysqli_query($conn, $query);
        echo "<p class='message success'>✓ Order Added Successfully</p>";
    } catch (mysqli_sql_exception $e) {
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            echo "<p class='message error'>⚠ Error: An order with ID '$order_id' already exists!</p>";
        } else if (strpos($e->getMessage(), 'a foreign key constraint fails') !== false) {
            echo "<p class='message error'>⚠ Error: Customer ID '$customer_id' does not exist!</p>";
        } else {
            echo "<p class='message error'>⚠ Error: " . $e->getMessage() . "</p>";
        }
    }
}

?>

</div>

</body>
</html>
