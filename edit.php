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
    $word = sanitizeInput($_POST['word'] ?? '');
    $definition = sanitizeInput($_POST['definition'] ?? '');
    $example = sanitizeInput($_POST['example'] ?? '');
    
    // Validate
    $validationErrors = validateDictionaryItem(['word' => $word]);
    
    if (!empty($validationErrors)) {
        $error = implode('<br>', $validationErrors);
    } else {
        $updatedData = [
            'word' => $word,
            'definition' => $definition,
            'example' => $example
        ];
        
        if (updateDictionaryItem($id, $updatedData)) {
            header('Location: index.php?message=Entry updated successfully!&id=' . $id);
            exit();
        } else {
            $error = "Failed to update entry. Please try again.";
        }
    }
    
    // Refresh item data after POST
    $item = getDictionaryItem($id);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Entry - Dictionary Manager</title>
    <link href="assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header fade-in">
            <div>
                <h1><i class="fas fa-edit"></i> Edit Dictionary Entry</h1>
                <p>Update entry #<?php echo $item['id']; ?></p>
            </div>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>

        <div class="nav-tabs">
            <a href="index.php" class="tab-link"><i class="fas fa-list"></i> View All</a>
            <a href="add.php" class="tab-link"><i class="fas fa-plus"></i> Add New</a>
            <a href="edit.php?id=<?php echo $id; ?>" class="tab-link active"><i class="fas fa-edit"></i> Edit</a>
        </div>

        <?php
        if ($message) echo showMessage('success', $message);
        if ($error) echo showMessage('error', $error);
        ?>

        <div class="form-container fade-in">
            <form method="POST" action="">
                <h2><i class="fas fa-edit"></i> Edit Entry #<?php echo $item['id']; ?></h2>
                
                <div class="form-group">
                    <label for="id"><i class="fas fa-hashtag"></i> Entry ID</label>
                    <input type="text" id="id" value="<?php echo $item['id']; ?>" 
                           class="form-control" readonly disabled>
                    <small style="color: #666;">ID is automatically assigned and cannot be changed.</small>
                </div>
                
                <div class="form-group">
                    <label for="word"><i class="fas fa-font"></i> Word *</label>
                    <input type="text" id="word" name="word" required 
                           value="<?php echo htmlspecialchars($item['word']); ?>"
                           class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="definition"><i class="fas fa-align-left"></i> Definition</label>
                    <textarea id="definition" name="definition" 
                              class="form-control" rows="6"><?php echo htmlspecialchars($item['definition'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="example"><i class="fas fa-comment"></i> Example Usage</label>
                    <textarea id="example" name="example" 
                              class="form-control" rows="3"><?php echo htmlspecialchars($item['example'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-info-circle"></i> Entry Metadata</label>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px;">
                        <p><strong>Created:</strong> <?php echo date('F j, Y \a\t g:i A', strtotime($item['created_at'])); ?></p>
                        <p><strong>Last Updated:</strong> 
                            <?php echo isset($item['updated_at']) && $item['updated_at'] !== $item['created_at'] 
                                ? date('F j, Y \a\t g:i A', strtotime($item['updated_at'])) 
                                : 'Never'; ?>
                        </p>
                    </div>
                </div>
                
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Entry
                    </button>
                    <a href="delete.php?id=<?php echo $item['id']; ?>" 
                       class="btn btn-danger"
                       onclick="return confirm('Are you sure you want to delete this entry? This action cannot be undone.')">
                        <i class="fas fa-trash"></i> Delete Entry
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