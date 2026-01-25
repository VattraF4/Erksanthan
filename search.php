<?php
require_once 'json_handler.php';

$results = [];
$searchWord = sanitizeInput($_GET['word'] ?? '');
$searchDefinition = sanitizeInput($_GET['definition'] ?? '');
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($searchWord || $searchDefinition || $dateFrom || $dateTo)) {
    $allItems = getAllDictionaryItems('created_at', 'DESC');
    
    foreach ($allItems as $item) {
        $matches = true;
        
        if ($searchWord && stripos($item['word'], $searchWord) === false) {
            $matches = false;
        }
        
        if ($searchDefinition && stripos($item['definition'] ?? '', $searchDefinition) === false) {
            $matches = false;
        }
        
        if ($dateFrom && strtotime($item['created_at']) < strtotime($dateFrom)) {
            $matches = false;
        }
        
        if ($dateTo && strtotime($item['created_at']) > strtotime($dateTo . ' 23:59:59')) {
            $matches = false;
        }
        
        if ($matches) {
            $results[] = $item;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Search - Dictionary Manager</title>
    <link href="assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header fade-in">
            <h1><i class="fas fa-search"></i> Advanced Search</h1>
            <p>Find entries using specific criteria</p>
        </div>

        <div class="nav-tabs">
            <a href="index.php" class="tab-link"><i class="fas fa-list"></i> View All</a>
            <a href="search.php" class="tab-link active"><i class="fas fa-search"></i> Advanced Search</a>
        </div>

        <div class="form-container fade-in">
            <form method="GET" action="">
                <h2><i class="fas fa-filter"></i> Search Filters</h2>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label for="word"><i class="fas fa-font"></i> Word contains</label>
                        <input type="text" id="word" name="word" 
                               value="<?php echo htmlspecialchars($searchWord); ?>"
                               placeholder="Search in words" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="definition"><i class="fas fa-align-left"></i> Definition contains</label>
                        <input type="text" id="definition" name="definition" 
                               value="<?php echo htmlspecialchars($searchDefinition); ?>"
                               placeholder="Search in definitions" class="form-control">
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                    <div class="form-group">
                        <label for="date_from"><i class="fas fa-calendar-plus"></i> Created After</label>
                        <input type="date" id="date_from" name="date_from" 
                               value="<?php echo htmlspecialchars($dateFrom); ?>" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="date_to"><i class="fas fa-calendar-minus"></i> Created Before</label>
                        <input type="date" id="date_to" name="date_to" 
                               value="<?php echo htmlspecialchars($dateTo); ?>" class="form-control">
                    </div>
                </div>
                
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>

        <?php if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($searchWord || $searchDefinition || $dateFrom || $dateTo)): ?>
            <div class="dictionary-container fade-in" style="margin-top: 30px;">
                <h2><i class="fas fa-search"></i> Search Results (<?php echo count($results); ?> found)</h2>
                
                <?php if (empty($results)): ?>
                    <div class="empty-state">
                        <i class="fas fa-search-minus"></i>
                        <h3>No results found</h3>
                        <p>Try adjusting your search criteria.</p>
                    </div>
                <?php else: ?>
                    <table class="dictionary-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Word</th>
                                <th>Definition</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $item): ?>
                                <tr>
                                    <td><?php echo $item['id']; ?></td>
                                    <td class="word"><?php echo htmlspecialchars($item['word']); ?></td>
                                    <td><?php echo nl2br(htmlspecialchars(substr($item['definition'] ?? 'No definition', 0, 100))); ?>...</td>
                                    <td><?php echo date('M d, Y', strtotime($item['created_at'])); ?></td>
                                    <td class="actions">
                                        <a href="edit.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-secondary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="view.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>