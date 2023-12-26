<?php

$files = glob("./downloads/*");

if(!$files)
{
    echo 'No files ';
    exit;
}

$total = 0;
$kotakSize = 0;
foreach($files as $file)
{
    $size = filesize($file);
    if($file !== './downloads/download.mp4')
    {
        $total += $size;
    }else{
        $kotakSize = $size;
    }
    echo "<div>$file is <span style='color: orange;font-size:30px'>".($size)."</div>";
}

echo "<div>Total <span style='color: orange;font-size:30px'>".($total)."</div>";
echo "<div>Diff <span style='color: orange;font-size:30px'>".($kotakSize - $total)."</div>";



function formatFileSize($size) {
    $units = ['Bytes', 'KBytes', 'MBytes', 'GBytes', 'TBytes'];

    for ($i = 0; $size > 1024; $i++) {
        $size /= 1024;
    }

    return round($size, 2) . '' . $units[$i];
}
