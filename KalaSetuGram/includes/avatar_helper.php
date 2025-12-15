<?php
/**
 * Avatar Helper Functions
 * Generates user-friendly emoji and colorful avatars for artisans and users
 */

// Array of craft-related emojis for artisans
$craftEmojis = [
    '🎨', '🖌️', '🧵', '🪡', '🏺', '🔨', '⚒️', '🪚', '🎭', '🎪',
    '🧶', '🪢', '🎯', '🎨', '🖼️', '🎪', '🎭', '🎨', '🧵', '🪡',
    '🏺', '🔨', '⚒️', '🪚', '🎯', '🧶', '🪢', '🎨', '🖌️', '🎪'
];

// Array of friendly face emojis for general users
$faceEmojis = [
    '😊', '😄', '😃', '😁', '😆', '🙂', '😇', '🤗', '😋', '😎',
    '🤓', '🧐', '😌', '😍', '🥰', '😘', '😗', '😙', '😚', '🤩',
    '🥳', '😊', '😄', '😃', '😁', '😆', '🙂', '😇', '🤗', '😋'
];

// Array of background gradient colors
$gradientColors = [
    ['#667eea', '#764ba2'], // Purple-Blue
    ['#f093fb', '#f5576c'], // Pink-Red
    ['#4facfe', '#00f2fe'], // Blue-Cyan
    ['#fa709a', '#fee140'], // Pink-Yellow
    ['#a8edea', '#fed6e3'], // Mint-Pink
    ['#ffecd2', '#fcb69f'], // Peach-Orange
    ['#ff9a9e', '#fecfef'], // Rose-Pink
    ['#a18cd1', '#fbc2eb'], // Purple-Pink
    ['#fad0c4', '#ffd1ff'], // Peach-Lavender
    ['#ffeaa7', '#fab1a0'], // Yellow-Orange
    ['#74b9ff', '#0984e3'], // Light Blue-Blue
    ['#fd79a8', '#e84393'], // Pink-Magenta
    ['#fdcb6e', '#e17055'], // Yellow-Orange
    ['#6c5ce7', '#a29bfe'], // Purple-Light Purple
    ['#00b894', '#00cec9'], // Green-Teal
];

/**
 * Generate emoji avatar for artisan
 */
function getArtisanEmojiAvatar($artisanId, $name = '') {
    global $craftEmojis;
    
    // Use artisan ID to get consistent emoji
    $emojiIndex = $artisanId % count($craftEmojis);
    return $craftEmojis[$emojiIndex];
}

/**
 * Generate emoji avatar for regular user
 */
function getUserEmojiAvatar($userId, $name = '') {
    global $faceEmojis;
    
    // Use user ID to get consistent emoji
    $emojiIndex = $userId % count($faceEmojis);
    return $faceEmojis[$emojiIndex];
}

/**
 * Generate gradient background colors
 */
function getAvatarGradient($id) {
    global $gradientColors;
    
    $colorIndex = $id % count($gradientColors);
    return $gradientColors[$colorIndex];
}

/**
 * Generate complete avatar HTML for artisan
 */
function generateArtisanAvatar($artisanId, $name, $size = 50, $fontSize = '1.5rem') {
    $emoji = getArtisanEmojiAvatar($artisanId, $name);
    $gradient = getAvatarGradient($artisanId);
    
    $html = '<div class="emoji-avatar" style="
        width: ' . $size . 'px;
        height: ' . $size . 'px;
        border-radius: 50%;
        background: linear-gradient(135deg, ' . $gradient[0] . ', ' . $gradient[1] . ');
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: ' . $fontSize . ';
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border: 3px solid white;
    " title="' . htmlspecialchars($name) . '">';
    
    $html .= $emoji;
    $html .= '</div>';
    
    return $html;
}

/**
 * Generate complete avatar HTML for user
 */
function generateUserAvatar($userId, $name, $size = 40, $fontSize = '1.2rem') {
    $emoji = getUserEmojiAvatar($userId, $name);
    $gradient = getAvatarGradient($userId);
    
    $html = '<div class="emoji-avatar" style="
        width: ' . $size . 'px;
        height: ' . $size . 'px;
        border-radius: 50%;
        background: linear-gradient(135deg, ' . $gradient[0] . ', ' . $gradient[1] . ');
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: ' . $fontSize . ';
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: 2px solid white;
    " title="' . htmlspecialchars($name) . '">';
    
    $html .= $emoji;
    $html .= '</div>';
    
    return $html;
}

/**
 * Generate simple emoji for inline use
 */
function getSimpleArtisanEmoji($artisanId) {
    return getArtisanEmojiAvatar($artisanId);
}

/**
 * Generate simple emoji for user inline use
 */
function getSimpleUserEmoji($userId) {
    return getUserEmojiAvatar($userId);
}

/**
 * Generate avatar with initials fallback
 */
function generateAvatarWithInitials($id, $name, $isArtisan = false, $size = 50) {
    if ($isArtisan) {
        $emoji = getArtisanEmojiAvatar($id, $name);
    } else {
        $emoji = getUserEmojiAvatar($id, $name);
    }
    
    $gradient = getAvatarGradient($id);
    $initials = strtoupper(substr($name, 0, 1));
    
    $html = '<div class="emoji-avatar-with-initials" style="
        width: ' . $size . 'px;
        height: ' . $size . 'px;
        border-radius: 50%;
        background: linear-gradient(135deg, ' . $gradient[0] . ', ' . $gradient[1] . ');
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: ' . ($size * 0.6) . 'px;
        color: white;
        font-weight: bold;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border: 3px solid white;
        position: relative;
    " title="' . htmlspecialchars($name) . '">';
    
    // Show emoji with initials as fallback
    $html .= '<span class="emoji-main" style="font-size: ' . ($size * 0.5) . 'px;">' . $emoji . '</span>';
    $html .= '<span class="initials-fallback" style="display: none; font-size: ' . ($size * 0.4) . 'px;">' . $initials . '</span>';
    
    $html .= '</div>';
    
    return $html;
}
?>
