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
        window.location.pathname = '/' + $('#subreddit').val();
    });

    // Show the image resolution
    var img = new Image();
    img.onload = function () {
        $('#image-resolution').html(this.width + 'x' + this.height);
    };
    img.src = $('#wallpaper-image').attr('src');
});
