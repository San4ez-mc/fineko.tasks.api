<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

// Фейкові користувачі (поки без БД)
$users = [
    [
        'email' => 'test@test.com',
        'password' => '123456',
        'name' => 'Олександр'
    ]
];

// Фейкові задачі
$tasks = [
    ['id' => 1, 'title' => 'Підготувати звіт', 'status' => 'todo'],
    ['id' => 2, 'title' => 'Зателефонувати клієнту', 'status' => 'in_progress'],
    ['id' => 3, 'title' => 'Оновити сайт', 'status' => 'done']
];

// Визначаємо який ендпойнт викликали
$action = $_GET['action'] ?? '';

if ($action === 'login') {
    // Отримуємо JSON з фронтенду
    $data = json_decode(file_get_contents("php://input"), true);

    foreach ($users as $user) {
        if ($user['email'] === $data['email'] && $user['password'] === $data['password']) {
            echo json_encode([
                'success' => true,
                'user' => [
                    'name' => $user['name'],
                    'email' => $user['email']
                ]
            ]);
            exit;
        }
    }
    echo json_encode(['success' => false, 'message' => 'Невірний логін або пароль']);
    exit;
}

if ($action === 'tasks') {
    echo json_encode(['tasks' => $tasks]);
    exit;
}

// Якщо API викликано без параметра
echo json_encode(['message' => 'API працює']);
