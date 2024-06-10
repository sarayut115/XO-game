<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $size = intval($_POST["size"]);
    $player1 = $_POST["player1"];
    $player2 = $_POST["player2"];

    $sql = "INSERT INTO games (size, player1, player2) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $size, $player1, $player2);
    $stmt->execute();

    $game_id = $stmt->insert_id;
    header("Location: play.php?game_id=" . $game_id);
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>2Player</title>
    <link rel="stylesheet" type="text/css" href="css/select2Player.css">
</head>
<body>
    <h1>CHOOSE A GRID</h1>
    <form method="post" action="">
        <label for="size">Board Size:</label>
        <input type="number" id="size" name="size" min="3" required><br>
        <label for="player1">Name Player 1 (X):</label>
        <input type="text" id="player1" name="player1" required><br>
        <label for="player2">Name Player 2 (O):</label>
        <input type="text" id="player2" name="player2" required><br>
        <input type="submit" value="Start Game">
    </form>
</body>
</html>
