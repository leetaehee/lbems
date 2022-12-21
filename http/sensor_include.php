<?php
foreach (glob(TOP_HTTP_DIR . 'sensor/*.php') as $filename)
{
    include_once($filename);
}
