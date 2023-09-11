<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "test";
$code = $_POST['code_128'];

// Create connection
$conn = new mysqli($servername, $username, $password, $database);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT Name, Type, Serial_number, expiry FROM asset WHERE Serial_number = $code";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {
    echo "Name: " . $row["Name"]. " - Type: " . $row["Type"]. " Serial number: " . $row["Serial_number"]. "<br>";
  }
} else {
  echo "0 results", $code;
}
$conn->close();
?>