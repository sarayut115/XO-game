<?php
include 'db.php';

$game_id = intval($_GET["game_id"]);

// ดึงข้อมูลเกม
$sql_game = "SELECT * FROM games WHERE id = ?";
$stmt_game = $conn->prepare($sql_game);
$stmt_game->bind_param("i", $game_id);
$stmt_game->execute();
$game_result = $stmt_game->get_result();
$game = $game_result->fetch_assoc();

// ดึงประวัติการเล่นเกม
$sql_history = "SELECT * FROM game_history WHERE game_id = ? ORDER BY move_time ASC";
$stmt_history = $conn->prepare($sql_history);
$stmt_history->bind_param("i", $game_id);
$stmt_history->execute();
$history_result = $stmt_history->get_result();
$history = $history_result->fetch_all(MYSQLI_ASSOC);

// ตรวจสอบผลการแข่งขัน
if ($game["winner"] !== "Tie") {
    $result_message = "Winner: " . $game["winner"];
} elseif ($game["winner"] === "Tie") {
    $result_message = "Draw Game!!!";
} else {
    $result_message = "No Result";
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Replay Game</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-image: linear-gradient(to top, #2980b9, #6dd5fa, #ffffff);
        }

        .container {
            background-color: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            text-align: center;
        }

        h1,
        h2 {
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        td {
            border-bottom: 1px solid #ddd;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #ddd;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Replay Game</h1>
        <h2><?php echo $result_message; ?></h2>
        <table>
            <tr>
                <th>Player</th>
                <th>Row</th>
                <th>Column</th>
                <th>Move Time</th>
            </tr>
            <?php foreach ($history as $move) : ?>
                <tr>
                    <td><?php echo $move['player']; ?></td>
                    <td><?php echo $move['move_row']; ?></td>
                    <td><?php echo $move['move_col']; ?></td>
                    <td><?php echo $move['move_time']; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>

</html>