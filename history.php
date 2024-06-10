<?php
include 'db.php';

$sql = "SELECT * FROM games ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>

<head>
    <title>XO Game - History</title>
    <link rel="stylesheet" type="text/css" href="css/history.css">
</head>

<body>
    <h1>XO Game - History</h1>
    <ul class="game-list">
        <?php while ($row = $result->fetch_assoc()) : ?>
            <li>
                <?php if ($row['winner']) : ?>
                    <a href="replay.php?game_id=<?php echo $row['id']; ?>">
                        Game ID: <?php echo $row['id']; ?> - <?php echo $row['player1']; ?> vs <?php echo $row['player2']; ?> - Replay
                    </a>
                <?php else : ?>
                    <?php if ($row['player2'] === 'BOT') : ?>
                        <a href="bot.php?game_id=<?php echo $row['id']; ?>">
                            Game ID: <?php echo $row['id']; ?> - <?php echo $row['player1']; ?> vs <?php echo $row['player2']; ?> - Continue playing
                        </a>
                    <?php else : ?>
                        <a href="play.php?game_id=<?php echo $row['id']; ?>">
                            Game ID: <?php echo $row['id']; ?> - <?php echo $row['player1']; ?> vs <?php echo $row['player2']; ?> - Continue playing
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
                (<?php echo $row['created_at']; ?>)
            </li>
        <?php endwhile; ?>
    </ul>
    <a href="index.php" class="back-btn">Back to Main</a>
</body>

</html>
