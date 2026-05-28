<<<<<<< HEAD
<?php
// logout.php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/Services/SessionService.php';
require_once __DIR__ . '/src/Services/AuthService.php';

$which = $_GET['session'] ?? 'all';

if ($which === 'admin') {
    SessionService::start(SessionService::ADMIN_SESSION);
    (new AuthService())->logout();
    SessionService::destroy(SessionService::ADMIN_SESSION);
} elseif ($which === 'voter') {
    SessionService::start(SessionService::VOTER_SESSION);
    (new AuthService())->logout();
    SessionService::destroy(SessionService::VOTER_SESSION);
} else {
    foreach ([SessionService::ADMIN_SESSION, SessionService::VOTER_SESSION, SessionService::DEFAULT_SESSION] as $name) {
        SessionService::destroy($name);
    }
    // Legacy single-cookie cleanup
    foreach (['VOTESECURE_SESS', 'PHPSESSID'] as $legacy) {
        setcookie($legacy, '', time() - 3600, '/');
    }
}

header('Location: ' . APP_URL . '/index.php');
exit;
=======
<?php
// logout.php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/Services/SessionService.php';
require_once __DIR__ . '/src/Services/AuthService.php';

$which = $_GET['session'] ?? 'all';

if ($which === 'admin') {
    SessionService::start(SessionService::ADMIN_SESSION);
    (new AuthService())->logout();
    SessionService::destroy(SessionService::ADMIN_SESSION);
} elseif ($which === 'voter') {
    SessionService::start(SessionService::VOTER_SESSION);
    (new AuthService())->logout();
    SessionService::destroy(SessionService::VOTER_SESSION);
} else {
    foreach ([SessionService::ADMIN_SESSION, SessionService::VOTER_SESSION, SessionService::DEFAULT_SESSION] as $name) {
        SessionService::destroy($name);
    }
    // Legacy single-cookie cleanup
    foreach (['VOTESECURE_SESS', 'PHPSESSID'] as $legacy) {
        setcookie($legacy, '', time() - 3600, '/');
    }
}

header('Location: ' . APP_URL . '/index.php');
exit;
>>>>>>> 4b4892c0a36933c726154fb629a76e5be16d9c40
