<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Set JSON header
header('Content-Type: application/json');

$pdo = getConnection();
$userId = $_SESSION['user_id'] ?? null;
$craftId = intval($_GET['craft_id'] ?? 0);
$type = $_GET['type'] ?? 'general';
$limit = min(12, intval($_GET['limit'] ?? 6));

// AI-powered recommendation engine
function getRecommendations($pdo, $userId, $craftId, $type, $limit) {
    $recommendations = [];
    
    switch ($type) {
        case 'similar':
            // Content-based filtering: Similar crafts
            if ($craftId) {
                $craft = getCraftById($craftId);
                if ($craft) {
                    $sql = "
                        SELECT c.*, cc.name as category_name, u.name as artisan_name,
                               (SELECT image_url FROM craft_images WHERE craft_id = c.id AND is_primary = TRUE LIMIT 1) as primary_image,
                               (
                                   -- Similarity score based on category, price range, and artisan
                                   CASE WHEN c.category_id = ? THEN 3 ELSE 0 END +
                                   CASE WHEN ABS(c.price - ?) < 500 THEN 2 ELSE 0 END +
                                   CASE WHEN c.artisan_id = ? THEN 1 ELSE 0 END
                               ) as similarity_score
                        FROM crafts c 
                        JOIN craft_categories cc ON c.category_id = cc.id 
                        JOIN artisans a ON c.artisan_id = a.id 
                        JOIN users u ON a.user_id = u.id 
                        WHERE c.status = 'active' AND c.id != ?
                        ORDER BY similarity_score DESC, RAND()
                        LIMIT ?
                    ";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$craft['category_id'], $craft['price'], $craft['artisan_id'], $craftId, $limit]);
                    $recommendations = $stmt->fetchAll();
                }
            }
            break;
            
        case 'collaborative':
            // Collaborative filtering: Users who bought this also bought
            if ($craftId && $userId) {
                $sql = "
                    SELECT c.*, cc.name as category_name, u.name as artisan_name,
                           (SELECT image_url FROM craft_images WHERE craft_id = c.id AND is_primary = TRUE LIMIT 1) as primary_image,
                           COUNT(DISTINCT o2.user_id) as co_purchase_count
                    FROM crafts c 
                    JOIN craft_categories cc ON c.category_id = cc.id 
                    JOIN artisans a ON c.artisan_id = a.id 
                    JOIN users u ON a.user_id = u.id 
                    JOIN order_items oi ON c.id = oi.craft_id
                    JOIN orders o ON oi.order_id = o.id
                    JOIN orders o2 ON o.user_id = o2.user_id
                    JOIN order_items oi2 ON o2.id = oi2.order_id
                    WHERE c.status = 'active' 
                    AND c.id != ? 
                    AND oi2.craft_id = ?
                    AND o.payment_status = 'completed'
                    AND o2.payment_status = 'completed'
                    GROUP BY c.id
                    ORDER BY co_purchase_count DESC, c.created_at DESC
                    LIMIT ?
                ";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$craftId, $craftId, $limit]);
                $recommendations = $stmt->fetchAll();
            }
            break;
            
        case 'personalized':
            // Personalized recommendations based on user history
            if ($userId) {
                $sql = "
                    SELECT c.*, cc.name as category_name, u.name as artisan_name,
                           (SELECT image_url FROM craft_images WHERE craft_id = c.id AND is_primary = TRUE LIMIT 1) as primary_image,
                           (
                               -- Personalization score based on user's purchase history
                               CASE WHEN cc.id IN (
                                   SELECT DISTINCT c2.category_id 
                                   FROM crafts c2 
                                   JOIN order_items oi ON c2.id = oi.craft_id
                                   JOIN orders o ON oi.order_id = o.id
                                   WHERE o.user_id = ? AND o.payment_status = 'completed'
                               ) THEN 5 ELSE 0 END +
                               CASE WHEN c.price BETWEEN 
                                   (SELECT AVG(c3.price) * 0.7 FROM crafts c3 
                                    JOIN order_items oi3 ON c3.id = oi3.craft_id
                                    JOIN orders o3 ON oi3.order_id = o3.id
                                    WHERE o3.user_id = ? AND o3.payment_status = 'completed') 
                                   AND 
                                   (SELECT AVG(c3.price) * 1.3 FROM crafts c3 
                                    JOIN order_items oi3 ON c3.id = oi3.craft_id
                                    JOIN orders o3 ON oi3.order_id = o3.id
                                    WHERE o3.user_id = ? AND o3.payment_status = 'completed')
                               THEN 3 ELSE 0 END +
                               CASE WHEN c.status = 'featured' THEN 2 ELSE 0 END
                           ) as personalization_score
                    FROM crafts c 
                    JOIN craft_categories cc ON c.category_id = cc.id 
                    JOIN artisans a ON c.artisan_id = a.id 
                    JOIN users u ON a.user_id = u.id 
                    WHERE c.status = 'active'
                    AND c.id NOT IN (
                        SELECT oi.craft_id 
                        FROM order_items oi 
                        JOIN orders o ON oi.order_id = o.id 
                        WHERE o.user_id = ? AND o.payment_status = 'completed'
                    )
                    ORDER BY personalization_score DESC, c.created_at DESC
                    LIMIT ?
                ";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$userId, $userId, $userId, $userId, $limit]);
                $recommendations = $stmt->fetchAll();
            }
            break;
            
        case 'trending':
            // Trending crafts based on recent sales and views
            $sql = "
                SELECT c.*, cc.name as category_name, u.name as artisan_name,
                       (SELECT image_url FROM craft_images WHERE craft_id = c.id AND is_primary = TRUE LIMIT 1) as primary_image,
                       (
                           COALESCE(recent_sales.sales_count, 0) * 3 +
                           CASE WHEN c.status = 'featured' THEN 5 ELSE 0 END +
                           CASE WHEN cc.gi_tagged = 1 THEN 2 ELSE 0 END
                       ) as trending_score
                FROM crafts c 
                JOIN craft_categories cc ON c.category_id = cc.id 
                JOIN artisans a ON c.artisan_id = a.id 
                JOIN users u ON a.user_id = u.id 
                LEFT JOIN (
                    SELECT oi.craft_id, COUNT(*) as sales_count
                    FROM order_items oi
                    JOIN orders o ON oi.order_id = o.id
                    WHERE o.payment_status = 'completed' 
                    AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY oi.craft_id
                ) recent_sales ON c.id = recent_sales.craft_id
                WHERE c.status = 'active'
                ORDER BY trending_score DESC, c.created_at DESC
                LIMIT ?
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$limit]);
            $recommendations = $stmt->fetchAll();
            break;
            
        case 'category':
            // Category-based recommendations
            $category = $_GET['category'] ?? '';
            if ($category) {
                $sql = "
                    SELECT c.*, cc.name as category_name, u.name as artisan_name,
                           (SELECT image_url FROM craft_images WHERE craft_id = c.id AND is_primary = TRUE LIMIT 1) as primary_image
                    FROM crafts c 
                    JOIN craft_categories cc ON c.category_id = cc.id 
                    JOIN artisans a ON c.artisan_id = a.id 
                    JOIN users u ON a.user_id = u.id 
                    WHERE c.status = 'active' AND cc.name = ?
                    ORDER BY 
                        CASE WHEN c.status = 'featured' THEN 0 ELSE 1 END,
                        c.created_at DESC
                    LIMIT ?
                ";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$category, $limit]);
                $recommendations = $stmt->fetchAll();
            }
            break;
            
        default:
            // General recommendations (featured + popular)
            $sql = "
                SELECT c.*, cc.name as category_name, u.name as artisan_name,
                       (SELECT image_url FROM craft_images WHERE craft_id = c.id AND is_primary = TRUE LIMIT 1) as primary_image,
                       (
                           CASE WHEN c.status = 'featured' THEN 10 ELSE 0 END +
                           CASE WHEN cc.gi_tagged = 1 THEN 5 ELSE 0 END +
                           COALESCE(sales_count, 0)
                       ) as recommendation_score
                FROM crafts c 
                JOIN craft_categories cc ON c.category_id = cc.id 
                JOIN artisans a ON c.artisan_id = a.id 
                JOIN users u ON a.user_id = u.id 
                LEFT JOIN (
                    SELECT oi.craft_id, COUNT(*) as sales_count
                    FROM order_items oi
                    JOIN orders o ON oi.order_id = o.id
                    WHERE o.payment_status = 'completed'
                    GROUP BY oi.craft_id
                ) sales ON c.id = sales.craft_id
                WHERE c.status = 'active'
                ORDER BY recommendation_score DESC, c.created_at DESC
                LIMIT ?
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$limit]);
            $recommendations = $stmt->fetchAll();
    }
    
    return $recommendations;
}

// Get recommendations
$recommendations = getRecommendations($pdo, $userId, $craftId, $type, $limit);

// Add additional metadata
foreach ($recommendations as &$rec) {
    $rec['formatted_price'] = formatPrice($rec['price']);
    $rec['rating'] = 4.5; // Placeholder rating
    $rec['review_count'] = rand(5, 50); // Placeholder review count
    
    // Calculate discount percentage if applicable
    if (isset($rec['original_price']) && $rec['original_price'] > $rec['price']) {
        $rec['discount_percentage'] = round((($rec['original_price'] - $rec['price']) / $rec['original_price']) * 100);
    }
}

// Response
echo json_encode([
    'success' => true,
    'type' => $type,
    'recommendations' => $recommendations,
    'count' => count($recommendations),
    'user_id' => $userId,
    'craft_id' => $craftId
]);
?>
