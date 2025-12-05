<?php
// 1. Database Connection Details
$host = 'mysql-sortievaldoise.alwaysdata.net'; 
$dbname = 'sortievaldoise_database';
$username = '437124';
$password = "j'ai_mange3ratatouille&-1-pain";
$charset = 'utf8mb4';

// 2. Set up the "DSN" (Data Source Name)
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

// 3. Set PDO options for error handling and fetching
$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
];

// 4. Create the connection
try {
  $pdo = new PDO($dsn, $username, $password, $options);
} catch (\PDOException $e) {
  throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>