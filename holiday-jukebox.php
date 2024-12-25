<?php
/**
 * Plugin Name: Holiday Jukebox
 * Description: A simple jukebox plugin to display albums and songs dynamically.
 * Version: 1.0
 * Author: Vivian
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

define('JUKEBOX_ALBUMS', [
    [
        'title' => '深蓝者',
        'title_en' => 'Deepblue',
        'cover' => plugin_dir_url(__FILE__) . 'assets/images/Deepblue.jpg',
    ],
    [
        'title' => '曼陀罗',
        'title_en' => 'Datura',
        'cover' => plugin_dir_url(__FILE__) . 'assets/images/Datura.jpg',
    ],
    [
        'title' => '拾荒者',
        'title_en' => 'Scavenger',
        'cover' => plugin_dir_url(__FILE__) . 'assets/images/Scavenger.jpg',
    ],
]);


// Enqueue necessary scripts


// Enqueue necessary scripts and localize AJAX URL
// Hook function to handle AJAX request for logged-in users

/**
 * Jukebox Shortcode Callback
 */
function jukebox_shortcode()
{
    ob_start();
    generate_jukebox();
    return ob_get_clean();
}
add_shortcode('holiday-jukebox', 'jukebox_shortcode');

/**
 * Generate the Jukebox HTML
 */
/**
 * Generate the Main Jukebox HTML
 */
function generate_jukebox()
{
    $albums = JUKEBOX_ALBUMS;
    ?>
    <div class="jukebox-container" id="jukebox-container">

        <!-- Songs Section -->
        <div>
            <div id="album-html-container" class="jukebox">
                <?php echo load_album("深蓝者"); ?>
            </div>  
            <button id="backButton">Back To Main Menu</button>
        </div>
    </div>

    <?php
}

/**
 * AJAX Handler for Generating Jukebox for an Album
 */
add_action('wp_ajax_generate_jukebox_for_album', 'generate_jukebox_for_album');
add_action('wp_ajax_nopriv_generate_jukebox_for_album', 'generate_jukebox_for_album');

function generate_jukebox_for_album()
{
    $albumTitle = isset($_GET['album_title']) ? sanitize_text_field($_GET['album_title']) : '深蓝者';

    load_album($albumTitle);
    wp_die();
}

function load_album($albumTitle)
{
    $albums = JUKEBOX_ALBUMS;

    // Find the album by title
    foreach ($albums as $album) {
        if ($album['title'] === $albumTitle) {
            $template = __DIR__ . '/templates/' . strtolower(str_replace(' ', '_', $album['title_en'])) . '.php';
            include __DIR__ . '/templates/' . strtolower(str_replace(' ', '_', $album['title_en'])) . '.php';
        }
    }
}

function jukebox_scripts() {
    // Enqueue the main Three.js library
    wp_enqueue_script(
        'three-js',
        'https://cdn.jsdelivr.net/npm/three@0.115.0/build/three.min.js',
        array(), // No dependencies
        '0.115.0', // Version of Three.js
        true // Load in the footer
    );

    // Enqueue postprocessing EffectComposer
    wp_enqueue_script(
        'effect-composer',
        'https://cdn.jsdelivr.net/npm/three@0.115.0/examples/js/postprocessing/EffectComposer.js',
        array('three-js'), // Depends on Three.js
        '0.115.0',
        true
    );

    // Enqueue RenderPass
    wp_enqueue_script(
        'render-pass',
        'https://cdn.jsdelivr.net/npm/three@0.115.0/examples/js/postprocessing/RenderPass.js',
        array('effect-composer'), // Depends on EffectComposer
        '0.115.0',
        true
    );

    // Enqueue ShaderPass
    wp_enqueue_script(
        'shader-pass',
        'https://cdn.jsdelivr.net/npm/three@0.115.0/examples/js/postprocessing/ShaderPass.js',
        array('effect-composer'), // Depends on EffectComposer
        '0.115.0',
        true
    );

    // Enqueue CopyShader
    wp_enqueue_script(
        'copy-shader',
        'https://cdn.jsdelivr.net/npm/three@0.115.0/examples/js/shaders/CopyShader.js',
        array('shader-pass'), // Depends on ShaderPass
        '0.115.0',
        true
    );


    // Enqueue LuminosityHighPassShader
    wp_enqueue_script(
        'luminosity-highpass-shader',
        'https://cdn.jsdelivr.net/npm/three@0.115.0/examples/js/shaders/LuminosityHighPassShader.js',
        array('shader-pass'), // Depends on ShaderPass
        '0.115.0',
        true
    );

    // Enqueue UnrealBloomPass
    wp_enqueue_script(
        'unreal-bloom-pass',
        'https://cdn.jsdelivr.net/npm/three@0.115.0/examples/js/postprocessing/UnrealBloomPass.js',
        array('effect-composer', 'luminosity-highpass-shader'), // Depends on EffectComposer and LuminosityHighPassShader
        '0.115.0',
        true
    );

    
    wp_enqueue_script('jukebox-script', plugin_dir_url(__FILE__) . 'assets/js/jukebox.js', array(), '1.0', true);
    wp_enqueue_style('jukebox-style', plugin_dir_url(__FILE__) . 'assets/css/jukebox.css', array(), '1.0', 'all');

    wp_localize_script('jukebox-script', 'ajaxData', array(
        'ajaxUrl' => admin_url('admin-ajax.php')
    ));
    
}
add_action('wp_enqueue_scripts', 'jukebox_scripts');
