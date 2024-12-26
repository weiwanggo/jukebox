document.addEventListener("DOMContentLoaded", () => {
    window.loadAlbumOverlay = function (albumName) {
        // Fetch album content via AJAX
        fetch(`/wp-admin/admin-ajax.php?action=get_album_content&album=${encodeURIComponent(albumName)}`)
            .then(response => response.text())
            .then(data => {
                const overlay = document.getElementById('overlay');
                overlay.innerHTML = data;
                overlay.style.display = 'inline-flex';
            })
            .catch(error => console.error('Error loading album:', error));
    };

    window.loadAlbum = function (albumName) {
        loadAlbumOverlay(albumName); // Use the same function to load albums dynamically
    };
    document.getElementById("backButton").addEventListener("click", stopAudio);
});
