<?php
include_once(TOP_HTTP_DIR . 'parser/IParser.php');

foreach (glob(TOP_HTTP_DIR . 'parser/*.php') as $filename)
{
	include_once($filename);
}