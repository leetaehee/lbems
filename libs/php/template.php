<?php
class template
{
    /**
    * Show or hide debug messages
    */
    public $debug = false;

    /**
    * Whether to use the cache or not
    */
    public $useCache = false;

    /**
    * The path to store cached templates in
    */
    public $path = '';

    /**
    * The path to store cached templates in
    */
    public $cachePath = '/tmp';

    /**
    * The variables
    */
    private $variables = array();

    /**
    * Allows is to tell if this is an included file or not
    */
    public $isInclude = false;

    /**
    * The constructor
    *
    * @param string $template The name of the template to use
    */
    public function __construct($template)
    {
        $this->template = $template;
    }

    /**
    * Sets the path to store cached templates
    */
    public function SetCachePath($path)
    {
        $this->cachePath = $path;
    }

    /**
    * Sets the path to find templates
    */
    public function SetPath($path)
    {
        $path = $path . '/';
        $this->path = str_replace('//', '/', $path);
    }

    /**
    * The callback for handling if conditions
    *
    * @param array $matches The matches found by the regex
    */
    public function ifCallbackHandler($matches)
    {
        if (!empty($this->variables[trim($matches[1])])) {
            return $matches[2];

        } elseif (!empty($matches[3])) {
            return $matches[3];

        } else {
            return '';
        }
    }

    /**
    * The callback for handling inclusions
    *
    * @param array $matches The matches found by the regex
    */
    private function incCallbackHandler($matches)
    {
        preg_match_all('/(?:([a-z0-9]+)="([^"]*)")/is', $matches[1], $attributes);

        foreach ($attributes[0] as $v) {
            $matches[1] = str_replace($v, '', $matches[1]);
        }

        $filename = $this->path . trim($matches[1]);

        /**
        * If the filename is of the format: $foo ie a variable, look it up in the variables
        * that the object has, and use that instead
        */
        if (preg_match('/^(?:\.\/)?\$([a-z0-9]+)$/', basename($filename), $varname)) {
            $filename = str_replace($varname[0], $this->variables[$varname[1]], $filename);
        }

        if (file_exists($filename)) {
            $included_template = new templete($filename);
            $included_template->isInclude = true;
            $included_template->useCache = $this->useCache; // Inherit this objects cache usage setting

            ob_start();

                // Set all the main template variables
                foreach ($this->variables as $k => $v) {
                    $included_template->Set($k, $v);
                }

                // Set this included templates variables
                foreach ($attributes[1] as $k => $v) {
                    $name  = $v;
                    $value = $attributes[2][$k];

                    $included_template->Set($name, $value);
                }

                $included_template->Display();
                $output = ob_get_contents();
            ob_end_clean();
        } else {

            $output = '<p style="color: red; font-weight: bold">File does not exist: ' . $filename . '</p>';
        }

        return $output;
    }

    /**
    * Displays the template
    */
    public function Display($r = false)
    {
        /**
        * Try and get the template from the cache if it's there
        */
        $filename        = str_replace('//', '/', $this->path . $this->template);
        $cached_filename = $this->getCacheFileName();



        /**
        * Show the cached template. Depending on the browser and how the page was requested will control the
        * Cache-Control header that the the browser sends:
        */
        if (    $this->useCache
            AND file_exists($cached_filename)
            AND filemtime($filename) < filemtime($cached_filename)
            AND $contents = file_get_contents($cached_filename)
            AND @$_SERVER['HTTP_CACHE_CONTROL'] != 'no-cache') {

            if($r) return $contents;
            else echo $contents;

            return;
        }

        /**
        * Generate the template and show it
        */
        $filename = str_replace('//', '/', $filename);
        if (!file_exists($filename)) {
            trigger_error("Template ({$filename}) does not exist");
            exit;
        }

        $output = file_get_contents($filename);

        /**
        * First change all escaped curly braces to aan arbitrary string
        */
        $output = str_replace(array('\\{', '\\}'), array('RTEMPLATE_OPEN_BRACE', 'RTEMPLATE_CLOSE_BRACE'), $output);

        /**
        * Handle conditionals (ie if/else) If conditions cannot be nested
        */
        $output = preg_replace_callback('/{if ([^}]*)}(.*)(?:{else}([^}]*))?{\/if}/Uis', array($this, 'ifCallbackHandler'), $output);

        /**
        * This does straight replacement of include calls
        */
        $output = preg_replace_callback('/{include ([^}]+)}/is', array($this, 'incCallbackHandler'), $output);
        $output = str_replace(array('\\RTEMPLATE_OPEN_BRACE', '\\RTEMPLATE_CLOSE_BRACE'), array('RTEMPLATE_OPEN_BRACE', 'RTEMPLATE_CLOSE_BRACE'), $output);

        /**
        * Handle loops
        */
        foreach ($this->variables as $k => $var) {


            if (is_array($var) OR is_object($var)) {
                if (preg_match('/{foreach\s+\$?[a-z0-9_]+\s*}(.*){\/foreach}/Uis', $output, $matches)) {

                    $table =  '';

                    foreach ($var as $value) {

                        $replacement = $matches[1];

                        foreach ($value as $a => $b) {
                            $replacement = preg_replace('/{' . $a . '}/i', $b, $replacement);
                        }

                        $table .= $replacement;
                    }

                    $output = str_replace($matches[0], $table, $output);

                }
            } else {
                $output = str_replace('{' . $k . '}', $var, $output);
            }
        }

        /**
        * Send the output to the browser
        */
        //$output = preg_replace('/[^\\\\]{.*[^\\\\]}/i', '', $output);

        /**
        * Change the escaped culy brace arbitrary strings back to unescaped curly braces. But only do this if
        * it's not an included file
        */

        if (empty($this->isInclude)) {
            $output = str_replace(array('RTEMPLATE_OPEN_BRACE', 'RTEMPLATE_CLOSE_BRACE'), array('{', '}'), $output);
        }


        /**
        * Send the output to the browser
        */
        if($r) return $output;
        else echo $output;


        /**
        * Save the generated content to the cache, remembering to set the modified time appropriately
        */
        if ($this->useCache) {
            $filename = $this->getCacheFilename();
            $this->Debug('Saving output to: ' . $filename);
            file_put_contents($filename, $output);

            // Update the timestamp
            touch($filename, time());
        }

    }

    /**
    * Outputs debug message
    *
    * @param string $msg The debug message
    */
    private function Debug($msg)
    {
        $this->debugMessages[] = nl2br(str_replace(' ', '&nbsp;', $msg));
    }

    /**
    * Returns a cache filename based on the given filename
    */
    private function getCacheFileName()
    {
         $f = preg_replace('/\/+/', '/', ($this->cachePath ? $this->cachePath . '/template_' : '') . md5(file_get_contents($this->path . $this->template) . serialize($this->variables))) . '.html';
         return $f;
    }

    /**
    * Sets a variable, be it singular or building
    *
    * @param string $name  The name of the variable
    * @param mixed  $value The value of the variable
    */
    public function Set($var_name,$var_value = '')
    {
        if($var_value != ''){
            $this->variables[$var_name] = $var_value;

        }else{

            $var_names = array_map("trim", explode(',',$var_name)); // moonsoo, 2016-12-23

            foreach($var_names as $var_name)
            {
                global $$var_name;
                $this->variables[$var_name] = $$var_name;
            }


        }
    }
}