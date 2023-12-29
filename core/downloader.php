<?php

require_once 'fns.php';

class YouTubeDownloader
{
    private $videoUrl;
    private $chunks = [];

    public static function downloadUsingCurl($videoInfo, $isAudio = false)
    {
        dkotak("start");
        set_time_limit(0);






        /////////
        $handle = curl_multi_init();


        /////////
        $url = $videoInfo->url;
        $url = str_replace(" ", "%20", $url);

        $len = $videoInfo->contentLength;
        $divide = 4;
        $chunkSize = floor($len / $divide);
        skotak("Video length " . $len);
        $start = -$chunkSize - 1;
        /////////

        for ($i = 0; $i < $divide; $i++) {

            $start = $start + $chunkSize + 1;
            $end = $start + $chunkSize;
            if ($i == $divide - 1) {
                if ($end != $len) {
                    $end = $len;
                }
            }
            $range = "$start-$end";



            //////
            $name =  "chunk-$i.mp4";
            if ($isAudio) {
                $name =  "audio-" . date(" m-i") . ".mp3";
            }
            skotak([$name, $range,  $end - $start]);

            //Here is the file we are downloading, replace spaces with %20
            $ch = curl_init($url);
            $file = fopen("./downloads/$i.mp4", "w+");
            // make sure to set timeout to a high enough value
            // if this is too low the download will be interrupted
            curl_setopt($ch, CURLOPT_TIMEOUT, 600);
            curl_setopt($ch, CURLOPT_FILE, $file);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RANGE, $range);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
            ///////////////////////////
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);



            //////
            // curl_setopt_array($ch, array(CURLOPT_RETURNTRANSFER => TRUE));

            // curl_setopt($ch, CURLOPT_HEADER, 0); // no headers in the output // or maybe dont use

            curl_multi_add_handle($handle, $ch);
        }
        $callback = function ($data, $info, $i) {
            file_put_contents("downloads/data-$i.mp4", $data);
        };

        $i = -1;
        do {
            $mrc = curl_multi_exec($handle, $active);

            // exit;
            if ($state = curl_multi_info_read($handle)) {
                $i++;
                $info = curl_getinfo($state['handle']);
                $data = curl_multi_getcontent($state['handle']);
                //
                dkotak($active);
                skotak($i);
                skotak(@strlen($data));
                // $callback($data, $info, $active);
                curl_multi_remove_handle($handle, $state['handle']);
            }
        } while ($mrc == CURLM_CALL_MULTI_PERFORM || $active);

        curl_multi_close($handle);


        self::combineDownloads();

        self::downloadTheFile();
    }

    public static function downloadTheFile()
    {
        header("Location: " . BASE_URI . "save.php");
    }

    public static function combineDownloads()
    {
        $filesArray = glob("downloads/*");
        sort($filesArray);


        $command = "[commandName] ";
        $command .= self::joinFilesBySpace($filesArray) . " > " . realpath("./downloads") . "\\download.mp4";
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $command = str_replace(["[commandName]", "\\"], ["type", "\\"], $command);
        } else {
            $command = str_replace(["[commandName]", "\\"], ["cat", "/"], $command);
        }
        exec($command, $output, $result_code);
    }

    public static function combineDownloads2()
    {
        $filesArray = glob("downloads/*");
        sort($filesArray);


        $command = "[commandName] ";
        $command .= self::joinFilesBySpace($filesArray) . " > " . realpath("./downloads") . "\\download.mp4";
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $command = str_replace(["[commandName]", "\\"], ["type", "\\"], $command);
        } else {
            $command = str_replace(["[commandName]", "\\"], ["cat", "/"], $command);
        }
        skotak($command);
        exit;
        exec($command, $output, $result_code);
    }

    public static function joinFilesBySpace($filesArray)
    {
        return join(" ", array_map(function ($file) {
            return realpath($file);
        }, $filesArray));
    }


    public function __construct($videoUrl)
    {
        $this->videoUrl = $videoUrl;
    }

    public function getVideoInfo($video_id)
    {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://www.youtube.com/youtubei/v1/player?key=AIzaSyAO_FJ2SlqU8Q4STEHLGCilw_Y9_11qcW8');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{  "context": {    "client": {      "hl": "en",      "clientName": "WEB",      "clientVersion": "2.20210721.00.00",      "clientFormFactor": "UNKNOWN_FORM_FACTOR",   "clientScreen": "WATCH",      "mainAppWebInfo": {        "graftUrl": "/watch?v=' . $video_id . '",           }    },    "user": {      "lockedSafetyMode": false    },    "request": {      "useSsl": true,      "internalExperimentFlags": [],      "consistencyTokenJars": []    }  },  "videoId": "' . $video_id . '",  "playbackContext": {    "contentPlaybackContext": {        "vis": 0,      "splay": false,      "autoCaptionsDefaultOn": false,      "autonavState": "STATE_NONE",      "html5Preference": "HTML5_PREF_WANTS",      "lactMilliseconds": "-1"    }  },  "racyCheckOk": false,  "contentCheckOk": false}');
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

        $headers = array();
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }

    public function extractVideoInfoFromUrl($url)
    {
        preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
        $video_id = $match[1];
        $video = json_decode(self::getVideoInfo($video_id));
        $title = $video->videoDetails->title;
        $description = $video->videoDetails->shortDescription;

        $formats1 = $video->streamingData->formats;

        $formats2 = $video->streamingData->adaptiveFormats;

        $formats = array_merge($formats1, $formats2);

        //add flags hasAudioVideo
        foreach ($formats as $format) {
            $format->hasAudio = isset($format->audioQuality);
            $format->hasVideo = strpos($format->mimeType, "video/") !== false;
            $format->hasBothAudioVideo = $format->hasAudio && $format->hasVideo;
            if ($format->hasVideo) {
                $qualityParts = explode("p", $format->qualityLabel, 2);
                $format->qualityLabel2 = $qualityParts[0] . "p";
                $format->isHDR = strpos($qualityParts[1], "HDR") !== false;
            } else {
                $format->qualityLabel2 = "FetchAudio";
                $format->isHDR = false;
            }
        }

        $both = [];
        $videoOnly = [];
        $audioOnly = [];


        foreach ($formats as $format) {
            // dkotak($format->audioQuality);
            if ($format->hasBothAudioVideo) {
                $both[] = $format;
            } else if ($format->hasVideo) {
                $videoOnly[] = $format;
            } else {
                $audioOnly[]  = $format;
            }
        }

        return ["title" => $title, "both" => $both, "videoOnly" => $videoOnly, "audioOnly" => $audioOnly];
    }

    public static function downloadVideo()
    {
        $videoInfoStr = $_POST['video-info'];
        $videoInfo = json_decode($videoInfoStr);

        if (!isset($videoInfo->contentLength) || $videoInfo->contentLength == 0) {
            $videoInfo->contentLength = curl_get_file_size($videoInfo->url);
        }
        delete_old();

        //This is the file where we save the    information
        if (!file_exists(realpath("./") . "/downloads")) {
            mkdir(realpath("./") . "/downloads");
        }

        $start = time();
        // $name = self::downloadUsingCurl($videoInfo);
        $name = self::downloadUsingAria2c($videoInfo);
        $end = time();
        skotak($end - $start);
        exit;
    }

    public static function downloadUsingAria2c($videoInfo)
    {
        $url = $videoInfo->url;
        $url = str_replace(" ", "%20", $url);
        $aria2cPath = realpath(__DIR__ . "/../3rd/aria2c.exe");
        // $savePath = realpath(__DIR__ . "/../downloads");
        $savePath = "downloads/";
        $savePath .= substr($videoInfo->customTitle, 0, 10) . "-" . $videoInfo->qualityLabel . ".mp4";
        $command = "$aria2cPath -k1024K -x5 -s5 --out=\"$savePath\" \"$url\"";
        dText($command);
        $o = shell_exec($command);
        s($o);
    }



    public function mergeChunks()
    {
    }
}

// Example usage with your existing code:
