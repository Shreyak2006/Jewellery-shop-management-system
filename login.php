<?php
session_start();
include("config.php");

$error = "";

if(isset($_POST['login']))
{
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT * FROM admin WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) == 1)
    {
        $_SESSION['admin'] = $username;
        header("Location: index.php");
        exit();
    }
    else
    {
        $error = "Invalid Username or Password";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>

    <style>
        body{
            margin:0;
            font-family:Arial,sans-serif;
            background:#111;
            display:flex;
            justify-content:center;
            align-items:center;
            height:100vh;
        }

        .login-box{
            background:white;
            padding:40px;
            width:350px;
            border-radius:10px;
            box-shadow:0 0 20px rgba(0,0,0,0.4);
        }

        h2{
            text-align:center;
            color:#caa52e;
        }

        input{
            width:100%;
            padding:12px;
            margin-top:10px;
            margin-bottom:15px;
            border:1px solid #ccc;
            border-radius:5px;
        }

        button{
            width:100%;
            padding:12px;
            background:#caa52e;
            border:none;
            color:white;
            font-size:16px;
            cursor:pointer;
        }

        .error{
            color:red;
            text-align:center;
            margin-bottom:10px;
        }
    </style>
</head>

body{
    margin:0;
    padding:0;
    height:100vh;

    background:url('img/jewellery-bg1.jpg');
    background-size:cover;
    background-position:center;
    background-repeat:no-repeat;

    display:flex;
    justify-content:center;
    align-items:center;

    font-family:Arial, sans-serif;
}
</html>
