<?php
namespace OyezTest\RuntimeTest;

class Sample_Class
{
    const A_CONSTANT = 'a constant value';

    public $aProperty;

    public function __construct($property_value = 'a property value')
    {
        $this->aProperty = $property_value;
    }

    public function aMethod($return_value = 'a method value')
    {
        return $return_value;
    }

    const RESET_STATIC_PROPERTY_VALUE = 'a static property value';

    public static $aStaticProperty = self::RESET_STATIC_PROPERTY_VALUE;

    public static function reset()
    {
        self::$aStaticProperty = self::RESET_STATIC_PROPERTY_VALUE;
    }

    public static function aStaticMethod($return_value = 'a static method value')
    {
        return $return_value;
    }
}

// END of file
