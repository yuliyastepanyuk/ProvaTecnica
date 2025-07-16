<?php
// connessione.php
$serverName = "db"; // nome del servizio MySQL nel docker-compose.yml
$databaseName = getenv('MYSQL_DATABASE');
$username = getenv('MYSQL_USER');
$password = getenv('MYSQL_PASSWORD');

try {
    $conn = new PDO("mysql:host=$serverName;dbname=$databaseName", $username, $password); // libreria php data objects
    // imposta la modalità degli errori di PDO su eccezioni
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // accedo al metodo e imposto le costanti per lanciare le eccezioni
    echo "Connected successfully<br>";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "<br>";

    error_log("Errore di connessione al DB: " . $e->getMessage(), 3, "istruzioni_errori.log");
    exit(); // termina lo script se la connessione fallisce
}
?>
