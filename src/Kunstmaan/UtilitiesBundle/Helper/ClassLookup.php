<?php

namespace Kunstmaan\UtilitiesBundle\Helper;

use Doctrine\ORM\Proxy\Proxy;

/**
 * Helper for looking up the class name, not the ORM proxy
 */
class ClassLookup
{
    /**
     * Get full class name of object (ie. class name including full namespace)
     *
     * @param mixed $object
     *
     * @return string the name of the class and if the given $object isn't a valid Object false will be returned.
     */
    public static function getClass($object)
    {
        return ($object instanceof Proxy) ? get_parent_class($object) : get_class($object);
    }

    /**
     * Get class name of object (ie. class name without namespace)
     *
     * @param mixed $object
     *
     * @return string
     */
    public static function getClassName($object)
    {
        $className = explode('\\', ClassLookup::getClass($object));

        return array_pop($className);
    }
}
