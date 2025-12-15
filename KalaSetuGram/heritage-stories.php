<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get filter parameters
$category = $_GET['category'] ?? '';
$district = $_GET['district'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 9;
$offset = ($page - 1) * $limit;

// Get all categories and districts for filters
$pdo = getConnection();
$categories = getAllCategories();

$districts = $pdo->query("SELECT DISTINCT district FROM heritage_stories WHERE district IS NOT NULL ORDER BY district")->fetchAll(PDO::FETCH_COLUMN);

// Get heritage stories with filters
$sql = "SELECT hs.*, cc.name as category_name 
        FROM heritage_stories hs 
        LEFT JOIN craft_categories cc ON hs.category_id = cc.id 
        WHERE 1=1";
$params = [];

if ($category) {
    $sql .= " AND cc.name = ?";
    $params[] = $category;
}

if ($district) {
    $sql .= " AND hs.district = ?";
    $params[] = $district;
}

$sql .= " ORDER BY hs.is_featured DESC, hs.created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$stories = $stmt->fetchAll();

// Get total count for pagination
$countSql = "SELECT COUNT(*) FROM heritage_stories hs 
             LEFT JOIN craft_categories cc ON hs.category_id = cc.id 
             WHERE 1=1";
$countParams = [];

if ($category) {
    $countSql .= " AND cc.name = ?";
    $countParams[] = $category;
}

if ($district) {
    $countSql .= " AND hs.district = ?";
    $countParams[] = $district;
}

$stmt = $pdo->prepare($countSql);
$stmt->execute($countParams);
$totalStories = $stmt->fetchColumn();
$totalPages = ceil($totalStories / $limit);

// Get featured story
$featuredStory = $pdo->query("
    SELECT hs.*, cc.name as category_name 
    FROM heritage_stories hs 
    LEFT JOIN craft_categories cc ON hs.category_id = cc.id 
    WHERE hs.is_featured = 1 
    ORDER BY hs.created_at DESC 
    LIMIT 1
")->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Heritage Stories - KalaSetuGram</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    
    <style>
        .heritage-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 80px 0;
            margin-top: -76px;
            padding-top: 156px;
            position: relative;
            overflow: hidden;
        }
        
        .heritage-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('assets/images/pattern-overlay.png') repeat;
            opacity: 0.1;
        }
        
        .heritage-container {
            padding: 60px 0;
        }
        
        .featured-story {
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow-heavy);
            overflow: hidden;
            margin-bottom: 60px;
            position: relative;
        }
        
        .featured-image {
            height: 400px;
            background-size: cover;
            background-position: center;
            position: relative;
        }
        
        .featured-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            color: white;
            padding: 40px;
        }
        
        .featured-badge {
            position: absolute;
            top: 20px;
            left: 20px;
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .story-filters {
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow-light);
            padding: 25px;
            margin-bottom: 40px;
        }
        
        .story-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }
        
        .story-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow-light);
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .story-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-heavy);
        }
        
        .story-image {
            height: 200px;
            background-size: cover;
            background-position: center;
            position: relative;
        }
        
        .story-category {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(212, 165, 116, 0.9);
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .story-content {
            padding: 25px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .story-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 10px;
            line-height: 1.3;
        }
        
        .story-meta {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        .story-excerpt {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
            flex: 1;
        }
        
        .story-footer {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-top: auto;
        }
        
        .read-more-btn {
            background: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
        }
        
        .read-more-btn:hover {
            background: var(--secondary-color);
            color: white;
            transform: translateX(5px);
        }
        
        .story-stats {
            color: #999;
            font-size: 0.8rem;
        }
        
        .no-stories {
            text-align: center;
            padding: 80px 20px;
            color: #666;
        }
        
        .no-stories i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .pagination-wrapper {
            display: flex;
            justify-content: center;
            margin-top: 50px;
        }
        
        .heritage-intro {
            background: var(--accent-color);
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            margin-bottom: 50px;
        }
        
        @media (max-width: 768px) {
            .story-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .featured-image {
                height: 250px;
            }
            
            .featured-overlay {
                padding: 25px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>
    
    <!-- Heritage Header -->
    <section class="heritage-header">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-4 fw-bold mb-4">Heritage Stories</h1>
                    <p class="lead mb-0">Discover the rich cultural narratives behind Andhra Pradesh's traditional crafts</p>
                </div>
            </div>
        </div>
    </section>
    
    <div class="container heritage-container">
        <!-- Heritage Introduction -->
        <div class="heritage-intro">
            <h3 class="mb-3">
                <i class="fas fa-book-open text-primary me-3"></i>
                Preserving Cultural Legacy
            </h3>
            <p class="lead mb-0">
                Each craft carries within it centuries of tradition, passed down through generations of skilled artisans. 
                These stories connect us to our roots and help preserve the cultural heritage of Andhra Pradesh for future generations.
            </p>
        </div>
        
        <!-- Featured Story -->
        <?php if ($featuredStory): ?>
        <div class="featured-story">
            <div class="featured-image" style="background-image: url('<?php echo $featuredStory['image_url'] ? htmlspecialchars($featuredStory['image_url']) : 'https://via.placeholder.com/800x400/d4a574/ffffff?text=Heritage+Story'; ?>');">
                <div class="featured-badge">
                    <i class="fas fa-star me-1"></i>
                    Featured Story
                </div>
                <div class="featured-overlay">
                    <div class="row align-items-end">
                        <div class="col-lg-8">
                            <h2 class="mb-3"><?php echo htmlspecialchars($featuredStory['title']); ?></h2>
                            <p class="mb-3"><?php echo htmlspecialchars(substr($featuredStory['content'], 0, 200)) . '...'; ?></p>
                            <div class="d-flex align-items-center mb-3">
                                <?php if ($featuredStory['village']): ?>
                                <span class="me-4">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?php echo htmlspecialchars($featuredStory['village']); ?>, <?php echo htmlspecialchars($featuredStory['district']); ?>
                                </span>
                                <?php endif; ?>
                                <?php if ($featuredStory['category_name']): ?>
                                <span>
                                    <i class="fas fa-tag me-1"></i>
                                    <?php echo htmlspecialchars($featuredStory['category_name']); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <a href="story-detail.php?id=<?php echo $featuredStory['id']; ?>" class="btn btn-light btn-lg">
                                <i class="fas fa-book-reader me-2"></i>
                                Read Full Story
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Filters -->
        <div class="story-filters">
            <form method="GET" action="">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Filter by Category:</label>
                        <select name="category" class="form-select" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['name']); ?>" 
                                    <?php echo $category === $cat['name'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Filter by District:</label>
                        <select name="district" class="form-select" onchange="this.form.submit()">
                            <option value="">All Districts</option>
                            <?php foreach ($districts as $dist): ?>
                            <option value="<?php echo htmlspecialchars($dist); ?>" 
                                    <?php echo $district === $dist ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dist); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-2"></i>Apply Filters
                            </button>
                            <a href="heritage-stories.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Results Info -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>Heritage Stories</h4>
            <span class="text-muted">
                Showing <?php echo count($stories); ?> of <?php echo $totalStories; ?> stories
            </span>
        </div>
        
        <!-- Stories Grid -->
        <?php if (empty($stories)): ?>
            <div class="no-stories">
                <i class="fas fa-book-open"></i>
                <h4>No stories found</h4>
                <p>Try adjusting your filters or browse all heritage stories.</p>
                <a href="heritage-stories.php" class="btn btn-primary">View All Stories</a>
            </div>
        <?php else: ?>
            <div class="story-grid">
                <?php foreach ($stories as $story): ?>
                <div class="story-card">
                    <div class="story-image" style="background-image: url('<?php echo $story['image_url'] ? htmlspecialchars($story['image_url']) : 'https://via.placeholder.com/350x200/d4a574/ffffff?text=Heritage+Story'; ?>');">
                        <?php if ($story['category_name']): ?>
                        <div class="story-category"><?php echo htmlspecialchars($story['category_name']); ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="story-content">
                        <h5 class="story-title"><?php echo htmlspecialchars($story['title']); ?></h5>
                        
                        <div class="story-meta">
                            <?php if ($story['village'] && $story['district']): ?>
                            <i class="fas fa-map-marker-alt me-1"></i>
                            <?php echo htmlspecialchars($story['village']); ?>, <?php echo htmlspecialchars($story['district']); ?>
                            <?php endif; ?>
                            
                            <?php if ($story['author']): ?>
                            <span class="ms-3">
                                <i class="fas fa-user me-1"></i>
                                <?php echo htmlspecialchars($story['author']); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="story-excerpt">
                            <?php echo htmlspecialchars(substr($story['content'], 0, 150)) . '...'; ?>
                        </div>
                        
                        <div class="story-footer">
                            <a href="story-detail.php?id=<?php echo $story['id']; ?>" class="read-more-btn">
                                Read More <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                            
                            <div class="story-stats">
                                <i class="fas fa-calendar-alt me-1"></i>
                                <?php echo date('M j, Y', strtotime($story['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="pagination-wrapper">
                <nav>
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
    
    <script>
        // Initialize animations on scroll
        document.addEventListener('DOMContentLoaded', function() {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);
            
            // Observe story cards for animation
            document.querySelectorAll('.story-card').forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                card.style.transition = `all 0.6s ease ${index * 0.1}s`;
                observer.observe(card);
            });
        });
    </script>
</body>
</html>
