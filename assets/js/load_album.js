// Add these variables at the global scope where other variables are declared
async function loadAlbum(title) {
    const albumHtmlContainer = document.getElementById('album-html-container');

        try {
        // Make the AJAX request to load album HTML dynamically
        const response = await fetch(`${ajaxData.ajaxUrl}?action=generate_jukebox_for_album&album_title=${encodeURIComponent(title)}`);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const albumHtml = await response.text();

        // Insert the HTML content into the container
        albumHtmlContainer.innerHTML = albumHtml;
    } catch (error) {
        console.error('Error fetching album data:', error);
    }
}