<?php
$host = "localhost";
$username = "u312704806_aimnxt";
$password = "N^w2nzy]";
$database = "u312704806_aimnxt";
$connection = mysqli_connect($host, $username, $password, $database);

$query = "UPDATE views_count SET views=views+1";
mysqli_query($connection, $query);

$totalviewssql = "SELECT * FROM views_count";
$runtotalviewsquery = mysqli_query($connection, $totalviewssql);

$totalViews = 0;
while ($viewscount = $runtotalviewsquery->fetch_assoc()) {
   $totalViews = $viewscount['views']; 
}

mysqli_close($connection);

header('Content-Type: application/json');
echo json_encode(['totalViews' => $totalViews]);
?>