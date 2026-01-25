<?php
require_once 'json_handler.php';
$searchTerm = $_GET['search'] ?? '';
$viewMode = $_GET['view'] ?? 'table'; // 'table' or 'cards'

if ($searchTerm) {
    $items = searchDictionaryItems($searchTerm);
} else {
    $items = getAllDictionaryItems('word', 'ASC');
}

$totalItems = getDictionaryCount();
$searchResults = $searchTerm ? count($items) : $totalItems;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dictionary Manager</title>
    <link href="assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header fade-in">
            <div>
                <h1><i class="fas fa-book"></i> Dictionary Manager</h1>
                <p>Manage your dictionary entries with ease</p>
            </div>
            <div class="stats">
                <i class="fas fa-list"></i> <?php echo $totalItems; ?> Total Entries
            </div>
        </div>

        <?php
        $message = $_GET['message'] ?? '';
        $error = $_GET['error'] ?? '';
        if ($message) echo showMessage('success', $message);
        if ($error) echo showMessage('error', $error);
        ?>

        <div class="nav-tabs">
            <a href="index.php" class="tab-link active"><i class="fas fa-list"></i> View All</a>
            <a href="add.php" class="tab-link"><i class="fas fa-plus"></i> Add New</a>
            <a href="search.php" class="tab-link"><i class="fas fa-search"></i> Advanced Search</a>
            <a href="backup.php" class="tab-link"><i class="fas fa-download"></i> Backup</a>
        </div>

        <div class="search-box">
            <form method="GET" action="" style="flex: 1; display: flex; gap: 10px;">
                <input type="text" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" 
                       placeholder="Search words or definitions..." class="form-control">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                <?php if ($searchTerm): ?>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                <?php endif; ?>
            </form>
            <div style="display: flex; gap: 10px; align-items: center;">
                <span>View:</span>
                <a href="?view=table<?php echo $searchTerm ? '&search=' . urlencode($searchTerm) : ''; ?>" 
                   class="btn btn-sm <?php echo $viewMode === 'table' ? 'btn-primary' : 'btn-secondary'; ?>">
                    <i class="fas fa-table"></i>
                </a>
                <a href="?view=cards<?php echo $searchTerm ? '&search=' . urlencode($searchTerm) : ''; ?>" 
                   class="btn btn-sm <?php echo $viewMode === 'cards' ? 'btn-primary' : 'btn-secondary'; ?>">
                    <i class="fas fa-th"></i>
                </a>
            </div>
        </div>

        <div class="dictionary-container fade-in">
            <h2>
                <?php if ($searchTerm): ?>
                    <i class="fas fa-search"></i> Search Results for "<?php echo htmlspecialchars($searchTerm); ?>"
                <?php else: ?>
                    <i class="fas fa-book"></i> Dictionary Entries
                <?php endif; ?>
                <span class="stats" style="font-size: 0.8rem; margin-left: 10px;">
                    (<?php echo $searchResults; ?> found)
                </span>
            </h2>

            <?php if (empty($items)): ?>
                <div class="empty-state">
                    <i class="fas fa-book-open"></i>
                    <h3>No entries found</h3>
                    <p><?php echo $searchTerm ? 'Try a different search term.' : 'Start by adding your first dictionary entry!'; ?></p>
                    <a href="add.php" class="btn btn-primary" style="margin-top: 20px;">
                        <i class="fas fa-plus"></i> Add New Entry
                    </a>
                </div>
            <?php elseif ($viewMode === 'cards'): ?>
                <div class="cards-grid">
                    <?php foreach ($items as $item): ?>
                        <div class="card">
                            <h3><?php echo htmlspecialchars($item['word']); ?></h3>
                            <div class="definition">
                                <?php echo nl2br(htmlspecialchars($item['definition'] ?? 'No definition provided')); ?>
                            </div>
                            <div class="meta">
                                ID: <?php echo $item['id']; ?> | 
                                Created: <?php echo date('M d, Y', strtotime($item['created_at'])); ?>
                                <?php if (isset($item['updated_at']) && $item['updated_at'] !== $item['created_at']): ?>
                                    <br>Updated: <?php echo date('M d, Y', strtotime($item['updated_at'])); ?>
                                <?php endif; ?>
                            </div>
                            <div class="btn-group" style="margin-top: 15px;">
                                <a href="edit.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="delete.php?id=<?php echo $item['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Are you sure you want to delete this entry?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <table class="dictionary-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">ID</th>
                            <th style="width: 20%;">Word</th>
                            <th style="width: 50%;">Definition</th>
                            <th style="width: 15%;">Created</th>
                            <th style="width: 10%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo $item['id']; ?></td>
                                <td class="word"><?php echo htmlspecialchars($item['word']); ?></td>
                                <td>
                                    <div class="definition">
                                        <?php echo nl2br(htmlspecialchars(substr($item['definition'] ?? 'No definition', 0, 150) . (strlen($item['definition'] ?? '') > 150 ? '...' : ''))); ?>
                                    </div>
                                    <div class="meta">
                                        <?php if (isset($item['updated_at']) && $item['updated_at'] !== $item['created_at']): ?>
                                            Updated: <?php echo date('M d, Y', strtotime($item['updated_at'])); ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($item['created_at'])); ?></td>
                                <td class="actions">
                                    <a href="edit.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-secondary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete.php?id=<?php echo $item['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this entry?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <?php if (!empty($items)): ?>
                <div style="margin-top: 30px; text-align: center;">
                    <a href="add.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Entry
                    </a>
                    <a href="export.php" class="btn btn-secondary">
                        <i class="fas fa-file-export"></i> Export to JSON
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>