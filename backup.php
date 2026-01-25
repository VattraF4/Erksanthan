<?php
require_once 'json_handler.php';

$message = '';
$error = '';

if (isset($_GET['create_backup'])) {
    if (backupDictionary()) {
        $message = "Backup created successfully!";
    } else {
        $error = "Failed to create backup.";
    }
}

// Get list of backup files
$backupDir = __DIR__ . '/backups/';
$backups = [];
if (file_exists($backupDir)) {
    $files = scandir($backupDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'json') {
            $backups[] = [
                'filename' => $file,
                'path' => $backupDir . $file,
                'size' => filesize($backupDir . $file),
                'modified' => filemtime($backupDir . $file)
            ];
        }
    }
    
    // Sort by modification time (newest first)
    usort($backups, function($a, $b) {
        return $b['modified'] <=> $a['modified'];
    });
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup Manager - Dictionary Manager</title>
    <link href="assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header fade-in">
            <h1><i class="fas fa-database"></i> Backup Manager</h1>
            <p>Manage your dictionary backups</p>
        </div>

        <div class="nav-tabs">
            <a href="index.php" class="tab-link"><i class="fas fa-list"></i> View All</a>
            <a href="backup.php" class="tab-link active"><i class="fas fa-download"></i> Backup</a>
        </div>

        <?php
        if ($message) echo showMessage('success', $message);
        if ($error) echo showMessage('error', $error);
        ?>

        <div class="form-container fade-in">
            <h2><i class="fas fa-plus-circle"></i> Create New Backup</h2>
            <p>Create a backup of all dictionary entries. This will save a JSON file with all current data.</p>
            
            <div class="btn-group">
                <a href="backup.php?create_backup=1" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Backup Now
                </a>
                <a href="export.php" class="btn btn-secondary">
                    <i class="fas fa-file-export"></i> Export Data
                </a>
            </div>
        </div>

        <?php if (!empty($backups)): ?>
        <div class="dictionary-container fade-in" style="margin-top: 30px;">
            <h2><i class="fas fa-history"></i> Existing Backups</h2>
            
            <table class="dictionary-table">
                <thead>
                    <tr>
                        <th>Filename</th>
                        <th>Date</th>
                        <th>Size</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($backups as $backup): ?>
                        <tr>
                            <td class="word">
                                <i class="fas fa-file-archive"></i>
                                <?php echo htmlspecialchars($backup['filename']); ?>
                            </td>
                            <td>
                                <?php echo date('Y-m-d H:i:s', $backup['modified']); ?>
                            </td>
                            <td>
                                <?php echo round($backup['size'] / 1024, 2); ?> KB
                            </td>
                            <td class="actions">
                                <a href="<?php echo htmlspecialchars($backup['path']); ?>" 
                                   download
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-download"></i> Download
                                </a>
                                <a href="backup.php?restore=<?php echo urlencode($backup['filename']); ?>" 
                                   class="btn btn-sm btn-secondary"
                                   onclick="return confirm('Restore from this backup? Current data will be replaced.')">
                                    <i class="fas fa-undo"></i> Restore
                                </a>
                                <a href="backup.php?delete=<?php echo urlencode($backup['filename']); ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Delete this backup? This action cannot be undone.')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="empty-state" style="margin-top: 30px;">
                <i class="fas fa-database"></i>
                <h3>No backups found</h3>
                <p>Create your first backup to get started.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>