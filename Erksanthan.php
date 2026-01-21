<?php
// Add this to reload the script when stuck
register_shutdown_function(function () {
    $time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
    if ($time > 25) {
        header("Location: {$_SERVER['SCRIPT_NAME']}");
        exit;
    }
});
// 1. Load configuration
require_once 'config.php';

// 2. Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);


// 2. Set UTF-8 encoding
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

// 3. Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// 4. Maintenance mode check
if (defined('MAINTENANCE_MODE') && MAINTENANCE_MODE === true) {
    header("HTTP/1.1 503 Service Unavailable");
    exit("Bot is under maintenance. Please try again later.");
}

// 5. Rate limiting implementation
function checkRateLimit($userId)
{
    if (!defined('RATE_LIMIT_PER_MINUTE') || RATE_LIMIT_PER_MINUTE <= 0) {
        return true;
    }

    $rateFile = 'rate_limits.json';
    $currentTime = time();
    $windowStart = $currentTime - 60; // 1 minute window

    // Read existing data
    $rateData = [];
    if (file_exists($rateFile)) {
        $rateData = json_decode(file_get_contents($rateFile), true) ?: [];
    }

    // Filter out old entries
    $rateData = array_filter($rateData, function ($entry) use ($windowStart) {
        return $entry['time'] >= $windowStart;
    });

    // Count user's requests in current window
    $userRequests = array_filter($rateData, function ($entry) use ($userId) {
        return $entry['user'] == $userId;
    });

    if (count($userRequests) >= RATE_LIMIT_PER_MINUTE) {
        return false;
    }

    // Log new request
    $rateData[] = ['user' => $userId, 'time' => $currentTime];
    file_put_contents($rateFile, json_encode(array_values($rateData)));

    return true;
}

// 6. Main bot logic
try {
    $content = file_get_contents("php://input");

    // Verify we received data
    if (empty($content)) {
        throw new Exception("No data received");
    }

    $update = json_decode($content, true);

    // Verify JSON decoding worked
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON: " . json_last_error_msg());
    }

    // Basic update validation
    if (!isset($update['message']['chat']['id'])) {
        throw new Exception("Invalid update format");
    }

    // Process message
    processMessage($update['message']);

} catch (Exception $e) {
    logError($e->getMessage());

    // Send error to admin if configured
    if (defined('ADMIN_ID') && ADMIN_ID) {
        sendMessage(ADMIN_ID, "❌ Bot Error: " . $e->getMessage());
    }

    // For webhook: send 200 OK to prevent retries
    header("HTTP/1.1 200 OK");
    exit;
}

// 7. Core Functions
function processMessage($message)
{
    $chatId = $message['chat']['id'];
    $userId = $message['from']['id'] ?? 0;
    $text = trim($message['text'] ?? '');
    $isGroup = ($message['chat']['type'] ?? 'private') !== 'private';

    // Check rate limiting
    if (!checkRateLimit($userId)) {
        sendMessage($chatId, "⚠️ You're sending too many requests. Please wait a minute.");
        return;
    }

    // Group message handling
    if ($isGroup) {
        // Check if message is for the bot
        $isForBot = false;

        // Check commands
        foreach (GROUP_COMMANDS as $cmd) {
            if (strpos($text, $cmd) === 0) {
                $text = trim(substr($text, strlen($cmd)));
                $isForBot = true;
                break;
            }
        }

        // Check mentions
        if (!$isForBot && strpos($text, BOT_USERNAME) !== false) {
            $text = trim(str_replace(BOT_USERNAME, '', $text));
            $isForBot = true;
        }

        if (!$isForBot)
            return;
    }

    // Handle empty messages
    if (empty($text)) {
        sendMessage($chatId, "<b>សូមសរសេរពាក្យដែរត្រូវស្វែងរក.<b>");
        return;
    }

    // Handle commands
    switch (strtolower($text)) {
        case '/start':
            sendWelcomeMessage($chatId, $isGroup);
            return;
        case '/help':
            sendHelpMessage($chatId, $isGroup);
            return;
        case (strpos(strtolower($text), '/admin') === 0 && isAdmin($userId)):
            handleAdminCommand($chatId, $text);
            return;
    }

    // Dictionary lookup
    try {
        $dictionary = loadDictionary();
        // Add temporary debug output:
        error_log("Dictionary loaded. First entry: " . json_encode($dictionary[0] ?? null));
        $result = searchDictionary($text, $dictionary);
        sendMessage($chatId, $result);
    } catch (Exception $e) {
        logError("Dictionary error: " . $e->getMessage());
        sendMessage($chatId, "⚠️ វិចនានុក្រុមកំពុងមានបញ្ហា");
    }
}

function searchDictionary($query, $dictionary)
{
    $query = trim($query);
    $suggestions = [];

    foreach ($dictionary as $entry) {
        $word = trim($entry['word'] ?? '');

        // Use mb_strtolower for Khmer characters
        $normalizedQuery = mb_strtolower($query, 'UTF-8');
        $normalizedWord = mb_strtolower($word, 'UTF-8');

        if ($normalizedQuery === $normalizedWord) {
            $definition = cleanDefinition($entry['definition'] ?? '');
            return "📘 <b>" . htmlspecialchars($word) . "</b>\n\n" . $definition;
        } elseif (mb_stripos($word, $query, 0, 'UTF-8') !== false) {
            $suggestions[] = $word;
        }
    }
    if (!empty($suggestions)) {
        $suggestText = implode(", ", array_slice($suggestions, 0, MAX_SUGGESTIONS));
        return "🔍 ស្វែងរកពុំឃើញដូចនូវពាក្យ  <b>" . htmlspecialchars($query) . "</b>\n\nសូមព្យយាមស្វែងរកពាក្យ:\n" . htmlspecialchars($suggestText);
    }

    return "❌ ពាក្យ <b>" . htmlspecialchars($query) . "</b> ស្វែងរកមិនឃើញ។";
}

// 8. Helper Functions
function cleanDefinition($definition)
{
    // Remove numeric codes and format properly
    $clean = preg_replace('/<"[0-9]+">/', '', $definition);
    return htmlspecialchars(trim($clean));
}

function loadDictionary()
{
    if (!file_exists(DB_FILE)) {
        logError("Dictionary file not found at: " . DB_FILE);
        throw new Exception("Dictionary file not found");
    }

    $data = file_get_contents(DB_FILE);
    if ($data === false) {
        logError("Failed to read dictionary file at: " . DB_FILE);
        throw new Exception("Failed to read dictionary");
    }

    $dict = json_decode($data, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        logError("Dictionary JSON error: " . json_last_error_msg());
        throw new Exception("Dictionary JSON error: " . json_last_error_msg());
    }

    // Debugging output: log the dictionary content
    error_log("Dictionary loaded: " . json_encode($dict));

    return $dict;
}

function sendMessage($chatId, $text, $parseMode = 'HTML')
{
    $url = "https://api.telegram.org/bot" . TOKEN . "/sendMessage";

    $data = [
        'chat_id' => $chatId,
        'text' => $text,
        'parse_mode' => $parseMode,
        'disable_web_page_preview' => true
    ];

    $options = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query($data)
        ]
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === false) {
        throw new Exception("Message send failed to $chatId");
    }

    return true;
}

function sendWelcomeMessage($chatId, $isGroup = false)
{
    $message = "👋 សូមស្វាគមន៍មកកាន់វចនានុក្រុមខ្មែរ ជូន ណាត​ \nWelcome to Dictionary Bot!\n";
    $message .= "សូមបញ្ចូលពាក្យដែរត្រូវស្វែងរក.";

    if ($isGroup) {
        $message .= "\n\nIn groups, mention me or use /define before words.";
    }

    sendMessage($chatId, $message);
}

function sendHelpMessage($chatId, $isGroup = false)
{
    $message = "📖 <b>ជំនួយ Dictionary Bot Help</b>\n\n";
    $message .= "• សូមផ្ញើរនូវពាក្យដែលអ្នកចង់់ស្វែងរក \nJust send me any word to get its definition\n";
    $message .= "• ការប្រើប្រាស់នៅក្នុងក្រុម សូមភ្ជាប់មកជាមួយពាក្យដែរត្រូវស្វែងរក និង /define។ ឧ. /define <u>ស្រលាញ់</u>\nIn groups, mention me or use /define before words\n\n";
    $message .= "Commands:\n";
    $message .= "/start - សារស្វាគមន៍ -- Welcome message\n";
    $message .= "/help - ស្វែងរករំនួយ -- This help message";

    sendMessage($chatId, $message);
}

function handleAdminCommand($chatId, $command)
{
    $parts = explode(' ', $command, 2);
    $subcommand = strtolower($parts[1] ?? '');

    switch ($subcommand) {
        case 'stats':
            $stats = getBotStats();
            sendMessage($chatId, "📊 Bot Statistics:\n" . $stats);
            break;
        default:
            sendMessage($chatId, "Available admin commands:\n/stats - Show bot statistics");
    }
}

function getBotStats()
{
    $logSize = file_exists(LOG_FILE) ? filesize(LOG_FILE) : 0;
    $dbSize = file_exists(DB_FILE) ? filesize(DB_FILE) : 0;

    return sprintf(
        "Log file size: %s\nDictionary entries: %d",
        formatBytes($logSize),
        count(
            json_decode(file_get_contents(DB_FILE), true) ?: 0
        )
    );
}

function formatBytes($bytes, $precision = 2)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
}

function isAdmin($userId)
{
    return defined('ADMIN_ID') && $userId == ADMIN_ID;
}

function logError($message)
{
    $logMessage = date("[Y-m-d H:i:s]") . " ERROR: " . $message . PHP_EOL;
    file_put_contents(LOG_FILE, $logMessage, FILE_APPEND);
}