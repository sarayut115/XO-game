<?php
include 'db.php';

$game_id = intval($_GET["game_id"]);

$sql = "SELECT * FROM games WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $game_id);
$stmt->execute();
$game = $stmt->get_result()->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $player = $_POST["player"];
    $row = intval($_POST["move_row"]);
    $col = intval($_POST["move_col"]);

    // ตรวจสอบว่าช่องนี้ถูกเล่นแล้วหรือยัง
    $sql_check_move = "SELECT * FROM moves WHERE game_id = ? AND move_row = ? AND move_col = ?";
    $stmt_check_move = $conn->prepare($sql_check_move);
    $stmt_check_move->bind_param("iii", $game_id, $row, $col);
    $stmt_check_move->execute();
    $move_exists = $stmt_check_move->get_result()->num_rows > 0;

    if (!$move_exists && !$game["winner"]) { // เพิ่มการตรวจสอบว่าเกมได้จบลงหรือยัง
        // ทำการเพิ่มรายการใหม่
        $sql_insert_move = "INSERT INTO moves (game_id, player, move_row, move_col) VALUES (?, ?, ?, ?)";
        $stmt_insert_move = $conn->prepare($sql_insert_move);
        $stmt_insert_move->bind_param("isii", $game_id, $player, $row, $col);
        $stmt_insert_move->execute();

        // เพิ่มรายการประวัติการเล่น
        $sql_insert_history = "INSERT INTO game_history (game_id, player, move_row, move_col) VALUES (?, ?, ?, ?)";
        $stmt_insert_history = $conn->prepare($sql_insert_history);
        $stmt_insert_history->bind_param("isii", $game_id, $player, $row, $col);
        $stmt_insert_history->execute();

        // ทำการเล่นของบอท
        $sql = "SELECT * FROM moves WHERE game_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $game_id);
        $stmt->execute();
        $moves = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // สร้างกระดานเกม
        $board = array_fill(0, $game["size"], array_fill(0, $game["size"], ''));

        foreach ($moves as $move) {
            $board[$move["move_row"]][$move["move_col"]] = $move["player"] == $game["player1"] ? 'X' : 'O';
        }

        // ตรวจสอบผู้ชนะหลังจากเล่นครั้งล่าสุด
        $winner = checkWinner($board, $game["size"]);
        if ($winner && !$game["winner"]) {
            $winner_name = $winner == 'X' ? $game["player1"] : $game["player2"];
            $sql = "UPDATE games SET winner = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $winner_name, $game_id);
            $stmt->execute();
            $game["winner"] = $winner_name;
        }

        // ตรวจสอบว่ากระดานเต็มและยังไม่มีผู้ชนะ
        if (!$game["winner"] && count($moves) == $game["size"] * $game["size"]) {
            $sql = "UPDATE games SET winner = 'Tie' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $game_id);
            $stmt->execute();
            $game["winner"] = 'Tie';
        }

        // บอทเล่นเฉพาะถ้ายังไม่มีผู้ชนะ
        if (!$game["winner"]) {
            $empty_cells = [];
            for ($i = 0; $i < $game["size"]; $i++) {
                for ($j = 0; $j < $game["size"]; $j++) {
                    if ($board[$i][$j] == '') {
                        $empty_cells[] = ["row" => $i, "col" => $j];
                    }
                }
            }

            if (!empty($empty_cells)) {
                // สุ่มเลือกตำแหน่งที่ว่าง
                $bot_move = $empty_cells[array_rand($empty_cells)];
                $sql = "INSERT INTO moves (game_id, player, move_row, move_col) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isii", $game_id, $game["player2"], $bot_move["row"], $bot_move["col"]);
                $stmt->execute();

                // เพิ่มรายการประวัติการเล่นของบอท
                $sql_insert_bot_history = "INSERT INTO game_history (game_id, player, move_row, move_col) VALUES (?, ?, ?, ?)";
                $stmt_insert_bot_history = $conn->prepare($sql_insert_bot_history);
                $stmt_insert_bot_history->bind_param("isii", $game_id, $game["player2"], $bot_move["row"], $bot_move["col"]);
                $stmt_insert_bot_history->execute();
            }
        }

        // นำผู้เล่นกลับไปยังหน้าเล่นเกม
        header("Location: bot.php?game_id=$game_id");
        exit;
    }
}

// โหลดกระดานปัจจุบัน
$sql = "SELECT * FROM moves WHERE game_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $game_id);
$stmt->execute();
$moves = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// สร้างกระดานเกม
$board = array_fill(0, $game["size"], array_fill(0, $game["size"], ''));

foreach ($moves as $move) {
    $board[$move["move_row"]][$move["move_col"]] = $move["player"] == $game["player1"] ? 'X' : 'O';
}

// ตรวจสอบผู้ชนะ
function checkWinner($board, $size)
{
    // ตรวจสอบแถว
    for ($i = 0; $i < $size; $i++) {
        if ($board[$i][0] != '' && count(array_unique($board[$i])) === 1) {
            return $board[$i][0];
        }
    }

    // ตรวจสอบคอลัมน์
    for ($i = 0; $i < $size; $i++) {
        $column = array_column($board, $i);
        if ($column[0] != '' && count(array_unique($column)) === 1) {
            return $column[0];
        }
    }

    // ตรวจสอบเส้นทแยงมุม
    $diagonal1 = $diagonal2 = [];
    for ($i = 0; $i < $size; $i++) {
        $diagonal1[] = $board[$i][$i];
        $diagonal2[] = $board[$i][$size - $i - 1];
    }
    if ($diagonal1[0] != '' && count(array_unique($diagonal1)) === 1) {
        return $diagonal1[0];
    }
    if ($diagonal2[0] != '' && count(array_unique($diagonal2)) === 1) {
        return $diagonal2[0];
    }

    // เพิ่มเงื่อนไขสำหรับกระดานขนาด 4 หรือมากกว่า
    if ($size >= 4) {

        // ตรวจสอบเส้นทแยงมุมด้านขวาลง
        for ($i = 0; $i <= $size - 4; $i++) {
            for ($j = 0; $j <= $size - 4; $j++) {
                $pattern = [];
                for ($k = 0; $k < 4; $k++) {
                    $pattern[] = $board[$i + $k][$j + $k];
                }
                if (count(array_unique($pattern)) === 1 && $pattern[0] != '') {
                    return $pattern[0];
                }
            }
        }

        // ตรวจสอบเส้นทแยงมุมด้านซ้ายลง
        for ($i = 0; $i <= $size - 4; $i++) {
            for ($j = $size - 1; $j >= 3; $j--) {
                $pattern = [];
                for ($k = 0; $k < 4; $k++) {
                    $pattern[] = $board[$i + $k][$j - $k];
                }
                if (count(array_unique($pattern)) === 1 && $pattern[0] != '') {
                    return $pattern[0];
                }
            }
        }
    }

    return null;
}

$winner = checkWinner($board, $game["size"]);
if ($winner && !$game["winner"]) {
    $winner_name = $winner == 'X' ? $game["player1"] : $game["player2"];
    $sql = "UPDATE games SET winner = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $winner_name, $game_id);
    $stmt->execute();
    $game["winner"] = $winner_name;
}

// การเก็บประวัติการเล่น
if (!$move_exists && !$game["winner"]) {
    $sql = "INSERT INTO game_history (game_id, player, move_row, move_col) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isii", $game_id, $player, $row, $col);
    $stmt->execute();
}

// เพิ่มเงื่อนไขเช็คว่ากระดานเต็มและยังไม่มีผู้ชนะ
if (!$game["winner"] && count($moves) == $game["size"] * $game["size"]) {
    // ไม่มีผู้ชนะและกระดานเต็ม กำหนดผลเกมเป็นเสมอ
    $sql = "UPDATE games SET winner = 'Tie' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $game_id);
    $stmt->execute();
    $game["winner"] = 'Tie';
}


$current_player = count($moves) % 2 == 0 ? $game["player1"] : $game["player2"];
?>

<!DOCTYPE html>
<html>

<head>
    <title>Play Game</title>
    <link rel="stylesheet" type="text/css" href="css/bot.css">

</head>

<body>
    <h1>Game ID: <?php echo $game_id; ?></h1>
    <h2><?php echo $game["player1"]; ?> (X) vs <?php echo $game["player2"]; ?> (O)</h2>

    <?php if ($game["winner"]) : ?>
        <?php if ($game["winner"] === "Tie") : ?>
            <h3>Draw!!!</h3>
            <table>
                <?php
                for ($i = 0; $i < $game["size"]; $i++) {
                    echo "<tr>";
                    for ($j = 0; $j < $game["size"]; $j++) {
                        echo "<td class='" . ($board[$i][$j] == 'X' ? 'X' : ($board[$i][$j] == 'O' ? 'O' : '')) . ($game["winner"] && $board[$i][$j] == $game["winner"] ? ' winner' : '') . "'>";
                        echo $board[$i][$j];
                        echo "</td>";
                    }
                    echo "</tr>";
                }
                ?>
            </table>

        <?php else : ?>
            <h3>Winner: <?php echo $game["winner"]; ?></h3>
            <table>
                <?php
                for ($i = 0; $i < $game["size"]; $i++) {
                    echo "<tr>";
                    for ($j = 0; $j < $game["size"]; $j++) {
                        echo "<td class='" . ($board[$i][$j] == 'X' ? 'X' : ($board[$i][$j] == 'O' ? 'O' : '')) . ($game["winner"] && $board[$i][$j] == $game["winner"] ? ' winner' : '') . "'>";
                        echo $board[$i][$j];
                        echo "</td>";
                    }
                    echo "</tr>";
                }
                ?>
            </table>
        <?php endif; ?>
        <!-- <a href="bot.php">Play Again</a> -->
        <form method="post" action="selectBot.php" class="play-again-form">
            <input type="hidden" name="size" value="<?php echo $game["size"]; ?>">
            <input type="hidden" name="player1" value="<?php echo $game["player1"]; ?>">
            <input type="hidden" name="player2" value="<?php echo $game["player2"]; ?>">
            <input type="submit" value="Play Again">
        </form>

        <a href="index.php">Go to Home page</a>


    <?php else : ?>
        <h3>Current Player: <?php echo $current_player == $game["player1"] ? 'X' : 'O'; ?></h3>
        <table>
            <?php
            for ($i = 0; $i < $game["size"]; $i++) {
                echo "<tr>";
                for ($j = 0; $j < $game["size"]; $j++) {
                    echo "<td class='" . ($board[$i][$j] == 'X' ? 'X' : ($board[$i][$j] == 'O' ? 'O' : '')) . "'>";
                    if ($board[$i][$j] == '' && !$game["winner"]) {
                        echo "<form method='post' action='' class='play-move-form'>
                                <input type='hidden' name='player' value='$current_player'>
                                <input type='hidden' name='move_row' value='$i'>
                                <input type='hidden' name='move_col' value='$j'>
                                <input type='submit' value=''>
                              </form>";
                    } else {
                        echo $board[$i][$j];
                    }
                    echo "</td>";
                }
                echo "</tr>";
            }
            ?>
        </table>

    <?php endif; ?>
</body>

</html>