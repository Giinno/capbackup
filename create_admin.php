<?php
// Database connection
include 'db-connect.php';

// Admin details
$users = [
    [
        'firstname' => 'Admin',
        'lastname' => 'One',
        'username' => '1admin',
        'age' => 30,
        'email' => '1admin@example.com',
        'position' => 'Point Guard',
        'team' => 'Admin Team',
        'phone' => '123-456-7890',
        'password' => '1admin',
        'role' => 'Statistics-admin'
    ],
    [
        'firstname' => 'Admin',
        'lastname' => 'Two',
        'username' => '2admin',
        'age' => 30,
        'email' => '2admin@example.com',
        'position' => 'Point Guard',
        'team' => 'Admin Team',
        'phone' => '123-456-7891',
        'password' => '2admin',
        'role' => 'Scheduling-admin'
    ]
];

foreach ($users as $user) {
    $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);
    $firstname = $user['firstname'];
    $lastname = $user['lastname'];
    $username = $user['username'];
    $age = $user['age'];
    $email = $user['email'];
    $position = $user['position'];
    $team = $user['team'];
    $phone = $user['phone'];
    $role = $user['role'];

    // SQL query
    $sql = "INSERT INTO users (firstname, lastname, username, age, email, position, team, phone, password, role) 
            VALUES ('$firstname', '$lastname', '$username', $age, '$email', '$position', '$team', '$phone', '$hashedPassword', '$role')";
    
    // Execute the query
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully for user: $username\n";
    } else {
        echo "Error: " . $sql . "\n" . $conn->error;
    }
}

// Close the database connection
$conn->close();
?>
