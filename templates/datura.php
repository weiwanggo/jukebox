<div id="overlay">
    <ul>
        <li class="title">《曼陀罗》</li>
        <li><button class="btn" id="btnA" type="button" data-filepath="/wp-content/plugins/holiday-jukebox/assets/media/Datura.mp3">曼陀罗</button></li>
        <li><button class="btn" id="btnB" type="button" data-filepath="/wp-content/plugins/holiday-jukebox/assets/media/Believer.mp3">Believer</button></li>
        <li><button class="btn" id="btnC" type="button" data-filepath="/wp-content/plugins/holiday-jukebox/assets/media/Moonlight.mp3">Moonlight</button></li>
        <li><button class="btn" id="btnD" type="button" data-filepath="/wp-content/plugins/holiday-jukebox/assets/media/TimeToLeave.mp3">马上就离开</button></li>
        <li><button class="btn" id="btnE" type="button" data-filepath="/wp-content/plugins/holiday-jukebox/assets/media/UnfinishedJourney.mp3">未完成的旅行</button></li>
        <li><button class="btn" id="btnF" type="button" data-filepath="/wp-content/plugins/holiday-jukebox/assets/media/Pressure.mp3">Pressure</button></li>
        <li><button class="btn" id="btnG" type="button" data-filepath="/wp-content/plugins/holiday-jukebox/assets/media/Chase.mp3">追</button> </li>
        <li><button class="btn" id="btnG" type="button" data-filepath="/wp-content/plugins/holiday-jukebox/assets/media/Bad.mp3">坏</button> </li>
        <li><button class="btn" id="btnG" type="button" data-filepath="/wp-content/plugins/holiday-jukebox/assets/media/No1Player.mp3">头号玩家</button> </li>
    </ul>
    <div class="navigation-container">
        <button id="prevButton" class="nav-button" onclick="loadAlbum('深蓝者')">
            <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . '../assets/images/leftarrow1.png'); ?>"
                alt="Previous" />
        </button>
        <button id="homeButton" class="nav-button" onclick="window.location.href = '/'">
            <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . '../assets/images/homepage.png'); ?>" alt="Home" />
        </button>
        <button id="nextButton" class="nav-button" onclick="loadAlbum('拾荒者')">
            <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . '../assets/images/rightarrow1.png'); ?>"
                alt="Next" />
        </button>
    </div>
</div>