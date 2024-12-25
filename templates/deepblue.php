<div id="overlay">
    <ul>
        <li class="title">《深蓝者》</li>
        <li><button class="btn" id="btnA" type="button" data-filepath="/wp-content/plugins/holiday-jukebox/assets/media/MelancholySunshine.mp3">忧伤的晴朗</button></li>
        <li><button class="btn" id="btnB" type="button" data-filepath="/wp-content/plugins/holiday-jukebox/assets/media/KnightErrant.mp3">游侠</button></li>
        <li><button class="btn" id="btnC" type="button" data-filepath="/wp-content/plugins/holiday-jukebox/assets/media/Journey.mp3">途</button></li>
        <li><button class="btn" id="btnD" type="button" data-filepath="/wp-content/plugins/holiday-jukebox/assets/media/PrimordialTheater.mp3">洪荒剧场</button></li>
        <li><button class="btn" id="btnE" type="button" data-filepath="/wp-content/plugins/holiday-jukebox/assets/media/LostGlacier.mp3">冰川消失那天</button></li>
        <li><button class="btn" id="btnF" type="button" data-filepath="/wp-content/plugins/holiday-jukebox/assets/media/MagnificentLife.mp3">人生海海</button></li>
        <li><button class="btn" id="btnG" type="button" data-filepath="/wp-content/plugins/holiday-jukebox/assets/media/StarsLightYouUp.mp3">变成星星照亮你</button> </li>
    </ul>
    <div class="navigation-container">
        <button id="prevButton" class="nav-button">
            <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . '../assets/images/christmas-zhe2.png'); ?>"
                alt="Previous" />
        </button>
        <button id="homeButton" class="nav-button" onclick="window.location.href = '/'">
            <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . '../assets/images/homepage.png'); ?>" alt="Home" />
        </button>
        <button id="nextButton" class="nav-button" onclick="loadAlbum('曼陀罗')">
            <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . '../assets/images/rightarrow1.png'); ?>"
                alt="Next" />
        </button>
    </div>
</div>