<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Set JSON header
header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');
$suggestions = isset($_GET['suggestions']) && $_GET['suggestions'] === 'true';

if (empty($query)) {
    echo json_encode(['success' => false, 'message' => 'Search query is required']);
    exit;
}

if ($suggestions) {
    // Return search suggestions
    $pdo = getConnection();
    
    // Search in craft titles, categories, and artisan names
    $sql = "
        SELECT DISTINCT 
            c.title as suggestion,
            'craft' as type
        FROM crafts c 
        WHERE c.title LIKE ? AND c.status = 'active'
        
        UNION
        
        SELECT DISTINCT 
            cc.name as suggestion,
            'category' as type
        FROM craft_categories cc 
        WHERE cc.name LIKE ?
        
        UNION
        
        SELECT DISTINCT 
            u.name as suggestion,
            'artisan' as type
        FROM users u 
        JOIN artisans a ON u.id = a.user_id 
        WHERE u.name LIKE ?
        
        LIMIT 8
    ";
    
    $searchTerm = "%$query%";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    $results = $stmt->fetchAll();
    
    $suggestions = [];
    foreach ($results as $result) {
        $suggestions[] = [
            'text' => htmlspecialchars($result['suggestion']),
            'query' => $result['suggestion'],
            'type' => $result['type']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'suggestions' => $suggestions
    ]);
    
} else {
    // Return full search results
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = 12;
    $offset = ($page - 1) * $limit;
    
    // Get crafts matching the search query
    $crafts = getAllCrafts($limit, $offset, null, $query);
    
    // Get total count for pagination
    $pdo = getConnection();
    $countSql = "
        SELECT COUNT(*) 
        FROM crafts c 
        JOIN craft_categories cc ON c.category_id = cc.id 
        JOIN artisans a ON c.artisan_id = a.id 
        JOIN users u ON a.user_id = u.id 
        WHERE c.status = 'active' 
        AND (c.title LIKE ? OR c.description LIKE ? OR cc.name LIKE ? OR u.name LIKE ?)
    ";
    
    $searchTerm = "%$query%";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $totalResults = $stmt->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'query' => $query,
        'results' => $crafts,
        'total_results' => $totalResults,
        'page' => $page,
        'total_pages' => ceil($totalResults / $limit),
        'results_per_page' => $limit
    ]);
}
?>
