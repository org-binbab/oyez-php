<?php
namespace OyezTest\RuntimeTest;

class Sample_Class
{
    public $aProperty = 'a property value';

    public function aMethod()
    {
        return 'a method value';
    }

    public static $aStaticProperty = 'a static property value';

    public static function reset()
    {
        self::$aStaticProperty = 'a static property value';
    }

    public static function aStaticMethod()
    {
        return 'a static method value';
    }
}

// END of file
