<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-md fixed-top border-bottom">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="images/kalasetugramlogo.png" alt="KalaSetuGram" height="45" width="45" class="d-inline-block align-text-top rounded-circle bg-white p-1" style="object-fit: cover;" 
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
            <span class="brand-text ms-2" style="display: inline;">KalaSetuGram</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="crafts.php">Crafts</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="artisans.php">Artisans</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="heritage-stories.php">Heritage Stories</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="adopt-artisan.php">Adopt an Artisan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contact.php">Contact</a>
                </li>
            </ul>
            
            <!-- Search Bar -->
            <form class="d-flex me-3" action="crafts.php" method="GET">
                <div class="input-group">
                    <input class="form-control" type="search" name="search" placeholder="Search crafts..." 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button class="btn btn-outline-primary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
            
            <ul class="navbar-nav">
                <?php if (isLoggedIn()): ?>
                    <!-- Cart Icon -->
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="cart.php">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cart-count">
                                <?php 
                                $cartItems = getCartItems($_SESSION['user_id']);
                                echo count($cartItems);
                                ?>
                            </span>
                        </a>
                    </li>
                    
                    <!-- User Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="orders.php"><i class="fas fa-box me-2"></i>My Orders</a></li>
                            <?php if ($_SESSION['user_role'] === 'artisan'): ?>
                                <li><a class="dropdown-item" href="artisan-dashboard.php"><i class="fas fa-palette me-2"></i>Artisan Dashboard</a></li>
                            <?php endif; ?>
                            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="admin/dashboard.php"><i class="fas fa-cog me-2"></i>Admin Dashboard</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="auth/login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary ms-2" href="auth/register.php">Sign Up</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Add some top padding to body to account for fixed navbar -->
<style>
body {
    padding-top: 76px;
}

.navbar-brand .brand-text {
    font-weight: 700;
    background: linear-gradient(45deg, #E4405F, #F77737);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-size: 1.5rem;
}

.navbar {
    border-bottom: 1px solid #DBDBDB;
}

.navbar-nav .nav-link {
    font-weight: 500;
    color: #262626 !important;
    transition: color 0.3s ease;
}

.navbar-nav .nav-link:hover {
    color: #E4405F !important;
}

.btn-primary {
    background: linear-gradient(45deg, #833AB4, #E4405F, #F77737, #FCAF45);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(45deg, #6B2D8A, #C7364F, #E66B2F, #E09A3A);
    border: none;
    transform: translateY(-1px);
}

.btn-outline-primary {
    color: #E4405F;
    border-color: #E4405F;
}

.btn-outline-primary:hover {
    background-color: #E4405F;
    border-color: #E4405F;
}

#cart-count {
    font-size: 0.7rem;
}
</style>
