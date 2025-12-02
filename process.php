<?php
require_once 'config.php';

$errors = [];

function sanitize($data) {
    return trim($data);
}

function validateFullName($name) {
    $name = sanitize($name);
    if (empty($name)) {
        return ['valid' => false, 'error' => 'Полное имя обязательно для заполнения'];
    }
    if (strlen($name) < 2 || strlen($name) > 100) {
        return ['valid' => false, 'error' => 'Полное имя должно содержать от 2 до 100 символов'];
    }
    if (!preg_match('/^[A-Za-zА-Яа-яЁё\s\-\']+$/u', $name)) {
        return ['valid' => false, 'error' => 'Полное имя может содержать только буквы, пробелы, дефисы и апострофы'];
    }
    return ['valid' => true, 'value' => $name];
}

function validateEmail($email) {
    $email = sanitize($email);
    if (empty($email)) {
        return ['valid' => false, 'error' => 'Email обязателен для заполнения'];
    }
    if (strlen($email) > 255) {
        return ['valid' => false, 'error' => 'Email не должен превышать 255 символов'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['valid' => false, 'error' => 'Некорректный формат email адреса'];
    }
    return ['valid' => true, 'value' => $email];
}

function validatePhone($phone) {
    $phone = sanitize($phone);
    if (empty($phone)) {
        return ['valid' => false, 'error' => 'Телефон обязателен для заполнения'];
    }
    $cleanPhone = preg_replace('/[^\d\+\-\s\(\)]/', '', $phone);
    if (strlen($cleanPhone) < 7 || strlen($cleanPhone) > 20) {
        return ['valid' => false, 'error' => 'Телефон должен содержать от 7 до 20 символов'];
    }
    if (!preg_match('/^[\d\+\-\s\(\)]+$/', $phone)) {
        return ['valid' => false, 'error' => 'Телефон может содержать только цифры, +, пробелы, дефисы и скобки'];
    }
    return ['valid' => true, 'value' => $phone];
}

function validateAge($age) {
    if (empty($age) && $age !== '0') {
        return ['valid' => false, 'error' => 'Возраст обязателен для заполнения'];
    }
    $age = intval($age);
    if ($age < 0 || $age > 120) {
        return ['valid' => false, 'error' => 'Возраст должен быть от 0 до 120 лет'];
    }
    return ['valid' => true, 'value' => $age];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: form.html');
    exit;
}

$fullNameResult = validateFullName($_POST['full_name'] ?? '');
if (!$fullNameResult['valid']) {
    $errors[] = $fullNameResult['error'];
} else {
    $fullName = $fullNameResult['value'];
}

$emailResult = validateEmail($_POST['email'] ?? '');
if (!$emailResult['valid']) {
    $errors[] = $emailResult['error'];
} else {
    $email = $emailResult['value'];
}

$phoneResult = validatePhone($_POST['phone'] ?? '');
if (!$phoneResult['valid']) {
    $errors[] = $phoneResult['error'];
} else {
    $phone = $phoneResult['value'];
}

$ageResult = validateAge($_POST['age'] ?? '');
if (!$ageResult['valid']) {
    $errors[] = $ageResult['error'];
} else {
    $age = $ageResult['value'];
}

if (!empty($errors)) {
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ошибка валидации</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="assets/styles.css">
    </head>
    <body>
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow border-danger">
                        <div class="card-header bg-danger text-white">
                            <h4 class="card-title mb-0">Ошибки валидации</h4>
                        </div>
                        <div class="card-body">
                            <p class="text-danger">Обнаружены следующие ошибки:</p>
                            <ul class="list-unstyled">
                                <?php foreach ($errors as $error): ?>
                                    <li class="text-danger mb-2">• <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="mt-3">
                                <a href="form.html" class="btn btn-primary">Назад к форме</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

try {
    $stmt = $mysqli->prepare("INSERT INTO user_profiles (full_name, email, phone, age) VALUES (?, ?, ?, ?)");
    
    if (!$stmt) {
        throw new Exception('Ошибка подготовки запроса');
    }
    
    $stmt->bind_param("sssi", $fullName, $email, $phone, $age);
    
    if (!$stmt->execute()) {
        throw new Exception('Ошибка выполнения запроса');
    }
    
    $insertedId = $mysqli->insert_id;
    
    $stmt->close();
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Данные сохранены</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="assets/styles.css">
    </head>
    <body>
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow border-success">
                        <div class="card-header bg-success text-white">
                            <h4 class="card-title mb-0">Данные успешно сохранены!</h4>
                        </div>
                        <div class="card-body">
                            <p class="text-success mb-3">Ваша анкета была успешно сохранена в базу данных.</p>
                            <div class="mb-3">
                                <strong>Сохранённые данные:</strong>
                                <ul class="list-unstyled mt-2">
                                    <li><strong>ID:</strong> <?php echo htmlspecialchars($insertedId, ENT_QUOTES, 'UTF-8'); ?></li>
                                    <li><strong>Полное имя:</strong> <?php echo htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?></li>
                                    <li><strong>Email:</strong> <?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></li>
                                    <li><strong>Телефон:</strong> <?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?></li>
                                    <li><strong>Возраст:</strong> <?php echo htmlspecialchars($age, ENT_QUOTES, 'UTF-8'); ?></li>
                                </ul>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="form.html" class="btn btn-primary">Новая запись</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    
} catch (Exception $e) {
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ошибка базы данных</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="assets/styles.css">
    </head>
    <body>
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow border-danger">
                        <div class="card-header bg-danger text-white">
                            <h4 class="card-title mb-0">Ошибка базы данных</h4>
                        </div>
                        <div class="card-body">
                            <p class="text-danger">Произошла ошибка при сохранении данных. Пожалуйста, попробуйте позже или обратитесь к администратору.</p>
                            <div class="mt-3">
                                <a href="form.html" class="btn btn-primary">Назад к форме</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}

$mysqli->close();
?>

