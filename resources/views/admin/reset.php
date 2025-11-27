<?php
include '../dbconfig.php';

$query = "DELETE FROM produk";
mysqli_query($conn, $query);

header("Location: dashboard.php");
?>
