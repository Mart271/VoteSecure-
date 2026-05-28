<?php
// config/database.php

define('DB_HOST',     getenv('DB_HOST')     ?: 'localhost');
define('DB_PORT',     getenv('DB_PORT')     ?: '3306');
define('DB_NAME',     getenv('DB_NAME')     ?: 'votesecure_db');
define('DB_USER',     getenv('DB_USER')     ?: 'root');
define('DB_PASS',     getenv('DB_PASS')     ?: '');
define('DB_CHARSET',  'utf8mb4');

define('QWEN_API_KEY',  'sk-16c472bae4234c27b924c73ab272fde2');
define('QWEN_API_URL',  'https://dashscope-intl.aliyuncs.com/compatible-mode/v1/chat/completions');
define('QWEN_MODEL',    'qwen3.6-plus');

define('APP_NAME',    'VoteSecure');
define('APP_VERSION', '1.0.0');
define('APP_URL', trim((string) (getenv('APP_URL') ?: 'http://localhost/votesecure')));
define('SESSION_LIFETIME', 3600); // 1 hour