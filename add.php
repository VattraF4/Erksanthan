<?php
require_once 'json_handler.php';

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
        $newItem = [
            'word' => $word,
            'definition' => $definition,
            'example' => $example
        ];
        
        $newId = addDictionaryItem($newItem);
        
        if ($newId) {
            header('Location: index.php?message=Entry added successfully!&id=' . $newId);
            exit();
        } else {
            $error = "Failed to add entry. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Entry - Dictionary Manager</title>
    <link href="assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header fade-in">
            <div>
                <h1><i class="fas fa-plus-circle"></i> Add New Dictionary Entry</h1>
                <p>Create a new word entry in your dictionary</p>
            </div>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>

        <div class="nav-tabs">
            <a href="index.php" class="tab-link"><i class="fas fa-list"></i> View All</a>
            <a href="add.php" class="tab-link active"><i class="fas fa-plus"></i> Add New</a>
            <a href="search.php" class="tab-link"><i class="fas fa-search"></i> Advanced Search</a>
        </div>

        <?php
        if ($message) echo showMessage('success', $message);
        if ($error) echo showMessage('error', $error);
        ?>

        <div class="form-container fade-in">
            <form method="POST" action="">
                <h2><i class="fas fa-edit"></i> Entry Details</h2>
                
                <div class="form-group">
                    <label for="word"><i class="fas fa-font"></i> Word *</label>
                    <input type="text" id="word" name="word" required 
                           value="<?php echo htmlspecialchars($_POST['word'] ?? ''); ?>"
                           placeholder="Enter the word" class="form-control">
                    <small style="color: #666;">Required. Maximum 100 characters.</small>
                </div>
                
                <div class="form-group">
                    <label for="definition"><i class="fas fa-align-left"></i> Definition</label>
                    <textarea id="definition" name="definition" 
                              placeholder="Enter the definition (optional)"
                              class="form-control" rows="6"><?php echo htmlspecialchars($_POST['definition'] ?? ''); ?></textarea>
                    <small style="color: #666;">You can use markdown or basic HTML formatting.</small>
                </div>
                
                <div class="form-group">
                    <label for="example"><i class="fas fa-comment"></i> Example Usage</label>
                    <textarea id="example" name="example" 
                              placeholder="Enter an example sentence (optional)"
                              class="form-control" rows="3"><?php echo htmlspecialchars($_POST['example'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-info-circle"></i> Additional Information</label>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px;">
                        <p><strong>Note:</strong> The entry ID will be automatically generated and cannot be changed.</p>
                        <p><strong>Tips:</strong></p>
                        <ul style="margin-left: 20px; margin-top: 10px;">
                            <li>Be specific with definitions</li>
                            <li>Include pronunciation if helpful</li>
                            <li>Add synonyms if applicable</li>
                            <li>Examples help with understanding</li>
                        </ul>
                    </div>
                </div>
                
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Entry
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Reset Form
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>