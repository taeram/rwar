/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
require('../css/app.scss');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
const $ = require('jquery');

$(document).ready(function () {
    // Trigger a page reload when a new subreddit is chosen
    $('#subreddit').on('change', function () {
        window.location.pathname = '/wallpapers/' + $('#subreddit').val();
    });

    // Ensure the image fits on the screen
    var imageHeight = $('#wallpaper-image').height();
    var imageWidth = $('#wallpaper-image').width();
    var containerHeight = document.documentElement.clientHeight - $('#subreddit-name').height() - $('#image-resolution').height() - 20;
    if (imageHeight > containerHeight) {
        imageWidth = Math.floor((imageWidth / imageHeight) * containerHeight);
        $('#wallpaper-image').css({
            'height': containerHeight + 'px',
            'width': imageWidth + 'px'
        });
    }

    // Show the image resolution
    var img = new Image();
    img.onload = function () {
        $('#image-resolution').html(this.width + 'x' + this.height);
    };
    img.src = $('#wallpaper-image').attr('src');

    // Hotkeys
    $(document).bind('keydown', null, function (e) {
        if (e.originalEvent.key === 'w') {
            window.location.href = $('#link-favourite').attr('href');
        } else if (e.originalEvent.key === 's') {
            window.location.href = $('#link-reject').attr('href');
        } else if (e.originalEvent.key === 'z') {
            window.location.href = $('#link-set').attr('href');
        }
    });
});
