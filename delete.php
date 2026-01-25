<?php
require_once 'json_handler.php';

$id = $_GET['id'] ?? 0;
$item = getDictionaryItem($id);

if (!$item) {
    header('Location: index.php?error=Entry not found');
    exit();
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm'])) {
        // Create backup before deletion
        backupDictionary();
        
        if (deleteDictionaryItem($id)) {
            header('Location: index.php?message=Entry deleted successfully!');
            exit();
        } else {
            $error = "Failed to delete entry. Please try again.";
        }
    } else {
        header('Location: index.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Entry - Dictionary Manager</title>
    <link href="assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .warning-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .entry-preview {
            background: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header fade-in">
            <div>
                <h1><i class="fas fa-trash-alt"></i> Delete Entry</h1>
                <p>Confirm deletion of entry #<?php echo $item['id']; ?></p>
            </div>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>

        <div class="nav-tabs">
            <a href="index.php" class="tab-link"><i class="fas fa-list"></i> View All</a>
            <a href="edit.php?id=<?php echo $id; ?>" class="tab-link"><i class="fas fa-edit"></i> Edit Entry</a>
            <a href="delete.php?id=<?php echo $id; ?>" class="tab-link active"><i class="fas fa-trash"></i> Delete</a>
        </div>

        <?php
        if ($message) echo showMessage('success', $message);
        if ($error) echo showMessage('error', $error);
        ?>

        <div class="form-container fade-in">
            <div class="warning-box">
                <h2 style="color: #856404;"><i class="fas fa-exclamation-triangle"></i> Warning!</h2>
                <p>You are about to delete the following entry. This action cannot be undone. A backup will be created automatically.</p>
            </div>
            
            <div class="entry-preview">
                <h3><?php echo htmlspecialchars($item['word']); ?></h3>
                <p><strong>Definition:</strong> <?php echo nl2br(htmlspecialchars(substr($item['definition'] ?? 'No definition', 0, 200))); ?></p>
                <p><strong>Created:</strong> <?php echo date('F j, Y', strtotime($item['created_at'])); ?></p>
                <?php if (isset($item['updated_at']) && $item['updated_at'] !== $item['created_at']): ?>
                    <p><strong>Last Updated:</strong> <?php echo date('F j, Y', strtotime($item['updated_at'])); ?></p>
                <?php endif; ?>
            </div>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="confirm-delete">
                        <input type="checkbox" id="confirm-delete" name="confirm" required>
                        I understand that this action cannot be undone and I want to delete this entry.
                    </label>
                </div>
                
                <div class="btn-group">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Confirm Delete
                    </button>
                    <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-secondary">
                        <i class="fas fa-edit"></i> Edit Instead
                    </a>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>