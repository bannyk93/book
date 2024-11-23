<?php
include 'db.php';
if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];

$notif_stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$notif_stmt->execute([$user_id]);
$unread_count = $notif_stmt->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Мои книги</title>
    <link rel="stylesheet" href="style.css">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,500&display=swap">
</head>
<body>
    <header>
        <h1>Мои книги</h1>
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
        <div class="book-list">
            <?php
            $stmt = $pdo->prepare("SELECT books.*, user_books.rent_end FROM books JOIN user_books ON books.id = user_books.book_id WHERE user_books.user_id = ? AND user_books.is_returned = 0");
            $stmt->execute([$user_id]);

            if($stmt->rowCount() > 0):
                while($row = $stmt->fetch()):
            ?>
                <div class="book">
                    <h3><?php echo htmlspecialchars($row['book_name']); ?></h3>
                    <p><?php echo htmlspecialchars($row['author']); ?>, <?php echo htmlspecialchars($row['year']); ?></p>
                    <?php if($row['rent_end']): ?>
                        <p>Аренда до <?php echo htmlspecialchars($row['rent_end']); ?></p>
                    <?php else: ?>
                        <p>Купленная книга</p>
                    <?php endif; ?>
                </div>
            <?php
                endwhile;
            else:
                echo "<p>У вас нет книг</p>";
            endif;
            ?>
        </div>
    </main>
</body>
</html>
