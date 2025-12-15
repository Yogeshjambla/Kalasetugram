<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get craft ID from URL
$craftId = intval($_GET['id'] ?? 0);

if (!$craftId) {
    header('Location: crafts.php');
    exit;
}

// Get craft details
$craft = getCraftById($craftId);
if (!$craft || !$craft['ar_model_url']) {
    header('Location: craft-detail.php?id=' . $craftId);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AR View - <?php echo htmlspecialchars($craft['title']); ?> - KalaSetuGram</title>
    
    <!-- A-Frame and AR.js -->
    <script src="https://aframe.io/releases/1.4.0/aframe.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/AR-js-org/AR.js/aframe/build/aframe-ar.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/donmccurdy/aframe-extras@v6.1.1/dist/aframe-extras.min.js"></script>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            overflow: hidden;
        }
        
        .ar-container {
            position: relative;
            width: 100vw;
            height: 100vh;
        }
        
        .ar-ui {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: linear-gradient(180deg, rgba(0,0,0,0.8) 0%, transparent 100%);
            color: white;
            padding: 20px;
        }
        
        .ar-controls {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: linear-gradient(0deg, rgba(0,0,0,0.8) 0%, transparent 100%);
            color: white;
            padding: 20px;
        }
        
        .control-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 12px 20px;
            border-radius: 25px;
            margin: 5px;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        
        .control-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            color: white;
        }
        
        .ar-info {
            background: rgba(0, 0, 0, 0.7);
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 15px;
            backdrop-filter: blur(10px);
        }
        
        .loading-screen {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: #000;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }
        
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .ar-instructions {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: white;
            background: rgba(0, 0, 0, 0.8);
            padding: 30px;
            border-radius: 15px;
            z-index: 1500;
            max-width: 90%;
        }
        
        .marker-not-found {
            display: none;
        }
        
        @media (max-width: 768px) {
            .ar-ui, .ar-controls {
                padding: 15px;
            }
            
            .control-btn {
                padding: 10px 15px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="ar-container">
        <!-- Loading Screen -->
        <div class="loading-screen" id="loadingScreen">
            <div class="loading-spinner"></div>
            <h4>Initializing AR Experience</h4>
            <p>Please allow camera access and wait for the AR environment to load...</p>
        </div>
        
        <!-- AR Scene -->
        <a-scene
            id="arScene"
            embedded
            arjs="sourceType: webcam; debugUIEnabled: false; detectionMode: mono_and_matrix; matrixCodeType: 3x3;"
            vr-mode-ui="enabled: false"
            renderer="logarithmicDepthBuffer: true;"
            loading-screen="enabled: false">
            
            <!-- Assets -->
            <a-assets>
                <a-asset-item id="craftModel" src="<?php echo htmlspecialchars($craft['ar_model_url']); ?>"></a-asset-item>
            </a-assets>
            
            <!-- Marker-based AR -->
            <a-marker 
                id="mainMarker"
                preset="hiro" 
                raycaster="objects: .clickable" 
                emitevents="true" 
                cursor="fuse: false; rayOrigin: mouse;"
                markerhandler>
                
                <!-- 3D Model -->
                <a-entity
                    id="craftEntity"
                    gltf-model="#craftModel"
                    scale="0.5 0.5 0.5"
                    position="0 0 0"
                    rotation="0 0 0"
                    animation-mixer="loop: repeat"
                    class="clickable">
                </a-entity>
                
                <!-- Lighting -->
                <a-light type="ambient" color="#404040"></a-light>
                <a-light type="directional" position="1 1 1" color="#ffffff" intensity="0.5"></a-light>
            </a-marker>
            
            <!-- Markerless AR (fallback) -->
            <a-entity
                id="markerlessEntity"
                gltf-model="#craftModel"
                scale="0.1 0.1 0.1"
                position="0 -1 -3"
                rotation="0 0 0"
                visible="false"
                animation="property: rotation; to: 0 360 0; loop: true; dur: 10000">
            </a-entity>
            
            <!-- Camera -->
            <a-entity camera look-controls-enabled="false"></a-entity>
        </a-scene>
        
        <!-- AR UI Header -->
        <div class="ar-ui">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1"><?php echo htmlspecialchars($craft['title']); ?></h5>
                    <small>AR Experience</small>
                </div>
                <div>
                    <button class="control-btn" onclick="exitAR()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <div class="ar-info" id="arStatus">
                <i class="fas fa-camera me-2"></i>
                <span id="statusText">Looking for AR marker...</span>
            </div>
        </div>
        
        <!-- AR Instructions -->
        <div class="ar-instructions" id="arInstructions">
            <h5><i class="fas fa-info-circle me-2"></i>How to use AR</h5>
            <p class="mb-3">Point your camera at the AR marker (Hiro marker) to view the 3D model of this craft.</p>
            <div class="mb-3">
                <img src="https://github.com/AR-js-org/AR.js/blob/master/data/images/hiro.png?raw=true" 
                     alt="Hiro Marker" style="width: 100px; height: 100px; border: 2px solid white;">
            </div>
            <p class="small mb-3">Don't have a marker? <button class="btn btn-sm btn-primary" onclick="enableMarkerless()">Try Markerless AR</button></p>
            <button class="btn btn-outline-light btn-sm" onclick="hideInstructions()">Got it!</button>
        </div>
        
        <!-- AR Controls -->
        <div class="ar-controls">
            <div class="d-flex justify-content-center flex-wrap">
                <button class="control-btn" onclick="resetModel()">
                    <i class="fas fa-redo me-2"></i>Reset
                </button>
                
                <button class="control-btn" onclick="scaleUp()">
                    <i class="fas fa-search-plus me-2"></i>Zoom In
                </button>
                
                <button class="control-btn" onclick="scaleDown()">
                    <i class="fas fa-search-minus me-2"></i>Zoom Out
                </button>
                
                <button class="control-btn" onclick="rotateModel()">
                    <i class="fas fa-sync-alt me-2"></i>Rotate
                </button>
                
                <button class="control-btn" onclick="takeScreenshot()">
                    <i class="fas fa-camera me-2"></i>Capture
                </button>
                
                <button class="control-btn" onclick="shareAR()">
                    <i class="fas fa-share-alt me-2"></i>Share
                </button>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let currentScale = 0.5;
        let currentRotation = 0;
        let isMarkerlessMode = false;
        let arInitialized = false;
        
        // Initialize AR
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                document.getElementById('loadingScreen').style.display = 'none';
                arInitialized = true;
            }, 3000);
        });
        
        // Marker detection events
        AFRAME.registerComponent('markerhandler', {
            init: function () {
                const marker = this.el;
                
                marker.addEventListener('markerFound', function () {
                    console.log('Marker found');
                    document.getElementById('statusText').innerHTML = '<i class="fas fa-check-circle me-2"></i>AR marker detected! Model is now visible.';
                    document.getElementById('arStatus').style.background = 'rgba(40, 167, 69, 0.8)';
                    hideInstructions();
                });
                
                marker.addEventListener('markerLost', function () {
                    console.log('Marker lost');
                    document.getElementById('statusText').innerHTML = '<i class="fas fa-search me-2"></i>Looking for AR marker...';
                    document.getElementById('arStatus').style.background = 'rgba(0, 0, 0, 0.7)';
                });
            }
        });
        
        // Control functions
        function exitAR() {
            window.history.back();
        }
        
        function hideInstructions() {
            document.getElementById('arInstructions').style.display = 'none';
        }
        
        function enableMarkerless() {
            isMarkerlessMode = true;
            document.getElementById('mainMarker').setAttribute('visible', 'false');
            document.getElementById('markerlessEntity').setAttribute('visible', 'true');
            document.getElementById('statusText').innerHTML = '<i class="fas fa-cube me-2"></i>Markerless AR mode enabled';
            document.getElementById('arStatus').style.background = 'rgba(23, 162, 184, 0.8)';
            hideInstructions();
        }
        
        function resetModel() {
            currentScale = 0.5;
            currentRotation = 0;
            const entity = isMarkerlessMode ? 
                document.getElementById('markerlessEntity') : 
                document.getElementById('craftEntity');
            
            entity.setAttribute('scale', `${currentScale} ${currentScale} ${currentScale}`);
            entity.setAttribute('rotation', `0 ${currentRotation} 0`);
            
            showNotification('Model reset to original position', 'info');
        }
        
        function scaleUp() {
            currentScale = Math.min(currentScale * 1.2, 2.0);
            const entity = isMarkerlessMode ? 
                document.getElementById('markerlessEntity') : 
                document.getElementById('craftEntity');
            
            entity.setAttribute('scale', `${currentScale} ${currentScale} ${currentScale}`);
            showNotification('Model scaled up', 'success');
        }
        
        function scaleDown() {
            currentScale = Math.max(currentScale * 0.8, 0.1);
            const entity = isMarkerlessMode ? 
                document.getElementById('markerlessEntity') : 
                document.getElementById('craftEntity');
            
            entity.setAttribute('scale', `${currentScale} ${currentScale} ${currentScale}`);
            showNotification('Model scaled down', 'success');
        }
        
        function rotateModel() {
            currentRotation += 45;
            const entity = isMarkerlessMode ? 
                document.getElementById('markerlessEntity') : 
                document.getElementById('craftEntity');
            
            entity.setAttribute('rotation', `0 ${currentRotation} 0`);
            showNotification('Model rotated', 'success');
        }
        
        function takeScreenshot() {
            const scene = document.getElementById('arScene');
            const canvas = scene.canvas;
            
            // Create download link
            canvas.toBlob(function(blob) {
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `kalasetugramdb-ar-${Date.now()}.png`;
                a.click();
                URL.revokeObjectURL(url);
                
                showNotification('Screenshot saved!', 'success');
            });
        }
        
        function shareAR() {
            if (navigator.share) {
                navigator.share({
                    title: 'Check out this AR craft experience!',
                    text: `I'm viewing "${<?php echo json_encode($craft['title']); ?>}" in AR on KalaSetuGram`,
                    url: window.location.href
                });
            } else {
                // Fallback to copying URL
                navigator.clipboard.writeText(window.location.href);
                showNotification('AR experience link copied to clipboard!', 'success');
            }
        }
        
        // Touch gestures for mobile
        let startTouchDistance = 0;
        let startScale = currentScale;
        
        document.addEventListener('touchstart', function(e) {
            if (e.touches.length === 2) {
                startTouchDistance = getTouchDistance(e.touches[0], e.touches[1]);
                startScale = currentScale;
            }
        });
        
        document.addEventListener('touchmove', function(e) {
            if (e.touches.length === 2) {
                e.preventDefault();
                const currentDistance = getTouchDistance(e.touches[0], e.touches[1]);
                const scaleFactor = currentDistance / startTouchDistance;
                currentScale = Math.max(0.1, Math.min(2.0, startScale * scaleFactor));
                
                const entity = isMarkerlessMode ? 
                    document.getElementById('markerlessEntity') : 
                    document.getElementById('craftEntity');
                
                entity.setAttribute('scale', `${currentScale} ${currentScale} ${currentScale}`);
            }
        });
        
        function getTouchDistance(touch1, touch2) {
            const dx = touch1.clientX - touch2.clientX;
            const dy = touch1.clientY - touch2.clientY;
            return Math.sqrt(dx * dx + dy * dy);
        }
        
        // Notification system
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} position-fixed`;
            notification.style.cssText = `
                top: 100px;
                right: 20px;
                z-index: 3000;
                min-width: 250px;
                animation: slideIn 0.3s ease-out;
            `;
            notification.innerHTML = `
                <i class="fas fa-info-circle me-2"></i>
                ${message}
                <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 3000);
        }
        
        // Error handling
        window.addEventListener('error', function(e) {
            console.error('AR Error:', e);
            showNotification('AR initialization error. Please check camera permissions.', 'warning');
        });
        
        // Camera permission check
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(function(stream) {
                console.log('Camera access granted');
                stream.getTracks().forEach(track => track.stop());
            })
            .catch(function(err) {
                console.error('Camera access denied:', err);
                showNotification('Camera access required for AR experience', 'warning');
            });
    </script>
    
    <style>
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
</body>
</html>
