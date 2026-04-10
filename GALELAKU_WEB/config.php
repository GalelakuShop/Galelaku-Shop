
<?php
$host = "localhost";
$dbname = "galelaku_shop";
$username = "root";
$password = "";

try {
    $dsn = "mysql:host=$host;dbname=$dbname";
    $pdo = new PDO($dsn, $username, $password);
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
}
?>
