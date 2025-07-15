<?php namespace Nabeghe\Reflecty;

use ReflectionClass;
use ReflectionMethod;
use ReflectionException;

/**
 * Reflection helper.
 */
class Reflecty
{
    protected static array $cache = [];

    protected static int $cacheLimit = 10;

    public static function setCacheLimit(int $limit): void
    {
        static::$cacheLimit = $limit;
    }

    protected static function limitCache(string $key): void
    {
        if (static::$cacheLimit <= 0 || !isset(static::$cache[$key])) {
            return;
        }

        if (count(static::$cache[$key]) > static::$cacheLimit) {
            array_shift(static::$cache[$key]);
        }
    }

    /**
     * Returns the namespace of the given object/class.
     *
     * The class name itself is not included within the namespace.
     *
     * @param  object|string  $class
     * @return string
     */
    public static function classNamespace($class): string
    {
        $fullname = static::classFullname($class);

        $last_backslash_position = strrpos($class, '\\');
        if ($last_backslash_position === false) {
            return '';
        }

        return substr($fullname, 0, $last_backslash_position);
    }

    /**
     * Returns the class of the given object/class.
     * Similar to the {@see get_class()} function, but if it is not an object, it returns itself.
     *
     * @param  object|string  $class
     * @return string
     */
    public static function classFullname($class): string
    {
        return is_object($class) ? get_class($class) : $class;
    }

    /**
     * Returns the class "basename" of the given object/class.
     *
     * @param  string|object  $class
     * @return string
     */
    public static function classBasename($class): string
    {
        $class = static::classFullname($class);

        return basename(str_replace('\\', '/', $class));
    }

    /**
     * Returns a ReflectionClass instance for the given class/object.
     *
     * @param  object|string  $class
     * @param  bool  $referesh
     * @return ReflectionClass|null
     */
    public static function classReflection($class, bool $referesh = false): ?ReflectionClass
    {
        try {
            if (is_object($class)) {
                $class = get_class($class);
                if (isset(static::$cache['rc'][$class])) {
                    return static::$cache['rc'][$class];
                }
            }

            if (!isset(static::$cache['rc'])) {
                static::$cache['rc'] = [];
            }

            $r = new ReflectionClass($class);
            static::$cache['rc'][] = $r;

            static::limitCache('rc');

            return $r;
        } catch (ReflectionException $e) {
        }

        return null;
    }

    /**
     * Returns all parent classes of the given object/class from the most recent to the earliest.
     *
     * @param  object|string  $class
     * @return string[]
     */
    public static function classAncestors($class): array
    {
        $ancestors = [];
        while ($class = get_parent_class($class)) {
            $ancestors[] = $class;
        }

        return $ancestors;
    }

    /**
     * Returns all traits used by the given object/class, its parent classes and trait of their traits.
     *
     * @param  object|string  $class
     * @return array
     */
    public static function classUsesRecursive($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $results = [];

        foreach (array_reverse(class_parents($class) ?: []) + [$class => $class] as $class) {
            $results += static::traitUsesRecursive($class);
        }

        return array_unique($results);
    }

    /**
     * Returns all traits used by a trait and its traits.
     *
     * @param  object|string  $trait
     * @return array
     */
    public static function traitUsesRecursive($trait)
    {
        $traits = class_uses($trait) ?: [];

        foreach ($traits as $trait) {
            $traits += static::traitUsesRecursive($trait);
        }

        return $traits;
    }

    /**
     * Checks whether a trait is used in the given object/class or not.
     *
     * @param  object|string  $class
     * @param  string  $trait
     * @return bool
     */
    public static function traitExists($class, $trait): bool
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        return in_array($trait, static::classUsesRecursive($class));
    }

    /**
     * Returns all constants the given object/class as key-value pairs.
     *
     * @param  object|string  $class
     * @param  string|null  $visibility
     * @return array|null
     */
    public static function constants($class, $visibility = null)
    {
        try {
            $r = new ReflectionClass($class);
            $constants = $r->getConstants();

            if ($visibility) {
                if ($visibility === 'private') {
                    return array_filter($constants, function ($name) use ($r) {
                        return $r->getReflectionConstant($name)->isPrivate();
                    }, ARRAY_FILTER_USE_KEY);
                } elseif ($visibility === 'protected') {
                    return array_filter($constants, function ($name) use ($r) {
                        return $r->getReflectionConstant($name)->isProtected();
                    }, ARRAY_FILTER_USE_KEY);
                } elseif ($visibility === 'public') {
                    return array_filter($constants, function ($name) use ($r) {
                        return $r->getReflectionConstant($name)->isPublic();
                    }, ARRAY_FILTER_USE_KEY);
                }
            }

            return $constants;
        } catch (ReflectionException $e) {
            return null;
        }
    }

    /**
     * Checks whether a constant exists in the given object/class.
     *
     * @param  object|string  $class
     * @param  string  $constant
     * @return bool|null
     */
    public static function constantExists($class, $constant, $visibility = null)
    {
        if ($visibility) {
            $constantNames = static::constants($class, $visibility) ?? [];

            return in_array($constant, $constantNames);
        }

        return defined(static::classFullname($class).'::'.$constant);
    }

    /**
     * Returns the names of all constants of the given object/class.
     *
     * @param  object|string  $class
     * @return string[]|null
     */
    public static function constantNames($class, $visibility = null)
    {
        $consts = static::constants($class, $visibility);
        return $consts === null ? null : array_keys($consts);
    }

    /**
     * Returns the value of a constant from the given object/class.
     *
     * @param  object|string  $class
     * @param  string  $constant
     * @param  mixed  $default
     * @return mixed
     */
    public static function constantValue($class, $constant, $default = null)
    {
        $class = static::classFullname($class);

        if (static::constantExists($class, $constant)) {
            return constant($class.'::'.$constant);
        }

        return $default;
    }

    /**
     * Used to modify/retrieve access to a property of the given object/class.
     *
     * @param  object|string  $class
     * @param  string  $property
     * @param  bool|null  $accessible
     * @return bool|null
     */
    public static function propertyAccessible($class, $property, ?bool $accessible = null)
    {
        try {
            $r = new ReflectionClass($class);
            $prop = $r->getProperty($property);

            if ($accessible === null) {
                return $prop->isPublic();
            }

            $prop->setAccessible($accessible);

            return true;
        } catch (ReflectionException $e) {
            return null;
        }
    }

    /**
     * Returns the number of properties of the given object/class.
     *
     * @param  object|string  $class
     * @return int|null
     */
    public static function propertiesCount($class)
    {
        try {
            $r = new ReflectionClass($class);

            return count($r->getProperties());
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Checks whether a property exists in the given object/class.
     *
     * @param  object|string  $class
     * @param  string  $property
     * @return bool|null
     */
    public static function propertyExists($class, $property): ?bool
    {
        try {
            $r = new ReflectionClass($class);

            return $r->hasProperty($property);
        } catch (ReflectionException $e) {
            return null;
        }
    }

    /**
     * Returns a property of the given object/class.
     *
     * @param  object|string  $class
     * @param  string  $property
     * @param  mixed  $default
     * @return \ReflectionProperty|null
     */
    public static function property($class, $property, $default = null)
    {
        try {
            $r = new ReflectionClass($class);

            if ($r->hasProperty($property)) {
                return $r->getProperty($property);
            }
        } catch (ReflectionException $e) {
        }

        return $default;
    }

    public static function getProperty($class, $property, $default = null)
    {
        return static::property($class, $property, $default);
    }

    /**
     * Returns a property value of the given object/class.
     *
     * @param  object  $object
     * @param  string  $property
     * @param  mixed  $default
     * @return \ReflectionProperty|null
     */
    public static function propertyValue($object, $property, $default = null)
    {
        try {
            $r = new ReflectionClass($object);

            if ($r->hasProperty($property)) {
                return $r->getProperty($property)->getValue($object);
            }
        } catch (\Throwable $e) {
        }

        return $default;
    }

    public static function getPropertyValue($object, $property, $default = null)
    {
        return static::propertyValue($object, $property, $default);
    }

    /**
     * Returns the number of methods in a class/object.
     *
     * @param  object|string  $class
     * @return int|null
     */
    public static function methodsCount($class)
    {
        try {
            $r = new ReflectionClass($class);

            return count($r->getMethods());
        } catch (ReflectionException $e) {
        }

        return null;
    }

    /**
     * Used to modify/retrieve access to a method of the given object/class.
     *
     * @param  object|string  $class
     * @param  string  $method
     * @param  bool|null  $accessible
     * @return bool|null
     */
    public static function methodAccessible($class, $method, ?bool $accessible = null)
    {
        try {
            $r = new ReflectionClass($class);
            $method = $r->getMethod($method);

            if ($accessible === null) {
                return $method->isPublic();
            }

            $method->setAccessible($accessible);

            return true;
        } catch (ReflectionException $e) {
        }

        return null;
    }

    /**
     * Returns the parameters related to a method of the given object/class.
     *
     * @param  object|string  $class
     * @param  string  $method
     * @return \ReflectionParameter[]|null
     */
    public static function methodParams($class, $method)
    {
        try {
            $r = new ReflectionMethod($class, $method);

            return $r->getParameters();
        } catch (ReflectionException $e) {
            return null;
        }
    }

    /**
     * Returns the names of the parameters related to a method of the given object/class.
     *
     * @param  object|string  $class
     * @param  string  $method
     * @return string[]|null
     */
    public static function methodParamNames($class, $method)
    {
        $prams = static::methodParams($class, $method);

        return $prams === null ? null : array_map(fn($param) => $param->getName(), $prams);
    }

    /**
     * Returns the optional parameters related to a method of the given object/class.
     *
     * @param  object|string  $class
     * @param  string  $method
     * @return \ReflectionParameter[]|null
     */
    public static function methodOptionalParams($class, $method)
    {
        $params = static::methodParams($class, $method);
        if ($params === null) {
            return null;
        }

        $optionals = [];
        foreach ($params as $param) {
            if ($param->isOptional()) {
                $optionals[] = $param;
            }
        }

        return $optionals;
    }

    /**
     * Returns the names of the optional parameters related to a method of the given object/class.
     *
     * @param  object|string  $class
     * @param  string  $method
     * @return string[]|null
     */
    public static function methodOptionalParamNames($class, $method)
    {
        $params = static::methodOptionalParams($class, $method);

        return $params === null ? null : array_map(fn($param) => $param->getName(), $params);
    }

    /**
     * Returns the case of based on its name.
     *
     * @param  string  $enum  Enum class.
     * @param  string  $name  Case name.
     * @return false|null
     */
    public static function enumByName(string $enum, string $name)
    {
        if (!$enum || !function_exists('enum_exists') || !enum_exists($enum)) {
            return false;
        }

        /** @noinspection PhpUndefinedMethodInspection */
        foreach ($enum::cases() as $case) {
            if ($case->name === $name) {
                return $case;
            }
        }

        return null;
    }

    /**
     * Checks whether a name exists as a case in the enum or not.
     *
     * @param  string  $enum  Enum class.
     * @param  string  $name  Case name.
     * @return bool
     */
    public static function enumHasName(string $enum, string $name): bool
    {
        return static::enumByName($name, $enum) !== null;
    }

    /**
     * Returns an array of the enum case names.
     *
     * @param  string  $enum  Enum class.
     * @return string[]|null
     */
    public static function enumNames(string $enum)
    {
        if (!$enum || !function_exists('enum_exists') || !enum_exists($enum)) {
            return null;
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return array_column($enum::cases(), 'name');
    }

    /**
     * Returns an array of the enum case values.
     *
     * @param  string  $enum
     * @return array|null
     */
    public static function enumValues(string $enum)
    {
        if (!$enum || !function_exists('enum_exists') || !enum_exists($enum)) {
            return null;
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return array_column($enum::cases(), 'value');
    }

    /**
     * Compares two values or enum cases for equality.
     *
     * @param  object|string|int  $enum1  First enum case object, string, or int to compare.
     * @param  object|string|int  $enum2  Second enum case object, string, or int to compare.
     * @return bool
     * @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection
     */
    public static function enumEquals($enum1, $enum2): bool
    {
        if (PHP_VERSION_ID < 80000) {
            return false;
        }

        $getValue = fn($val) => ($val instanceof \UnitEnum && property_exists($val, 'value')) ? $val->value : $val;

        return $getValue($enum1) === $getValue($enum2);
    }

    /**
     * Returns instances of attributes for a class or method.
     *
     * @template TAttribute
     * @param  string|callable|array{0:string, 1:string}  $target  Class name or [0 => class, 1 => method]
     * @param  class-string<TAttribute>|null  $attributeClass  Filter attribute class
     * @return iterable<TAttribute>
     * @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection
     */
    public static function attributes($target, ?string $attributeClass = null): iterable
    {
        if (PHP_VERSION_ID < 80000) {
            return [];
        }

        try {
            if (is_string($target)) {
                if (class_exists($target)) {
                    $r = static::classReflection($target);
                } else {
                    $r = new ReflectionMethod($target);
                }
            } elseif (is_callable($target)) {
                if (is_array($target)) {
                    $r = new ReflectionMethod($target[0], $target[1] ?? null);
                } else {
                    $r = new ReflectionMethod($target);
                }
            } else {
                return [];
            }

            foreach ($r->getAttributes($attributeClass) as $attribute) {
                yield $attribute->newInstance();
            }
        } catch (ReflectionException) {
        }

        return [];
    }

    /**
     * Returns the first attribute instance of the given class/method.
     *
     * @template TAttribute
     * @param  string|callable|array{0:string, 1:string}  $target  Class name or [0 => class, 1 => method]
     * @param  class-string<TAttribute>|null  $attributeClass  Filter attribute class
     * @return TAttribute|null
     */
    public static function attribute($target, ?string $attributeClass = null)
    {
        foreach (self::attributes($target, $attributeClass) as $attr) {
            return $attr;
        }

        return null;
    }
}