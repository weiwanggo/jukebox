<div id="overlay">
    <ul>
        <li class="title">Select Music 《拾荒者》</li>
        <li><button class="btn" id="btnA" type="button" data-filepath="/wp-content/plugins/holiday-jukebox/assets/media/CanYouHearMe.mp3">Can You Hear Me</button></li>
        <li><button class="btn" id="btnB" type="button" data-filepath="/wp-content/plugins/holiday-jukebox/assets/media/Unchained.mp3">无碍</button></li>
        <li><button class="btn" id="btnC" type="button" data-filepath="/wp-content/plugins/holiday-jukebox/assets/media/GoingOff.mp3">Going Off</button></li>
        <li><button class="btn" id="btnD" type="button" data-filepath="/wp-content/plugins/holiday-jukebox/assets/media/90S.mp3">90S</button></li>
        <li><button class="btn" id="btnD" type="button" data-filepath="/wp-content/plugins/holiday-jukebox/assets/media/Next.mp3">慢走不送</button></li>
    </ul>
    <div class="navigation-container">
        <button id="prevButton" class="nav-button" onclick="loadAlbum('曼陀罗')">
            <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . '../assets/images/leftarrow1.png'); ?>"
                alt="Previous" />
        </button>
        <button id="homeButton" class="nav-button" onclick="window.location.href = '/'">
            <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . '../assets/images/homepage.png'); ?>" alt="Home" />
        </button>
        <button id="nextButton" class="nav-button" >
            <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . '../assets/images/christmas-zhe2.png'); ?>"
                alt="Next" />
        </button>
    </div>

</div>