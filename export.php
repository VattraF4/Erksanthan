<?php
require_once 'json_handler.php';

if (isset($_GET['download'])) {
    $items = getAllDictionaryItems('id', 'ASC');
    $json = json_encode($items, JSON_PRETTY_PRINT);
    
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="dictionary_export_' . date('Y-m-d') . '.json"');
    header('Content-Length: ' . strlen($json));
    
    echo $json;
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Dictionary - Dictionary Manager</title>
    <link href="assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header fade-in">
            <h1><i class="fas fa-file-export"></i> Export Dictionary</h1>
            <p>Export your dictionary data</p>
        </div>

        <div class="form-container fade-in">
            <h2><i class="fas fa-download"></i> Export Options</h2>
            
            <div class="form-group">
                <label><i class="fas fa-info-circle"></i> Export Information</label>
                <div style="background: #f8f9fa; padding: 20px; border-radius: 5px;">
                    <p>This will export all dictionary entries in JSON format.</p>
                    <p><strong>Total Entries:</strong> <?php echo getDictionaryCount(); ?></p>
                    <p><strong>Export Format:</strong> JSON (JavaScript Object Notation)</p>
                    <p><strong>Features:</strong></p>
                    <ul style="margin-left: 20px;">
                        <li>Human-readable format</li>
                        <li>Includes all entry metadata</li>
                        <li>Can be imported into other systems</li>
                        <li>Backup your data regularly</li>
                    </ul>
                </div>
            </div>
            
            <div class="btn-group">
                <a href="export.php?download=1" class="btn btn-primary">
                    <i class="fas fa-file-download"></i> Download JSON Export
                </a>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dictionary
                </a>
            </div>
        </div>
    </div>
</body>
</html>