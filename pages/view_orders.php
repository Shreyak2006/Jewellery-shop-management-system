<?php
include '../config.php';
include '../includes/header.php';

$query = "SELECT * FROM orders";
$result = mysqli_query($conn, $query);
?>

<style>
.container {
    width: 90%;
    margin: 40px auto;
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
}

h2 {
    margin-bottom: 25px;
    color: #1e293b;
    text-align: center;
}

table {
    width: 100%;
    border-collapse: collapse;
}

table th {
    background-color: #d4af37;
    color: white;
    padding: 15px;
    text-align: left;
}

table td {
    padding: 15px;
    border-bottom: 1px solid #ddd;
}

table tr:hover {
    background-color: #f5f5f5;
}
</style>

<div class="container">
    <h2>Order Records</h2>

    <table>
        <tr>
            <th>Order ID</th>
            <th>Customer ID</th>
            <th>Goldsmith ID</th>
            <th>Ornament</th>
            <th>Order Date</th>
            <th>Weight</th>
            <th>Status</th>
        </tr>

        <?php
        while($row = mysqli_fetch_assoc($result))
        {
        ?>
        <tr>
            <td><?php echo $row['order_id']; ?></td>
            <td><?php echo $row['customer_id']; ?></td>
            <td><?php echo $row['goldsmith_id']; ?></td>
            <td><?php echo $row['ornament_name']; ?></td>
            <td><?php echo $row['order_date']; ?></td>
            <td><?php echo $row['weight']; ?></td>
            <td><?php echo $row['status']; ?></td>
        </tr>
        <?php
        }
        ?>
    </table>
</div>

<?php
include '../includes/footer.php';
?>