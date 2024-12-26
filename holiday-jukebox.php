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

function jukebox_shortcode() {
    $albums = glob(plugin_dir_path(__FILE__) . 'assets/albums/*', GLOB_ONLYDIR);
    $html = '<div id="jukebox-container"><div id="album-html-container" class="jukebox"><div id="album-grid">';

    foreach ($albums as $album) {
        $album_name = basename($album);
        $album_display_name = preg_replace('/^\d+\s*/', '', $album_name);
        $album_file_name = strtolower(str_replace(' ', '_', $album_display_name));


        $album_image = plugin_dir_url(__FILE__) . "assets/images/{$album_file_name}.jpg";

        // Album cover with click event to load its overlay
        $html .= "<div class='album-item' onclick=\"loadAlbumOverlay('$album_name')\">";
        $html .= "<img src='$album_image' alt='$album_name cover' class='album-cover'>";
        $html .= "<p>$album_display_name</p>";
        $html .= "</div>";
    }

    $html .= '</div>';
    $html .= '<div id="overlay" style="display:none;"></div>';
    $html .= '<button id="backButton">Back To Main Menu</button></div></div>';
    return $html;
}
add_shortcode('jukebox', 'jukebox_shortcode');

function get_album_content() {
    if (!isset($_GET['album'])) {
        wp_die('Invalid request');
    }

    $album_name = sanitize_text_field($_GET['album']);
    $album_display_name = preg_replace('/^\d+\s*/', '', $album_name);
    $album_path = plugin_dir_path(__FILE__) . "assets/albums/$album_name";
    $songs = glob("$album_path/*.mp3");

    if (empty($songs)) {
        wp_die('No songs found for this album.');
    }

    $html = '<ul>';
    $html .= "<li class='title'>《{$album_display_name}》</li>";

    foreach ($songs as $song) {
        $song_file_name = basename($song, '.mp3');
        $display_name = preg_replace('/^\d+\s*/', '', $song_file_name);
        $file_path = plugin_dir_url(__FILE__) . "assets/albums/$album_name/$song_file_name.mp3";

        $html .= "<li><button class='btn' type='button' data-filepath='$file_path' onclick='loadAudio(this)'>$display_name</button></li>";
    }

    $html .= '</ul>';
    $html .= '<div class="navigation-container">';
    $html .= "<button id='homeButton' class='nav-button' onclick=\"window.location.href = '/'\">Home</button>";
    $html .= '</div>';

    echo $html;
    wp_die();
}
add_action('wp_ajax_get_album_content', 'get_album_content');
add_action('wp_ajax_nopriv_get_album_content', 'get_album_content');


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

    
    //wp_enqueue_script('jukebox-script', plugin_dir_url(__FILE__) . 'assets/js/jukebox.js', array(), '1.0', true);
    //wp_enqueue_script('album-script', plugin_dir_url(__FILE__) . 'assets/js/load_album.js', array(), '1.0', true);
    wp_enqueue_script('jukebox-script', plugin_dir_url(__FILE__) . 'assets/js/jukebox.js', array(), '1.0', true);
    wp_enqueue_style('jukebox-style', plugin_dir_url(__FILE__) . 'assets/css/jukebox.css', array(), '1.0', 'all');

    wp_localize_script('jukebox-script', 'ajaxData', array(
        'ajaxUrl' => admin_url('admin-ajax.php')
    ));
    
}
add_action('wp_enqueue_scripts', 'jukebox_scripts');
