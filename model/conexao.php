
<?php

// Conexão com o banco de dados
$host = "localhost";
$user = "root";
$pass = "BacoeRa@";
$dbname = "mercado";
$conn = pg_connect("host={$host} user={$user} password={$pass} dbname={$dbname}");
