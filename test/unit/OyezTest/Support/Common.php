<?php
namespace OyezTest\Support;

class Common
{
    /**
     * Generate consistent mock class names, using a base class,
     * and one or more appended attributes.
     *
     * @param string $base_class
     * @param string $append,...
     * @return string
     */
    public static function mockClassName($base_class, $append)
    {
        if (is_array($append)) {
            $base_class = str_replace('\\', '_', $base_class);
            return $base_class . '_' . implode('_', $append);
        }

        $mockClass = implode('_', func_get_args());
        return str_replace('\\', '_', $mockClass);
    }

    public static function getProtectedProperty($object, $attribute)
    {
        $rp = new \ReflectionProperty($object, $attribute);
        $rp->setAccessible(true);
        return $rp->getValue($object);
    }

    public static function setProtectedProperty($object, $attribute, $value)
    {
        $rp = new \ReflectionProperty($object, $attribute);
        $rp->setAccessible(true);
        $rp->setValue($object, $value);
    }
}

// END of file
