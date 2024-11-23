<?php
include 'db.php';
if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$book_id = $_GET['id'];
$type = $_GET['type'];

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch();

    if(!$book) {
        $message = "<p class='alert'>Книга не найдена</p>";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM user_books WHERE user_id = ? AND book_id = ? AND rent_end >= CURDATE()");
        $stmt->execute([$user_id, $book_id]);
        if($stmt->rowCount() > 0) {
            $message = "<p class='alert'>Вы уже арендовали эту книгу</p>";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();

            $price = 0;
            $rent_end = date('Y-m-d');

            if($type == '2w') {
                $price = $book['rent_two_w'];
                $rent_end = date('Y-m-d', strtotime('+2 weeks'));
            } elseif($type == '1m') {
                $price = $book['rent_month'];
                $rent_end = date('Y-m-d', strtotime('+1 month'));
            } elseif($type == '3m') {
                $price = $book['rent_three_m'];
                $rent_end = date('Y-m-d', strtotime('+3 months'));
            } else {
                $message = "<p class='alert'>Неверный тип аренды</p>";
            }

            if(!$message) {
                if($user['balance'] >= $price) {
                    $pdo->beginTransaction();

                    try {
                        $stmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
                        $stmt->execute([$price, $user_id]);

                        $stmt = $pdo->prepare("INSERT INTO user_books (user_id, book_id, rent_type, rent_end) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$user_id, $book_id, $type, $rent_end]);

                        $notify_stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                        $notify_stmt->execute([$user_id, "Вы успешно арендовали книгу \"" . $book['book_name'] . "\" до $rent_end."]);

                        $pdo->commit();

                        $message = "<p class='success'>Книга арендована до $rent_end</p>";
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $message = "<p class='alert'>Ошибка при аренде книги</p>";
                    }
                } else {
                    $message = "<p class='alert'>Недостаточно средств</p>";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Аренда книги</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Аренда книги</h1>
        <?php
        $notif_stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $notif_stmt->execute([$user_id]);
        $unread_count = $notif_stmt->fetchColumn();
        ?>
        <nav>
            <a href="index.php">Главная</a>
            <a href="library.php">Библиотека</a>
            <a href="my_books.php">Мои книги</a>
            <a href="notifications.php">Уведомления<?php if($unread_count > 0) echo " ({$unread_count})"; ?></a>
            <a href="wallet.php">Кошелек</a>
            <a href="logout.php">Выйти</a>
        </nav>
    </header>
    <main>
        <?php echo $message; ?>
        <?php if(!$message): ?>
            <p>Вы уверены, что хотите арендовать эту книгу на выбранный период?</p>
            <form method="post">
                <button type="submit">Арендовать</button>
            </form>
        <?php else: ?>
            <a href="library.php" class="button">Вернуться в библиотеку</a>
        <?php endif; ?>
    </main>
</body>
</html>
