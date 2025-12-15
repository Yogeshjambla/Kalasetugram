<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in and is an artisan
if (!isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

$pdo = getConnection();
$userId = $_SESSION['user_id'];

// Get artisan profile
$artisan = $pdo->prepare("SELECT * FROM artisans WHERE user_id = ?");
$artisan->execute([$userId]);
$artisan = $artisan->fetch();

if (!$artisan) {
    // Create artisan profile if doesn't exist
    $pdo->prepare("INSERT INTO artisans (user_id, craft_type, district) VALUES (?, 'General', 'Not specified')")
        ->execute([$userId]);
    
    $artisan = $pdo->prepare("SELECT * FROM artisans WHERE user_id = ?");
    $artisan->execute([$userId]);
    $artisan = $artisan->fetch();
}

// Get all categories
$categories = $pdo->query("SELECT * FROM craft_categories ORDER BY name")->fetchAll();

$message = '';
$error = '';

// Handle form submission
if ($_POST) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $story = trim($_POST['story'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $categoryId = intval($_POST['category_id'] ?? 0);
    $material = trim($_POST['material'] ?? '');
    $dimensions = trim($_POST['dimensions'] ?? '');
    $weight = trim($_POST['weight'] ?? '');
    $stockQuantity = intval($_POST['stock_quantity'] ?? 1);
    $status = $_POST['status'] ?? 'active';
    
    // Validation
    if (empty($title) || empty($description) || $price <= 0 || $categoryId <= 0) {
        $error = 'Please fill in all required fields correctly.';
    } else {
        try {
            // Insert craft
            $stmt = $pdo->prepare("
                INSERT INTO crafts (artisan_id, category_id, title, description, story, price, material, dimensions, weight, stock_quantity, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([$artisan['id'], $categoryId, $title, $description, $story, $price, $material, $dimensions, $weight, $stockQuantity, $status])) {
                $craftId = $pdo->lastInsertId();
                
                // Handle image uploads
                if (!empty($_FILES['images']['name'][0])) {
                    $uploadDir = 'assets/images/crafts/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    $isPrimary = true;
                    foreach ($_FILES['images']['name'] as $key => $filename) {
                        if (!empty($filename)) {
                            $fileExtension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                            
                            if (in_array($fileExtension, $allowedExtensions)) {
                                $newFilename = 'craft_' . $craftId . '_' . $key . '_' . time() . '.' . $fileExtension;
                                $uploadPath = $uploadDir . $newFilename;
                                
                                if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $uploadPath)) {
                                    // Insert image record
                                    $imgStmt = $pdo->prepare("INSERT INTO craft_images (craft_id, image_url, is_primary, alt_text) VALUES (?, ?, ?, ?)");
                                    $imgStmt->execute([$craftId, $uploadPath, $isPrimary, $title]);
                                    $isPrimary = false; // Only first image is primary
                                }
                            }
                        }
                    }
                }
                
                $message = 'Craft added successfully!';
                // Clear form data
                $_POST = [];
            } else {
                $error = 'Failed to add craft. Please try again.';
            }
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Craft - KalaSetuGram</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    
    <style>
        .page-header {
            background: linear-gradient(135deg, #d4a574, #8b4513);
            color: white;
            padding: 60px 0;
            margin-top: -76px;
            padding-top: 136px;
        }
        
        .form-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 40px;
            margin: -50px auto 50px;
            position: relative;
            z-index: 2;
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .section-title {
            color: #8b4513;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #d4a574;
        }
        
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        
        .form-control, .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #d4a574;
            box-shadow: 0 0 0 0.2rem rgba(212, 165, 116, 0.25);
        }
        
        .image-upload-area {
            border: 2px dashed #d4a574;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            background: #fafafa;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .image-upload-area:hover {
            background: #f0f0f0;
            border-color: #8b4513;
        }
        
        .upload-icon {
            font-size: 3rem;
            color: #d4a574;
            margin-bottom: 15px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #d4a574, #8b4513);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(212, 165, 116, 0.4);
        }
        
        .preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 15px;
        }
        
        .preview-item {
            position: relative;
            width: 100px;
            height: 100px;
            border-radius: 10px;
            overflow: hidden;
            border: 2px solid #e0e0e0;
        }
        
        .preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .remove-preview {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(255,0,0,0.8);
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            font-size: 12px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>
    
    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-5 fw-bold mb-3">Add New Craft</h1>
                    <p class="lead mb-0">Share your beautiful creations with the world</p>
                </div>
            </div>
        </div>
    </section>
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="form-container">
                    <!-- Alert Messages -->
                    <?php if ($message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <!-- Basic Information -->
                        <div class="form-section">
                            <h4 class="section-title">
                                <i class="fas fa-info-circle me-2"></i>
                                Basic Information
                            </h4>
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Craft Title *</label>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" 
                                               placeholder="Enter craft title" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="category_id" class="form-label">Category *</label>
                                        <select class="form-select" id="category_id" name="category_id" required>
                                            <option value="">Select Category</option>
                                            <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>" 
                                                    <?php echo (($_POST['category_id'] ?? '') == $category['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                                <?php if ($category['gi_tagged']): ?>
                                                    <span class="badge bg-success ms-1">GI Tagged</span>
                                                <?php endif; ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description *</label>
                                <textarea class="form-control" id="description" name="description" rows="4" 
                                          placeholder="Describe your craft, its features, and what makes it special" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="story" class="form-label">Craft Story</label>
                                <textarea class="form-control" id="story" name="story" rows="3" 
                                          placeholder="Share the story behind this craft, its cultural significance, or inspiration"><?php echo htmlspecialchars($_POST['story'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <!-- Pricing & Details -->
                        <div class="form-section">
                            <h4 class="section-title">
                                <i class="fas fa-tag me-2"></i>
                                Pricing & Details
                            </h4>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="price" class="form-label">Price (₹) *</label>
                                        <input type="number" class="form-control" id="price" name="price" 
                                               value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" 
                                               placeholder="0.00" min="1" step="0.01" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="stock_quantity" class="form-label">Stock Quantity</label>
                                        <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" 
                                               value="<?php echo htmlspecialchars($_POST['stock_quantity'] ?? '1'); ?>" 
                                               placeholder="1" min="1">
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="active" <?php echo (($_POST['status'] ?? 'active') == 'active') ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo (($_POST['status'] ?? '') == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                            <option value="featured" <?php echo (($_POST['status'] ?? '') == 'featured') ? 'selected' : ''; ?>>Featured</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="material" class="form-label">Material</label>
                                        <input type="text" class="form-control" id="material" name="material" 
                                               value="<?php echo htmlspecialchars($_POST['material'] ?? ''); ?>" 
                                               placeholder="e.g., Wood, Clay, Fabric">
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="dimensions" class="form-label">Dimensions</label>
                                        <input type="text" class="form-control" id="dimensions" name="dimensions" 
                                               value="<?php echo htmlspecialchars($_POST['dimensions'] ?? ''); ?>" 
                                               placeholder="e.g., 10cm x 15cm x 5cm">
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="weight" class="form-label">Weight</label>
                                        <input type="text" class="form-control" id="weight" name="weight" 
                                               value="<?php echo htmlspecialchars($_POST['weight'] ?? ''); ?>" 
                                               placeholder="e.g., 200g, 1.5kg">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Images -->
                        <div class="form-section">
                            <h4 class="section-title">
                                <i class="fas fa-images me-2"></i>
                                Craft Images
                            </h4>
                            
                            <div class="image-upload-area" onclick="document.getElementById('images').click()">
                                <div class="upload-icon">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <h5>Click to Upload Images</h5>
                                <p class="text-muted mb-0">
                                    Upload multiple images of your craft<br>
                                    <small>Supported formats: JPG, PNG, GIF, WebP (Max 5MB each)</small>
                                </p>
                                <input type="file" id="images" name="images[]" multiple accept="image/*" style="display: none;" onchange="previewImages(this)">
                            </div>
                            
                            <div id="imagePreview" class="preview-container"></div>
                        </div>
                        
                        <!-- Submit Buttons -->
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg me-3">
                                <i class="fas fa-plus me-2"></i>
                                Add Craft
                            </button>
                            <a href="artisan-dashboard.php" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-arrow-left me-2"></i>
                                Back to Dashboard
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function previewImages(input) {
            const previewContainer = document.getElementById('imagePreview');
            previewContainer.innerHTML = '';
            
            if (input.files) {
                Array.from(input.files).forEach((file, index) => {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        const previewItem = document.createElement('div');
                        previewItem.className = 'preview-item';
                        previewItem.innerHTML = `
                            <img src="${e.target.result}" alt="Preview ${index + 1}">
                            <button type="button" class="remove-preview" onclick="removePreview(${index}, this)">
                                <i class="fas fa-times"></i>
                            </button>
                        `;
                        previewContainer.appendChild(previewItem);
                    };
                    
                    reader.readAsDataURL(file);
                });
            }
        }
        
        function removePreview(index, button) {
            const previewItem = button.closest('.preview-item');
            previewItem.remove();
            
            // Reset file input to remove the file
            const fileInput = document.getElementById('images');
            const dt = new DataTransfer();
            const files = Array.from(fileInput.files);
            
            files.forEach((file, i) => {
                if (i !== index) {
                    dt.items.add(file);
                }
            });
            
            fileInput.files = dt.files;
        }
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const description = document.getElementById('description').value.trim();
            const price = parseFloat(document.getElementById('price').value);
            const categoryId = document.getElementById('category_id').value;
            
            if (!title || !description || !price || price <= 0 || !categoryId) {
                e.preventDefault();
                alert('Please fill in all required fields correctly.');
                return false;
            }
        });
    </script>
</body>
</html>
