<?php
header('Content-Type: application/json');

// Include the database connection file
require_once 'db-connect.php'; // Ensure the path is correct

try {
    // Begin transaction
    $pdo->beginTransaction();

    // Example: Update the team name based on the player's name
    $playerName = 'Player Name'; // Replace with the actual player's name
    $newTeam = 'New Team Name'; // Replace with the actual new team name
    $stmt = $pdo->prepare('UPDATE profiles SET team = ? WHERE name = ?');

    if ($stmt->execute([$newTeam, $playerName])) {
        if ($stmt->rowCount() > 0) {
            // Commit transaction
            $pdo->commit();

            // Success response
            echo json_encode([
                'status' => 'success',
                'message' => "Player '$playerName' updated to team '$newTeam' successfully.",
            ]);
        } else {
            // No rows affected, which means the player name was not found
            throw new Exception("No player found with the name '$playerName'.");
        }
    } else {
        throw new Exception("Failed to execute the update statement.");
    }
} catch (Exception $e) {
    // Rollback in case of error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Log the error message
    error_log('Error updating teams: ' . $e->getMessage());

    // Respond with error details
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred: ' . $e->getMessage(),
        'details' => $e->getTraceAsString(), // Optional: include the stack trace for more details
    ]);
}
