<?php
// Telegram Bot Configuration
define('TOKEN', '8254931317:AAHBFFJwJg6KmCtynrXkkUPqyxEdFkSNCsY'); // Replace with your actual token
define('ADMIN_ID', 1785971253); // Your Telegram user ID
define('BOT_USERNAME', '@vttra_saver_bot'); // Your bot's username

// Path Configuration
define('DB_FILE', 'db.json');
define('LOG_FILE', 'bot_logs.txt');


// Behavior Settings
define('MAX_SUGGESTIONS', 5);
define('RATE_LIMIT_PER_MINUTE', 15);
define('GROUP_COMMANDS', ['/define', '/d', '/meaning']); // Group commands
define('MAINTENANCE_MODE', false);

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');