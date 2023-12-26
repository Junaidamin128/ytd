<?php

$files = glob("./*");

echo '<ul>';
foreach($files as $file)
{
    if(is_dir($file))
    {
        echo "<li> ".'<svg style="width: 20px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
  <rect x="2" y="4" width="20" height="16" rx="2" ry="2"></rect>
  <line x1="8" y1="2" x2="8" y2="6"></line>
  <line x1="16" y1="2" x2="16" y2="6"></line>
  <line x1="12" y1="2" x2="12" y2="6"></line>
</svg>'." <a href='$file'>$file</a> </li>";
    }else{
        echo "<li> <a href='$file'>$file</a> </li>";    
    }
    
}