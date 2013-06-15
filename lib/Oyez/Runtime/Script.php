<?php
/**
 * "Oyez" - The Universal Format Library
 *
 * @author      BinaryBabel OSS (<projects@binarybabel.org>)
 * @link        http://code.binbab.org
 * @copyright   Copyright 2013 sha1(OWNER) = df334a7237f10846a0ca302bd323e35ee1463931
 * @license     GNU Lesser General Public License v3 (see LICENSE and COPYING files)
 */

namespace Oyez\Runtime;

class Script
{
    public $main;

    public function __construct()
    {
        $this->main = array();
    }

    public function run($context=null)
    {
        return new Runner($this, $context);
    }

    /**
     * @param string $filename
     * @return Script
     * @throws Exception
     */
    public static function load_fromFile($filename)
    {
        if ( ! file_exists($filename)) {
            throw new Exception(
                "Attempted to load missing file. ($filename)",
                Exception::NOT_FOUND
            );
        }

        $content = file_get_contents($filename);
        $content = json_decode($content);
        if ( ! $content) {
            throw new Exception(
                "Failed to parse file. ($filename)",
                Exception::BAD_VALUE
            );
        }

        $script = new self();
        $script->main = $content->main;

        return $script;
    }
}

// END of file
