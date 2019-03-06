
<?php
$servername = "localhost";
$username = "root";
$password = "244616678@2018";
$dbname = "golf";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
//get gift
mysqli_query($conn,"UPDATE gift_list SET stock='1' WHERE max_size='1'");
mysqli_query($conn,"UPDATE gift_list SET stock='10' WHERE max_size='10'");
mysqli_query($conn,"UPDATE gift_list SET stock='100' WHERE max_size='100'");
mysqli_query($conn,"UPDATE gift_list SET stock='1000' WHERE max_size='1000'");
mysqli_query($conn,"UPDATE gift_list SET stock='100000' WHERE max_size='1001'");
$conn->close();
?>
