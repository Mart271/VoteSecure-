<?php
// voter/profile.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Services/SessionService.php';
require_once __DIR__ . '/../src/Services/AuthService.php';
require_once __DIR__ . '/../src/Models/User.php';

SessionService::start(SessionService::VOTER_SESSION);

$auth = new AuthService();
$auth->requireRole('voter');

$userModel = new User();
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle profile update
    $display = trim($_POST['display_name'] ?? '');
    $schoolEmail = trim($_POST['school_email'] ?? '');
    $schoolId = trim($_POST['school_id'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $section = trim($_POST['section'] ?? '');

    // password change fields
    $currentPw = $_POST['current_password'] ?? '';
    $newPw     = $_POST['new_password'] ?? '';
    $confirmPw = $_POST['confirm_password'] ?? '';

    // build update payload only with columns that exist
    $db = \Database::getInstance();
    $cols = array_map(fn($r) => $r['Field'], $db->query('SHOW COLUMNS FROM users'));

    $data = [];
    if (in_array('full_name', $cols, true)) $data['full_name'] = $display;
    if (in_array('email', $cols, true))     $data['email']     = $schoolEmail;
    if (in_array('school_id', $cols, true)) $data['school_id'] = $schoolId;
    if (in_array('department', $cols, true)) $data['department'] = $department;
    if (in_array('section', $cols, true))    $data['section']    = $section;

    // handle avatar upload
    if (!empty($_FILES['avatar']['tmp_name'])) {
        $uploadDir = __DIR__ . '/../public/uploads/avatars';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $fname = 'avatar_' . $userId . '_' . time() . '.' . $ext;
        $dest = $uploadDir . DIRECTORY_SEPARATOR . $fname;
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dest)) {
            if (in_array('avatar', $cols, true)) $data['avatar'] = $fname;
        }
    }

    // perform profile update
    try {
        if (!empty($data)) {
            $userModel->update($userId, $data);
            if (isset($data['full_name'])) $_SESSION['full_name'] = $data['full_name'];
            if (isset($data['avatar'])) $_SESSION['avatar'] = $data['avatar'];
            $_SESSION['flash_success'] = 'Profile updated successfully.';
        }

        // handle password change
        if ($newPw !== '') {
            $existing = $userModel->findById($userId);
            if (!$existing || !$userModel->verifyPassword($currentPw, $existing['password_hash'])) {
                $_SESSION['login_error'] = 'Current password is incorrect.';
            } elseif ($newPw !== $confirmPw) {
                $_SESSION['login_error'] = 'New passwords do not match.';
            } else {
                $userModel->updatePassword($userId, $newPw);
                $_SESSION['flash_success'] = 'Password changed successfully.';
            }
        }
    } catch (Throwable $e) {
        $_SESSION['login_error'] = 'Failed to update profile.';
    }

    header('Location: ' . APP_URL . '/voter/profile.php');
    exit;
}

$user = $userModel->findById($userId);
if (!empty($user['avatar'])) {
    $_SESSION['avatar'] = $user['avatar'];
}
require __DIR__ . '/../views/voter/profile.php';
