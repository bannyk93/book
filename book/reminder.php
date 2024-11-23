<?php
include 'db.php';

$today = date('Y-m-d');

$stmt = $pdo->prepare("SELECT user_books.*, users.id as user_id, users.username, books.book_name FROM user_books JOIN users ON user_books.user_id = users.id JOIN books ON user_books.book_id = books.id WHERE user_books.rent_end = DATE_ADD(?, INTERVAL 2 DAY) AND user_books.is_returned = 0");
$stmt->execute([$today]);

while($row = $stmt->fetch()) {
    $user_id = $row['user_id'];
    $message = "Уважаемый " . $row['username'] . ", срок аренды книги \"" . $row['book_name'] . "\" истекает " . date('d.m.Y', strtotime($row['rent_end'])) . ". Пожалуйста, продлите аренду или верните книгу.";
    
    $notify_stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $notify_stmt->execute([$user_id, $message]);
    
    echo "Уведомление создано для пользователя " . $row['username'] . "<br>";
}
?>
