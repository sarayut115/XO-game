<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $size = intval($_POST["size"]);
    $player1 = $_POST["player1"];
    $player2 = "BOT"; // กำหนดชื่อของบอท

    $sql = "INSERT INTO games (size, player1, player2) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $size, $player1, $player2);
    $stmt->execute();

    $game_id = $stmt->insert_id;
    header("Location: bot.php?game_id=" . $game_id); // นำไปยังหน้า bot.php แทน play.php
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>1Player with Bot</title>
    <link rel="stylesheet" type="text/css" href="css/selectBot.css">
</head>
<body>
    <h1>CHOOSE A GRID</h1>
    <form method="post" action="">
        <label for="size">Board Size:</label>
        <input type="number" id="size" name="size" min="3" required><br>
        <label for="player1">Name Player 1 (X):</label>
        <input type="text" id="player1" name="player1" required><br>
        <!-- ไม่ต้องให้ผู้เล่นเลือกชื่อบอท -->
        <!-- <label for="player2">Player 2 (O):</label>
        <input type="text" id="player2" name="player2" required><br> -->
        <input type="submit" value="Start Game">
    </form>
</body>
</html>
