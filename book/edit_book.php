<?php
include 'db.php';
if(!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header('Location: index.php');
    exit();
}

$book_id = $_GET['id'];
$message = '';

$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$book_id]);
$book = $stmt->fetch();

if(!$book) {
    die('Книга не найдена');
}

if(isset($_POST['update_book'])) {
    $book_name = $_POST['book_name'];
    $author = $_POST['author'];
    $year = $_POST['year'];
    $category = $_POST['category'];
    $price_buy = $_POST['price_buy'];
    $rent_two_w = $_POST['rent_two_w'];
    $rent_month = $_POST['rent_month'];
    $rent_three_m = $_POST['rent_three_m'];

    $stmt = $pdo->prepare("UPDATE books SET book_name = ?, author = ?, year = ?, category = ?, price_buy = ?, rent_two_w = ?, rent_month = ?, rent_three_m = ? WHERE id = ?");
    if($stmt->execute([$book_name, $author, $year, $category, $price_buy, $rent_two_w, $rent_month, $rent_three_m, $book_id])) {
        $message = "<p class='success'>Книга успешно обновлена</p>";
        $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
        $stmt->execute([$book_id]);
        $book = $stmt->fetch();
    } else {
        $message = "<p class='alert'>Ошибка при обновлении книги</p>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Редактировать книгу</title>
    <link rel="stylesheet" href="style.css">
    <!-- Подключение Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,500&display=swap">
</head>
<body>
    <header>
        <h1>Редактировать книгу</h1>
        <?php
        $user_id = $_SESSION['user_id'];
        $notif_stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $notif_stmt->execute([$user_id]);
        $unread_count = $notif_stmt->fetchColumn();
        ?>
        <nav>
            <a href="index.php">Главная</a>
            <a href="library.php">Библиотека</a>
            <a href="admin.php">Админ</a>
            <a href="notifications.php">Уведомления<?php if($unread_count > 0) echo " ({$unread_count})"; ?></a>
            <a href="logout.php">Выйти</a>
        </nav>
    </header>
    <main>
        <?php echo $message; ?>
        <form method="post" action="">
            <input type="text" name="book_name" placeholder="Название книги" value="<?php echo htmlspecialchars($book['book_name']); ?>" required>
            <input type="text" name="author" placeholder="Автор" value="<?php echo htmlspecialchars($book['author']); ?>" required>
            <input type="number" name="year" placeholder="Год" value="<?php echo htmlspecialchars($book['year']); ?>" required>
            <input type="text" name="category" placeholder="Категория" value="<?php echo htmlspecialchars($book['category']); ?>">
            <input type="number" step="0.01" name="price_buy" placeholder="Цена покупки" value="<?php echo htmlspecialchars($book['price_buy']); ?>" required>
            <input type="number" step="0.01" name="rent_two_w" placeholder="Аренда 2 недели" value="<?php echo htmlspecialchars($book['rent_two_w']); ?>" required>
            <input type="number" step="0.01" name="rent_month" placeholder="Аренда месяц" value="<?php echo htmlspecialchars($book['rent_month']); ?>" required>
            <input type="number" step="0.01" name="rent_three_m" placeholder="Аренда 3 месяца" value="<?php echo htmlspecialchars($book['rent_three_m']); ?>" required>
            <button type="submit" name="update_book">Обновить</button>
        </form>
    </main>
</body>
</html>
