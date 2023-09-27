<?php

session_start();

if ($_SESSION['user']['expires_at'] < time()) {
    session_destroy();
    header('Location: index.php');
    exit();
}

$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "native_php";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Ошибка подключения к БД: " . $conn->connect_error);
}

function deleteUser($conn, $login): void
{
    $sql = "DELETE FROM users WHERE login = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $login);
    $stmt->execute();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $loginToDelete = $_POST['loginToDelete'];
    deleteUser($conn, $loginToDelete);
}

// Данные пользователя
$user = $_SESSION['user'];

// Получаем список всех пользователей
$sql = "SELECT * FROM users";
$result = $conn->query($sql);
$users = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

if (isset($_POST['logout'])) {
//    unset($_SESSION['user']);
    session_destroy();
    header('Location: index.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Таблица пользователей</title>
    <link rel="stylesheet" href="css/users.css">
</head>
<header>
    <?php if (isset($_SESSION['success_login'])): ?>
        <p style="color: green;"><?php echo $_SESSION['success_login']; ?></p>
    <?php endif; ?>
</header>
<body>
<h1>Таблица пользователей</h1>
<p>Добро пожаловать, <?php echo $user['login']; ?>!</p>

<table border="1">
    <tr>
        <th>Логин</th>
        <th>Пол</th>
        <th>Возраст</th>
        <th>Управление</th>
    </tr>
    <?php foreach ($users as $userData): ?>
        <tr>
            <td><?php echo $userData['login']; ?></td>
            <td><?php echo $userData['gender']; ?></td>
            <td><?php echo $userData['birthYear']; ?></td>
            <td>
                <form method="post">
                    <input type="hidden" name="loginToDelete" value="<?php echo $userData['login']; ?>">
                    <input type="submit" name="delete" value="Удалить">
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<form method="post">
    <input type="submit" value="Выйти" name="logout">
</form>
</body>
</html>
