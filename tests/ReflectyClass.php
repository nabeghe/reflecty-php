<?php declare(strict_types=1);

namespace Nabeghe\Reflecty\Tests;

use Nabeghe\Reflecty\Reflecty;

class ReflectyClass extends \PHPUnit\Framework\TestCase
{
    public function testClassNamespace()
    {
        $this->assertSame('Nabeghe\Reflecty\Tests', Reflecty::classNamespace(MyClass::class));
    }

    public function testClassFullname()
    {
        $this->assertSame('Nabeghe\Reflecty\Tests\MyClass', Reflecty::classFullname(MyClass::class));
    }

    public function testClassBasename()
    {
        $this->assertSame('MyClass', Reflecty::classBasename(MyClass::class));
    }

    public function testClassReflection()
    {
        $this->assertInstanceOf(\ReflectionClass::class, Reflecty::classReflection(MyClass::class));
    }

    public function testClassAncestors()
    {
        $this->assertSame([
            'Nabeghe\Reflecty\Tests\MyParent',
            'stdClass',
        ], Reflecty::classAncestors(MyClass::class));
    }

    public function testClassUsesRecursive()
    {
        $this->assertSame([
            'Nabeghe\Reflecty\Tests\TraitParent' => 'Nabeghe\Reflecty\Tests\TraitParent',
            'Nabeghe\Reflecty\Tests\Trait1' => 'Nabeghe\Reflecty\Tests\Trait1',
            'Nabeghe\Reflecty\Tests\Trait2' => 'Nabeghe\Reflecty\Tests\Trait2',
        ], Reflecty::classUsesRecursive(MyClass::class));
    }

    public function testTraitUsesRecursive()
    {
        $this->assertSame([
            'Nabeghe\Reflecty\Tests\Trait1' => 'Nabeghe\Reflecty\Tests\Trait1',
            'Nabeghe\Reflecty\Tests\Trait2' => 'Nabeghe\Reflecty\Tests\Trait2',
        ], Reflecty::traitUsesRecursive(MyTrait::class));
    }

    public function testConstants()
    {
        $this->assertSame([
            'CONST_1' => 'VALUE_1',
            'CONST_2' => 'VALUE_2',
            'PRIVATE_CONST_1' => 'PRIVATE_VALUE_1',
            'CONST_0' => 'VALUE_0', // from parent
        ], Reflecty::constants(MyClass::class));

        $this->assertSame([
            'CONST_1' => 'VALUE_1',
            'CONST_2' => 'VALUE_2',
            'CONST_0' => 'VALUE_0', // from parent
        ], Reflecty::constants(MyClass::class, 'public'));

        $this->assertSame([
            'PRIVATE_CONST_1' => 'PRIVATE_VALUE_1',
        ], Reflecty::constants(MyClass::class, 'private'));
    }

    public function testConstantExists()
    {
        $this->assertTrue(Reflecty::constantExists(MyClass::class, "CONST_0"));
        $this->assertTrue(Reflecty::constantExists(MyClass::class, "CONST_1"));
        $this->assertFalse(Reflecty::constantExists(MyClass::class, "CONST_20"));
    }

    public function testConstantNames()
    {
        $this->assertSame([
            'CONST_1',
            'CONST_2',
            'PRIVATE_CONST_1',
            'CONST_0',
        ], Reflecty::constantNames(MyClass::class));
    }

    public function testConstantValue()
    {
        $this->assertSame('VALUE_1', Reflecty::constantValue(MyClass::class, "CONST_1"));
        $this->assertSame('MY_VALUE', Reflecty::constantValue(MyClass::class, "MY_CONST", 'MY_VALUE'));
    }

    public function testPropertyAccessible()
    {
        $object = new MyClass();
        $this->assertTrue(Reflecty::propertyAccessible($object, "prop_1"));
        $this->assertFalse(Reflecty::propertyAccessible($object, "prop_private_1"));
        $this->assertFalse(Reflecty::propertyAccessible($object, "prop_protected_1"));
        $this->assertNull(Reflecty::propertyAccessible($object, "prop_not_found"));
    }

    public function testPropertiesCount()
    {
        $object = new MyClass();
        $this->assertSame(3, Reflecty::propertiesCount(MyClass::class));
        $this->assertSame(3, Reflecty::propertiesCount($object));
    }

    public function testHasProperty()
    {
        $object = new MyClass();
        $this->assertTrue(Reflecty::hasProperty(MyClass::class, 'prop_1'));
        $this->assertFalse(Reflecty::hasProperty($object, 'prop_not_found'));
    }

    public function testGetProperty()
    {
        $object = new MyClass();
        $this->assertInstanceOf(\ReflectionProperty::class, Reflecty::getProperty($object, 'prop_1'));
    }

    public function testGetPropertyValue()
    {
        $object = new MyClass();
        $this->assertSame('value_1', Reflecty::getPropertyValue($object, 'prop_1'));
    }

    public function testMethodsCount()
    {
        $object = new MyClass();
        $this->assertSame(4, Reflecty::methodsCount($object, 'prop_1'));
    }
}

trait  Trait1
{
}

trait  Trait2
{
}

trait  TraitParent
{
}

trait  MyTrait
{
    use Trait1, Trait2;
}

class MyParent extends \stdClass
{
    use TraitParent;

    public const CONST_0 = 'VALUE_0';

    public function parent_method_1()
    {

    }
}

class MyClass extends MyParent
{
    use Trait1, Trait2;

    public const CONST_1 = 'VALUE_1';
    public const CONST_2 = 'VALUE_2';
    private const PRIVATE_CONST_1 = 'PRIVATE_VALUE_1';

    public $prop_1 = "value_1";
    private $prop_private_1 = "private_value_1";
    private $prop_protected_1 = "protected_value_1";

    public function method_1()
    {
    }

    public function method_2()
    {
    }

    public function private_method_1()
    {
    }
}