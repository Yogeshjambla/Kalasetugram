// KalaSetuGram Main JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initializeNavbar();
    initializeAnimations();
    initializeCarousels();
    initializeTooltips();
    initializeModals();
    initializeCart();
    initializeSearch();
    initializeHeritageMap();
    
    // Initialize AOS (Animate On Scroll) if available
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 100
        });
    }
});

// Navbar functionality
function initializeNavbar() {
    const navbar = document.querySelector('.navbar');
    
    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
    
    // Mobile menu toggle
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    
    if (navbarToggler) {
        navbarToggler.addEventListener('click', function() {
            navbarCollapse.classList.toggle('show');
        });
    }
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!navbar.contains(e.target) && navbarCollapse.classList.contains('show')) {
            navbarCollapse.classList.remove('show');
        }
    });
}

// Animation utilities
function initializeAnimations() {
    // Intersection Observer for scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fade-up');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // Observe elements for animation
    document.querySelectorAll('.craft-card, .artisan-card, .testimonial-card').forEach(el => {
        observer.observe(el);
    });
}

// Carousel initialization
function initializeCarousels() {
    // Initialize Bootstrap carousels
    const carousels = document.querySelectorAll('.carousel');
    carousels.forEach(carousel => {
        new bootstrap.Carousel(carousel, {
            interval: 5000,
            wrap: true
        });
    });
}

// Tooltip initialization
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Modal initialization
function initializeModals() {
    // AR View Modal
    const arButtons = document.querySelectorAll('.ar-view-btn');
    arButtons.forEach(button => {
        button.addEventListener('click', function() {
            const craftId = this.dataset.craftId;
            openARView(craftId);
        });
    });
    
    // Quick View Modal
    const quickViewButtons = document.querySelectorAll('.quick-view-btn');
    quickViewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const craftId = this.dataset.craftId;
            openQuickView(craftId);
        });
    });
}

// Cart functionality
function initializeCart() {
    // Add to cart buttons
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const craftId = this.dataset.craftId;
            const quantity = this.dataset.quantity || 1;
            addToCart(craftId, quantity);
        });
    });
    
    // Update cart count on page load
    updateCartCount();
}

// Search functionality
function initializeSearch() {
    const searchInput = document.querySelector('input[name="search"]');
    const searchSuggestions = document.querySelector('.search-suggestions');
    
    if (searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length >= 2) {
                searchTimeout = setTimeout(() => {
                    fetchSearchSuggestions(query);
                }, 300);
            } else {
                hideSearchSuggestions();
            }
        });
        
        // Hide suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchSuggestions?.contains(e.target)) {
                hideSearchSuggestions();
            }
        });
    }
}

// Heritage Map initialization
function initializeHeritageMap() {
    const mapContainer = document.getElementById('heritage-map');
    if (mapContainer) {
        // Initialize interactive map (placeholder for now)
        loadHeritageMap();
    }
}

// Cart functions
async function addToCart(craftId, quantity = 1) {
    try {
        showLoading();
        
        const response = await fetch('api/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add',
                craft_id: craftId,
                quantity: quantity
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Item added to cart!', 'success');
            updateCartCount();
            
            // Add visual feedback
            const button = document.querySelector(`[data-craft-id="${craftId}"]`);
            if (button) {
                button.classList.add('btn-success');
                button.innerHTML = '<i class="fas fa-check"></i> Added!';
                setTimeout(() => {
                    button.classList.remove('btn-success');
                    button.innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart';
                }, 2000);
            }
        } else {
            showNotification(result.message || 'Failed to add item to cart', 'error');
        }
    } catch (error) {
        console.error('Error adding to cart:', error);
        showNotification('An error occurred. Please try again.', 'error');
    } finally {
        hideLoading();
    }
}

async function updateCartCount() {
    try {
        const response = await fetch('api/cart.php?action=count');
        const result = await response.json();
        
        const cartCountElement = document.getElementById('cart-count');
        if (cartCountElement && result.success) {
            cartCountElement.textContent = result.count;
            
            // Animate count update
            cartCountElement.classList.add('animate__animated', 'animate__pulse');
            setTimeout(() => {
                cartCountElement.classList.remove('animate__animated', 'animate__pulse');
            }, 1000);
        }
    } catch (error) {
        console.error('Error updating cart count:', error);
    }
}

// Search functions
async function fetchSearchSuggestions(query) {
    try {
        const response = await fetch(`api/search.php?q=${encodeURIComponent(query)}&suggestions=true`);
        const result = await response.json();
        
        if (result.success && result.suggestions.length > 0) {
            showSearchSuggestions(result.suggestions);
        } else {
            hideSearchSuggestions();
        }
    } catch (error) {
        console.error('Error fetching search suggestions:', error);
    }
}

function showSearchSuggestions(suggestions) {
    let suggestionsContainer = document.querySelector('.search-suggestions');
    
    if (!suggestionsContainer) {
        suggestionsContainer = document.createElement('div');
        suggestionsContainer.className = 'search-suggestions';
        document.querySelector('.navbar .input-group').appendChild(suggestionsContainer);
    }
    
    const suggestionsHTML = suggestions.map(suggestion => 
        `<div class="suggestion-item" data-query="${suggestion.query}">
            <i class="fas fa-search me-2"></i>
            ${suggestion.text}
        </div>`
    ).join('');
    
    suggestionsContainer.innerHTML = suggestionsHTML;
    suggestionsContainer.style.display = 'block';
    
    // Add click handlers
    suggestionsContainer.querySelectorAll('.suggestion-item').forEach(item => {
        item.addEventListener('click', function() {
            const query = this.dataset.query;
            document.querySelector('input[name="search"]').value = query;
            hideSearchSuggestions();
            performSearch(query);
        });
    });
}

function hideSearchSuggestions() {
    const suggestionsContainer = document.querySelector('.search-suggestions');
    if (suggestionsContainer) {
        suggestionsContainer.style.display = 'none';
    }
}

function performSearch(query) {
    window.location.href = `crafts.php?search=${encodeURIComponent(query)}`;
}

// AR and 3D functions
function openARView(craftId) {
    // Check if device supports AR
    if ('xr' in navigator) {
        navigator.xr.isSessionSupported('immersive-ar').then(supported => {
            if (supported) {
                launchARExperience(craftId);
            } else {
                show3DView(craftId);
            }
        });
    } else {
        show3DView(craftId);
    }
}

function launchARExperience(craftId) {
    // Launch AR experience using AR.js or WebXR
    const arModal = new bootstrap.Modal(document.getElementById('arModal'));
    
    // Load AR scene
    fetch(`api/crafts.php?id=${craftId}&ar=true`)
        .then(response => response.json())
        .then(data => {
            if (data.ar_model_url) {
                setupARScene(data.ar_model_url);
                arModal.show();
            } else {
                showNotification('AR model not available for this craft', 'info');
            }
        })
        .catch(error => {
            console.error('Error loading AR model:', error);
            showNotification('Failed to load AR experience', 'error');
        });
}

function show3DView(craftId) {
    // Fallback to 3D model viewer
    const modal = new bootstrap.Modal(document.getElementById('3dModal'));
    
    fetch(`api/crafts.php?id=${craftId}&model=true`)
        .then(response => response.json())
        .then(data => {
            if (data.model_url) {
                setup3DViewer(data.model_url);
                modal.show();
            } else {
                showNotification('3D model not available for this craft', 'info');
            }
        })
        .catch(error => {
            console.error('Error loading 3D model:', error);
            showNotification('Failed to load 3D model', 'error');
        });
}

function setupARScene(modelUrl) {
    const arScene = document.querySelector('#ar-scene');
    if (arScene) {
        arScene.innerHTML = `
            <a-marker preset="hiro">
                <a-entity
                    gltf-model="${modelUrl}"
                    scale="0.5 0.5 0.5"
                    animation="property: rotation; to: 0 360 0; loop: true; dur: 10000">
                </a-entity>
            </a-marker>
        `;
    }
}

function setup3DViewer(modelUrl) {
    const viewer = document.querySelector('#3d-viewer');
    if (viewer) {
        viewer.innerHTML = `
            <model-viewer
                src="${modelUrl}"
                alt="3D Craft Model"
                auto-rotate
                camera-controls
                background-color="#f0f0f0">
            </model-viewer>
        `;
    }
}

// Heritage Map functions
function loadHeritageMap() {
    const mapContainer = document.getElementById('heritage-map');
    
    // For now, create a placeholder interactive map
    // In production, integrate with Google Maps or Mapbox
    mapContainer.innerHTML = `
        <div class="heritage-map-interactive">
            <div class="map-region" data-region="krishna" style="top: 20%; left: 30%;">
                <div class="map-pin">
                    <i class="fas fa-map-marker-alt"></i>
                    <div class="map-tooltip">
                        <h6>Krishna District</h6>
                        <p>Home of Kondapalli Toys</p>
                    </div>
                </div>
            </div>
            <div class="map-region" data-region="guntur" style="top: 40%; left: 25%;">
                <div class="map-pin">
                    <i class="fas fa-map-marker-alt"></i>
                    <div class="map-tooltip">
                        <h6>Guntur District</h6>
                        <p>Famous for Kalamkari Art</p>
                    </div>
                </div>
            </div>
            <div class="map-region" data-region="nalgonda" style="top: 30%; left: 50%;">
                <div class="map-pin">
                    <i class="fas fa-map-marker-alt"></i>
                    <div class="map-tooltip">
                        <h6>Nalgonda District</h6>
                        <p>Pochampally Ikat Weaving</p>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add interactivity to map pins
    const mapPins = mapContainer.querySelectorAll('.map-pin');
    mapPins.forEach(pin => {
        pin.addEventListener('click', function() {
            const region = this.closest('.map-region').dataset.region;
            showRegionDetails(region);
        });
    });
}

function showRegionDetails(region) {
    // Show modal with region details
    const regionData = {
        krishna: {
            name: 'Krishna District',
            crafts: ['Kondapalli Toys'],
            description: 'Known for colorful wooden toys and traditional craftsmanship.',
            artisans: 45
        },
        guntur: {
            name: 'Guntur District',
            crafts: ['Kalamkari'],
            description: 'Famous for hand-painted textiles using natural dyes.',
            artisans: 32
        },
        nalgonda: {
            name: 'Nalgonda District',
            crafts: ['Pochampally Ikat'],
            description: 'Traditional tie-dye textile art with geometric patterns.',
            artisans: 28
        }
    };
    
    const data = regionData[region];
    if (data) {
        showNotification(`${data.name}: ${data.description} (${data.artisans} active artisans)`, 'info');
    }
}

// Utility functions
function showLoading() {
    const loader = document.querySelector('.loading-overlay');
    if (loader) {
        loader.style.display = 'flex';
    }
}

function hideLoading() {
    const loader = document.querySelector('.loading-overlay');
    if (loader) {
        loader.style.display = 'none';
    }
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${getNotificationIcon(type)} me-2"></i>
            ${message}
            <button class="btn-close" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
    `;
    
    // Add to page
    let container = document.querySelector('.notification-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'notification-container';
        document.body.appendChild(container);
    }
    
    container.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

function getNotificationIcon(type) {
    const icons = {
        success: 'check-circle',
        error: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    return icons[type] || 'info-circle';
}

// Form validation utilities
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePhone(phone) {
    const re = /^[+]?[\d\s\-\(\)]{10,}$/;
    return re.test(phone);
}

// Price formatting
function formatPrice(price) {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR'
    }).format(price);
}

// Image lazy loading
function initializeLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
}

// Initialize lazy loading when DOM is ready
document.addEventListener('DOMContentLoaded', initializeLazyLoading);

// Export functions for global use
window.KalaSetuGram = {
    addToCart,
    updateCartCount,
    showNotification,
    openARView,
    formatPrice,
    validateEmail,
    validatePhone
};
