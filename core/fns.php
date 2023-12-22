<?php
define("BASE_URI",  "/ytd/");


if (!function_exists("dlimit")) {
    function dlimit()
    {
        echo "<pre>";
        var_dump(func_get_args());
        echo "</pre>";
    }
}

if (!function_exists("s")) {
    function s()
    {
        echo "<pre>";
        print_r(func_get_args());
        echo "</pre>";
    }
}

$time = 0;
$benchTitle = "";
function benchStart($title = "Bench")
{
    global $benchTitle;
    global $time;
    $time = time();
    $benchTitle = $title;
}

function benchEnd()
{
    global $benchTitle;
    global $time;
    $time2 = time();
    echo "<h1>Benchmark $benchTitle (" . ($time2 - $time) . " seconds)</h1>";
}

function delete_old()
{
    foreach (glob("downloads/*") as $item) {
        unlink($item);
    }
}


function curl_get_file_size($url)
{
    // Assume failure.
    $result = -1;

    $curl = curl_init($url);

    // Issue a HEAD request and follow any redirects.
    curl_setopt($curl, CURLOPT_NOBODY, true);
    curl_setopt($curl, CURLOPT_HEADER, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    $data = curl_exec($curl);
    curl_close($curl);


    if ($data) {
        $content_length = null;
        $pos = strpos($data, "HTTP/1.1 200 OK");
        if ($pos !== false) {
            preg_match("/Content-Length: (.+)/", $data, $matches, 0, $pos);
            $content_length = $matches[1];
            $content_length = (int)$content_length;
        }
    }

    return $content_length;
}





function renderCards($formats, $cardHeader = "", $url = "", $title = "")
{
?>
    <div class="card formSmall">
        <div class="card-header">
            <strong><?= $cardHeader; ?></strong>
        </div>

        <div class="card-body">
            <table class="table ">
                <tr>
                    <td>Type</td>
                    <td>Quality</td>
                    <td>Action</td>
                </tr>
                <tr>
                    <h2 href="<?php echo $url; ?>"><?= $title; ?></h2>
                </tr>
                <?php foreach ($formats as $format) : ?>
                    <?php

                    if (@$format->url == "") {
                        $signature = "https://example.com?" . $format->signatureCipher;
                        parse_str(parse_url($signature, PHP_URL_QUERY), $parse_signature);
                        $url = $parse_signature['url'] . "&sig=" . $parse_signature['s'];
                    } else {
                        $url = $format->url;
                    }
                    ?>
                    <tr>

                        <td>
                            <?= explode(";", $format->mimeType)[0]; ?>
                            <?php //if ($format->mimeType) echo explode(";", explode("/", $format->mimeType)[1])[0];
                            //else echo "Unknown"; 
                            ?>
                        </td>
                        <td>
                            <?= $format->qualityLabel2 ?>
                            <?= isset($format->fps) ? "<span class='badge badge-primary'>" . $format->fps . "</span>" : "" ?>
                            <?= $format->isHDR ? "<span class='is-hdr badge badge-secondary badge-xs'>HDR</span>" : "" ?>
                        </td>
                        <td>
                            <form method="post" action="<?= BASE_URI; ?>download.php">
                                <textarea name="video-info"><?= json_encode($format) ?></textarea>
                                <button>Download</button>
                            </form>

                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
<?php
}
