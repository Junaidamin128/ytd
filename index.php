<?php
require_once "core/fns.php";
require_once "core/downloader.php";



include "components/header.php";
?>

<form method="get" action="select.php" class="formSmall">
    <div class="row">
        <div class="col-lg-12">
            <h7 class="text-align"> Download YouTube Video</h7>
        </div>
        <div class="col-lg-12">
            <div class="input-group">
                <input value="https://www.youtube.com/watch?v=WO2b03Zdu4Q" type="text" class="form-control" name="video_link" placeholder="Paste link.. e.g. https://www.youtube.com/watch?v=5cpIZ8zHHXw">
                <span class="input-group-btn">
                    <button type="submit" name="submit" id="submit" class="btn btn-primary">Go!</button>
                </span>
            </div><!-- /input-group -->
        </div>
    </div><!-- .row -->
</form>
<?php
include "components/footer.php";
?>