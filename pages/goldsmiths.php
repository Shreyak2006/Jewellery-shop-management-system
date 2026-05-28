<!DOCTYPE html>
<html>

<head>

    <title>Goldsmiths</title>

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

    <h1>Goldsmith Details</h1>

    <hr>

    <form method="POST" action="">

        <label>Goldsmith ID</label><br><br>
        <input type="text" name="goldsmith_id"><br><br>

        <label>Goldsmith Name</label><br><br>
        <input type="text" name="goldsmith_name"><br><br>

        <label>Phone Number</label><br><br>
        <input type="text" name="phone"><br><br>

        <button type="submit" name="submit">Add Goldsmith</button>

    </form>

    <?php

    include("../config.php");

    if (isset($_POST['submit'])) {
        $goldsmith_id = $_POST['goldsmith_id'];
        $goldsmith_name = $_POST['goldsmith_name'];
        $phone = $_POST['phone'];

        $query = "INSERT INTO goldsmiths (goldsmith_id, goldsmith_name, phone) 
                  VALUES ('$goldsmith_id', '$goldsmith_name', '$phone')";

        try {
            mysqli_query($conn, $query);
            echo "<p class='message success'>✓ Goldsmith Added Successfully</p>";
        } catch (mysqli_sql_exception $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                echo "<p class='message error'>⚠ Error: A goldsmith with ID '$goldsmith_id' already exists!</p>";
            } else {
                echo "<p class='message error'>⚠ Error: " . $e->getMessage() . "</p>";
            }
        }
    }

    ?>

</div>

</body>
</html>