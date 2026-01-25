<?php
require_once 'json_handler.php';

$id = $_GET['id'] ?? 0;
$item = getDictionaryItem($id);

if (!$item) {
    header('Location: index.php?error=Entry not found');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($item['word']); ?> - Dictionary Manager</title>
    <link href="assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .entry-detail {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .word-header {
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .definition-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            line-height: 1.8;
        }
        
        .example-box {
            background: #e8f5e9;
            padding: 15px;
            border-left: 4px solid #4CAF50;
            margin: 20px 0;
            font-style: italic;
        }
        
        .meta-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .meta-item {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header fade-in">
            <div>
                <h1><i class="fas fa-book-open"></i> Dictionary Entry</h1>
                <p>Viewing entry details</p>
            </div>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>

        <div class="nav-tabs">
            <a href="index.php" class="tab-link"><i class="fas fa-list"></i> View All</a>
            <a href="edit.php?id=<?php echo $id; ?>" class="tab-link"><i class="fas fa-edit"></i> Edit</a>
            <a href="view.php?id=<?php echo $id; ?>" class="tab-link active"><i class="fas fa-eye"></i> View</a>
        </div>

        <div class="entry-detail fade-in">
            <div class="word-header">
                <h1 style="color: #2c3e50; font-size: 3rem; margin-bottom: 10px;">
                    <?php echo htmlspecialchars($item['word']); ?>
                </h1>
                <p style="color: #666; font-size: 1.2rem;">
                    Entry #<?php echo $item['id']; ?>
                </p>
            </div>
            
            <h2><i class="fas fa-align-left"></i> Definition</h2>
            <div class="definition-box">
                <?php echo nl2br(htmlspecialchars($item['definition'] ?? 'No definition provided.')); ?>
            </div>
            
            <?php if (!empty($item['example'])): ?>
                <h2><i class="fas fa-comment"></i> Example Usage</h2>
                <div class="example-box">
                    "<?php echo htmlspecialchars($item['example']); ?>"
                </div>
            <?php endif; ?>
            
            <div class="meta-info">
                <div class="meta-item">
                    <h3><i class="fas fa-hashtag"></i> Entry ID</h3>
                    <p style="font-size: 1.5rem; font-weight: bold;">#<?php echo $item['id']; ?></p>
                </div>
                
                <div class="meta-item">
                    <h3><i class="fas fa-calendar-plus"></i> Created</h3>
                    <p><?php echo date('F j, Y', strtotime($item['created_at'])); ?></p>
                    <small><?php echo date('g:i A', strtotime($item['created_at'])); ?></small>
                </div>
                
                <div class="meta-item">
                    <h3><i class="fas fa-calendar-check"></i> Last Updated</h3>
                    <p>
                        <?php if (isset($item['updated_at']) && $item['updated_at'] !== $item['created_at']): ?>
                            <?php echo date('F j, Y', strtotime($item['updated_at'])); ?>
                            <br><small><?php echo date('g:i A', strtotime($item['updated_at'])); ?></small>
                        <?php else: ?>
                            Never
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            
            <div class="btn-group" style="margin-top: 30px;">
                <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit This Entry
                </a>
                <a href="delete.php?id=<?php echo $id; ?>" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Delete This Entry
                </a>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-list"></i> View All Entries
                </a>
            </div>
        </div>
    </div>
</body>
</html>