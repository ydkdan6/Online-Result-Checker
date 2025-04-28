<?php
$connection = fsockopen('smtp.elasticemail.com', 587, $errno, $errstr, 10);
if (!$connection) {
    echo "Port 587 is closed. Error: $errstr ($errno)";
} else {
    echo "Port 587 is open!";
    fclose($connection);
}
?>
