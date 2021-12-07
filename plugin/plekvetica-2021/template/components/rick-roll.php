<?php 
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}
?>
<div class="plek_search_nothing">
    <div id="player"></div>
    <script>
        var tag = document.createElement('script');

        tag.src = 'https://www.youtube.com/iframe_api';
        var firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

        var player;

        function onYouTubeIframeAPIReady() {
            player = new YT.Player('player', {
                height: '360',
                width: '640',
                videoId: 'dQw4w9WgXcQ',
                events: {
                    'onReady': onPlayerReady,
                }
            });
        }

        function onPlayerReady(event) {
            event.target.playVideo();
        }

    </script>
    </iframe>
</div>