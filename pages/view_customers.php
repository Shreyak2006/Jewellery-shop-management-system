<?php
include '../config.php';
include '../includes/header.php';

$query = "SELECT * FROM customers";
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
    <h2>Customer Records</h2>

    <table>
        <tr>
            <th>Customer ID</th>
            <th>Customer Name</th>
            <th>Phone</th>
            <th>Address</th>
        </tr>

        <?php
        while($row = mysqli_fetch_assoc($result))
        {
        ?>
        <tr>
            <td><?php echo $row['customer_id']; ?></td>
            <td><?php echo $row['customer_name']; ?></td>
            <td><?php echo $row['phone']; ?></td>
            <td><?php echo $row['address']; ?></td>
        </tr>
        <?php
        }
        ?>
    </table>
</div>

<?php
include '../includes/footer.php';
?>