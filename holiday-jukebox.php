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
    $first_album_name = !empty($albums) ? basename($albums[0]) : ''; 
    $html = '<div id="jukebox-container"><div id="album-html-container" class="jukebox"><div id="album-grid">';
    $mappings = get_name_mappings();
    foreach ($albums as $album) {
        $album_name = basename($album);
        $album_display_name = preg_replace('/^\d+\s*/', '', $album_name);
        $album_file_name = strtolower(str_replace(' ', '_', $album_display_name));
        
        $album_display_name_zh = $mappings['albums'][$album_display_name];

        $album_image = plugin_dir_url(__FILE__) . "assets/images/{$album_file_name}.jpg";

        // Album cover with click event to load its overlay
        $html .= "<div class='album-item' onclick=\"loadAlbumOverlay('$album_name')\">";
        $html .= "<img src='$album_image' alt='$album_name cover' class='album-cover'>";
        $html .= "<p>$album_display_name_zh $album_display_name</p>";
        $html .= "</div>";
    }

    $html .= '</div>';
    $html .= '<div id="overlay">';
    if (!empty($first_album_name)){
        $html .= get_album_content($first_album_name);
    }
    $html .= '</div>';
    $html .= '<button id="backButton">返回 Back</button></div></div>';
    return $html;
}
add_shortcode('jukebox', 'jukebox_shortcode');
function get_name_mappings() {
    static $mappings = null; // Use a static variable to cache the mappings during a single request.

    if ($mappings === null) {
        $file_path = plugin_dir_path(__FILE__) . 'names.json';
        if (file_exists($file_path)) {
            $mappings = json_decode(file_get_contents($file_path), true);
        } else {
            $mappings = ['albums' => [], 'songs' => []];
        }
    }

    return $mappings;
}

function handle_play_audio() {
    // Verify if the user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'You must be logged in to play audio.']);
    }

    // Get the current user ID
    $user_id = get_current_user_id();

    // Check if the user has enough myCred points
    $required_points = 10; // Points required to play
    $user_points = mycred_get_users_balance($user_id);

    if ($user_points < $required_points) {
        wp_send_json_error(['message' => 'You do not have enough points to play this audio.']);
    }

    // Deduct points from the user's balance
    mycred_subtract('audio_play', $user_id, $required_points, 'Audio play deduction');

    // Return a success response
    wp_send_json_success();
}
add_action('wp_ajax_play_jukebox_audio', 'handle_play_audio');
add_action('wp_ajax_nopriv_play_jukebox_audio', 'handle_play_audio');


function ajax_get_album_content() {
    $album_name = isset($_GET['album']) ? sanitize_text_field($_GET['album']) : '';

    if (empty($album_name) || $album_name === "undefined") {
        $album_dirs = glob(plugin_dir_path(__FILE__) . 'assets/albums/*', GLOB_ONLYDIR);
        $album_name = !empty($album_dirs) ? basename($album_dirs[0]) : '';
    }

    $html = get_album_content($album_name);
    echo $html;

    wp_die();
}
add_action('wp_ajax_get_album_content', 'ajax_get_album_content');
add_action('wp_ajax_nopriv_get_album_content', 'ajax_get_album_content');

function get_album_content($album_name) {
    $html = '';
    $mappings = get_name_mappings();
    $album_display_name = preg_replace('/^\d+\s*/', '', $album_name);
    $album_display_name_zh = $mappings['albums'][$album_display_name];
    $album_path = plugin_dir_path(__FILE__) . "assets/albums/$album_name";
    $songs = glob("$album_path/*.mp3");

    if (empty($songs)) {
        wp_die('No songs found for this album.');
    }

    $html = '<ul>';
    $html .= "<li class='title'>《{$album_display_name_zh} {$album_display_name}》</li>";

    foreach ($songs as $song) {
        $song_file_name = basename($song, '.mp3');
        $display_name = preg_replace('/^\d+\s*/', '', $song_file_name);
        $file_path = plugin_dir_url(__FILE__) . "assets/albums/$album_name/$song_file_name.mp3";
        $display_name_zh = $mappings['songs'][$album_display_name][$display_name];

        $html .= "<li><button class='btn' type='button' data-filepath='$file_path' onclick='loadAudio(this)'>$display_name_zh $display_name</button></li>";
    }

    $html .= '</ul>';

    return  $html;
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

    
    //wp_enqueue_script('jukebox-script', plugin_dir_url(__FILE__) . 'assets/js/jukebox.js', array(), '1.0', true);
    //wp_enqueue_script('album-script', plugin_dir_url(__FILE__) . 'assets/js/load_album.js', array(), '1.0', true);
    wp_enqueue_script('jukebox-script', plugin_dir_url(__FILE__) . 'assets/js/jukebox.js', array('jquery'), '1.0', true);
    wp_enqueue_style('jukebox-style', plugin_dir_url(__FILE__) . 'assets/css/jukebox.css', array(), '1.0', 'all');

    wp_localize_script('jukebox-script', 'ajaxData', array(
        'ajaxUrl' => admin_url('admin-ajax.php')
    ));
    
}
add_action('wp_enqueue_scripts', 'jukebox_scripts');
