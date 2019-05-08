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
    let $wallpaperImage = $('#wallpaper-image');
    const imageHeight = $wallpaperImage.height();
    let imageWidth = $wallpaperImage.width();
    const containerHeight = document.documentElement.clientHeight - $('#subreddit-toolbar').height() - $('#image-resolution').height() - 30;
    if (imageHeight > containerHeight) {
        imageWidth = Math.floor((imageWidth / imageHeight) * containerHeight);
        $('#wallpaper-image').css({
            'height': containerHeight + 'px',
            'width': imageWidth + 'px'
        });
    }

    // Show the image resolution
    const img = new Image();
    img.onload = function () {
        $('#image-resolution').html(this.width + 'x' + this.height);
    };
    img.src = $wallpaperImage.attr('src');

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

    // Is the Downloader running?
    var checkDownloader = function () {
        $.get('/wallpapers/downloader/status', function (result) {
            if (result === 'Running') {
                $('#downloader').addClass('btn-primary').removeClass('btn-dark').html('Downloader Running');
            }
            else {
                $('#downloader').removeClass('btn-primary').addClass('btn-dark').html('Downloader Stopped');
            }
        });
    };
    var downloaderInterval = setInterval(checkDownloader, 5000);
    checkDownloader();

    // Trigger the downloader
    $('#downloader').on('click', function () {
        $.get('/wallpapers/downloader/start');
    });
});
