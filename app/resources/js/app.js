/**
 * Initializes a Masonry grid layout and sets up image loading behavior to trigger layout updates.
 *
 * This method selects a grid container by its ID (`#masonry-grid`) and applies the Masonry layout
 * to its child elements. It also uses the imagesLoaded library to ensure layout updates occur
 * as images progressively load or after all images complete loading.
 *
 * @return {void} This function does not return a value.
 */
function initMasonry() {
    const grid = document.querySelector('#masonry-grid');
    if (grid) {
        const msnry = new Masonry(grid, {
            itemSelector: '.masonry-item',
            percentPosition: true,
            columnWidth: '.masonry-item'
        });

        const imgLoad = imagesLoaded(grid);

        imgLoad.on('progress', function() {
            // layout Masonry after each image loads
            msnry.layout();
        });

        imgLoad.on('always', function() {
            // final layout after all images (loaded or failed)
            msnry.layout();
        });
    }
}

document.addEventListener('DOMContentLoaded', initMasonry);
