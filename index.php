<?php
// Эта страница авторизации и регистрации
session_start();

$success_register = null;

$session_lifetime = 20;
session_set_cookie_params($session_lifetime);

$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "native_php";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Ошибка подключения к БД: " . $conn->connect_error);
}


function registerUser($conn, $login, $gender, $birthYear, $password): void
{
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (login, gender, birthYear, password) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssis", $login, $gender, $birthYear, $hashedPassword);
    $stmt->execute();
    $stmt->close();
}

function authenticateUser($conn, $login, $password)
{
    $sql = "SELECT * FROM users WHERE login = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $login);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }

    return null;
}

if (isset($_SESSION['user'])) {
    header('Location: users.php');
    exit();
}

// Обработка POST-запроса для регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $login = $_POST['login'];
    $gender = $_POST['gender'];
    $birthYear = $_POST['birthYear'];
    $password = $_POST['password'];
    registerUser($conn, $login, $gender, $birthYear, $password);
    $_SESSION['success_register'] = "You have successfully registered";
    header('Location: index.php'); // После регистрации перенаправляем на страницу логина
    exit();
}

// Обработка POST-запроса для авторизации
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['log-in'])) {
    $login = $_POST['login'];
    $password = $_POST['password'];
    $user = authenticateUser($conn, $login, $password);
    if ($user) {
        $_SESSION['user'] = $user;
        $_SESSION['user']['expires_at'] = time() + 60;
        $_SESSION['success_login'] = "You have successfully log in";
        header('Location: users.php');
        exit();
    } else {
        $errorMessage = 'Неверные учетные данные.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Логин / Регистрация</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<h1>Логин / Регистрация</h1>

<?php if (isset($errorMessage)): ?>
    <p style="color: red;"><?php echo $errorMessage; ?></p>
<?php endif; ?>

<?php if (isset($_SESSION['success_register'])): ?>
    <p style="color: green;"><?php echo $_SESSION['success_register']; ?></p>
<?php endif; ?>

<form method="post">
    <h2>Логин</h2>
    <input type="text" name="login" required><br>
    <input type="password" name="password" required><br>
    <input type="submit" name="log-in" value="Войти">
</form>


<form method="post">
    <h2>Регистрация</h2>
    <input type="text" name="login"><br>
    <select name="gender">
        <option value="male">Мужской</option>
        <option value="female">Женский</option>
    </select><br>
    <input type="number" name="birthYear"><br>
    <input type="password" name="password"><br>
    <input type="submit" name="register" value="Зарегистрироваться">
</form>


</body>
</html>
