<?php
include_once(TOP_HTTP_DIR . 'command/Command.php');
include_once(TOP_HTTP_DIR . 'command/command_table.php');

foreach (glob(TOP_HTTP_DIR . 'command/login/*.php') as $filename)
{
	include_once($filename);
}

foreach (glob(TOP_HTTP_DIR . 'command/building/*.php') as $filename)
{
	include_once($filename);
}

foreach (glob(TOP_HTTP_DIR . 'command/report/*.php') as $filename)
{
	include_once($filename);
}

foreach (glob(TOP_HTTP_DIR . 'command/analysis/*.php') as $filename)
{
	include_once($filename);
}

foreach (glob(TOP_HTTP_DIR . 'command/watchdog/*.php') as $filename)
{
	include_once($filename);
}

foreach (glob(TOP_HTTP_DIR . 'command/dashboard/*.php') as $filename)
{
	include_once($filename);
}

foreach (glob(TOP_HTTP_DIR . 'command/weather/*.php') as $filename)
{
	include_once($filename);
}

foreach (glob(TOP_HTTP_DIR . 'command/control/*.php') as $filename)
{
	include_once($filename);
}

foreach (glob(TOP_HTTP_DIR . 'command/prediction/*.php') as $filename)
{
	include_once($filename);
}

foreach (glob(TOP_HTTP_DIR . 'command/facility/*.php') as $filename)
{
	include_once($filename);
}

foreach (glob(TOP_HTTP_DIR . 'command/alarm/*.php') as $filename)
{
	include_once($filename);
}

foreach (glob(TOP_HTTP_DIR . 'command/info/*.php') as $filename)
{
	include_once($filename);
}

foreach (glob(TOP_HTTP_DIR . 'command/diagram/*.php') as $filename)
{
	include_once($filename);
}

foreach (glob(TOP_HTTP_DIR . 'command/set/*.php') as $filename)
{
	include_once($filename);
}

foreach (glob(TOP_HTTP_DIR . 'command/manager/*.php') as $filename)
{
	include_once($filename);
}

foreach (glob(TOP_HTTP_DIR . 'command/menu/*.php') as $filename)
{
    include_once($filename);
}

foreach (glob(TOP_HTTP_DIR . 'command/solar/*.php') as $filename)
{
	include_once($filename);
}

foreach (glob(TOP_HTTP_DIR . 'command/paper/*.php') as $filename)
{
	include_once($filename);
}

foreach (glob(TOP_HTTP_DIR . 'command/account/*.php') as $filename)
{
    include_once($filename);
}

foreach (glob(TOP_HTTP_DIR . 'command/auth/*.php') as $filename)
{
    include_once($filename);
}

foreach (glob(TOP_HTTP_DIR . 'command/migration/*.php') as $filename)
{
    include_once($filename);
}

foreach (glob(TOP_HTTP_DIR . 'command/test/*.php') as $filename)
{
    include_once($filename);
}

foreach (glob(TOP_HTTP_DIR . 'command/cache/*.php') as $filename)
{
    include_once($filename);
}

foreach (glob(TOP_HTTP_DIR . 'command/frame/*.php') as $filename)
{
    include_once($filename);
}

foreach (glob(TOP_HTTP_DIR . 'command/home/*.php') as $filename)
{
    include_once($filename);
}

foreach (glob(TOP_HTTP_DIR . 'command/common/*.php') as $filename)
{
    include_once($filename);
}

foreach (glob(TOP_HTTP_DIR . 'command/calendar/*.php') as $filename)
{
    include_once($filename);
}

foreach (glob(TOP_HTTP_DIR . 'command/integration/*.php') as $filename)
{
    include_once($filename);
}