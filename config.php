<?php
// Database connection settings
$host = 'localhost';
$user = 'root';             
$pass = 'mayuri@0615';      
$dbname = 'housify_db';        // use your actual database name
$port = 3307;               

try {
    // Establish connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;port=$port;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} 
catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Import the database schema if the table does not exist
$table_name = 'resident';
$table_check_sql = "SHOW TABLES LIKE '$table_name'";
$table_exists = $pdo->query($table_check_sql)->rowCount() > 0;

if (!$table_exists) 
{
    if (file_exists('database.sql')) {
        $sql = file_get_contents('database.sql');
        $pdo->exec($sql);
    }

    // Insert default admin user if not exists
    $username = 'Admin';
    $userssn = '123456789';
    $password = password_hash('admin', PASSWORD_DEFAULT);

    $user_check_sql = "SELECT 1 FROM resident WHERE ssn = :ssn LIMIT 1";
    $stmt = $pdo->prepare($user_check_sql);
    $stmt->execute(['ssn' => $userssn]);
    $user_exists = $stmt->fetch();

    if (!$user_exists) {
        // Insert a sample house
        $pdo->exec("INSERT INTO house (house_number, street_name, block_number) VALUES ('101', 'Green Street', 'A')");
        $house_id = $pdo->lastInsertId();

        // Insert admin user
        $insert_sql = "INSERT INTO resident (name, ssn, house_id, password, role) VALUES (:name, :ssn, :house_id, :password, 'admin')";
        $stmt = $pdo->prepare($insert_sql);
        $stmt->execute([
            'name' => $username,
            'ssn' => $userssn,
            'house_id' => $house_id,
            'password' => $password
        ]);

        echo "✅ Setup complete. Please log in using:<br>";
        echo "Username: Admin<br>Password: admin<br>";
    }
}

date_default_timezone_set("Asia/Kolkata");
session_start();
?>
