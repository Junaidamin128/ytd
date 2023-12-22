<?php
require_once "core/fns.php";
require_once "core/downloader.php";

if (!isset($_GET['video_link']) || trim($_GET['video_link']) == '') {
    header("location: index.php");
    exit;
}
$url = $_GET['video_link'];
$youtubeDownloader = new YouTubeDownloader($url);
$info = $youtubeDownloader->extractVideoInfoFromUrl($url);
$title = $info['title'];
$both = $info['both'];
$videoOnly = $info['videoOnly'];
$audioOnly = $info['audioOnly'];



include "components/header.php";
// Display the rendered cards or perform further actions.
renderCards($both, "Audio/video", $url, $title);
renderCards($videoOnly, "VideoOnly", $url, $title);
renderCards($audioOnly, "AudioOnly", $url, $title);

include "components/footer.php";
