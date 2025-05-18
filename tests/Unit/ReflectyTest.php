<?php declare(strict_types=1);

namespace Nabeghe\Reflecty\Tests\Unit;

use Nabeghe\Reflecty\Reflecty;
use Nabeghe\Reflecty\Tests\Fixtures\Attributes\CustomAttribute;
use Nabeghe\Reflecty\Tests\Fixtures\Enums\SampleEnum;
use Nabeghe\Reflecty\Tests\Fixtures\MyClass;
use Nabeghe\Reflecty\Tests\Fixtures\Traits\MyTrait;

class ReflectyTest extends \PHPUnit\Framework\TestCase
{
    public function testClassNamespace()
    {
        $this->assertSame('Nabeghe\Reflecty\Tests\Fixtures', Reflecty::classNamespace(MyClass::class));
    }

    public function testClassFullname()
    {
        $this->assertSame('Nabeghe\Reflecty\Tests\Fixtures\MyClass', Reflecty::classFullname(MyClass::class));
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
            'Nabeghe\Reflecty\Tests\Fixtures\MyParent',
            'stdClass',
        ], Reflecty::classAncestors(MyClass::class));
    }

    public function testClassUsesRecursive()
    {
        $this->assertSame([
            'Nabeghe\Reflecty\Tests\Fixtures\Traits\TraitParent' => 'Nabeghe\Reflecty\Tests\Fixtures\Traits\TraitParent',
            'Nabeghe\Reflecty\Tests\Fixtures\Traits\Trait1' => 'Nabeghe\Reflecty\Tests\Fixtures\Traits\Trait1',
            'Nabeghe\Reflecty\Tests\Fixtures\Traits\Trait2' => 'Nabeghe\Reflecty\Tests\Fixtures\Traits\Trait2',
        ], Reflecty::classUsesRecursive(MyClass::class));
    }

    public function testTraitUsesRecursive()
    {
        $this->assertSame([
            'Nabeghe\Reflecty\Tests\Fixtures\Traits\Trait1' => 'Nabeghe\Reflecty\Tests\Fixtures\Traits\Trait1',
            'Nabeghe\Reflecty\Tests\Fixtures\Traits\Trait2' => 'Nabeghe\Reflecty\Tests\Fixtures\Traits\Trait2',
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
        $this->assertTrue(Reflecty::propertyExists(MyClass::class, 'prop_1'));
        $this->assertFalse(Reflecty::propertyExists($object, 'prop_not_found'));
    }

    public function testGetProperty()
    {
        $object = new MyClass();
        $this->assertInstanceOf(\ReflectionProperty::class, Reflecty::property($object, 'prop_1'));
    }

    public function testGetPropertyValue()
    {
        $object = new MyClass();
        $this->assertSame('value_1', Reflecty::propertyValue($object, 'prop_1'));
    }

    public function testMethodsCount()
    {
        $object = new MyClass();
        $this->assertSame(4, Reflecty::methodsCount($object));
    }

    public function testEnumByName()
    {
        $this->assertSame(SampleEnum::NAME_1, Reflecty::enumByName(SampleEnum::class, 'NAME_1'));
        $this->assertSame(null, Reflecty::enumByName(SampleEnum::class, '123'));
    }

    public function testEnumNames()
    {
        $this->assertSame([
            'NAME_1', 'NAME_2', 'NAME_3', 'NAME_4', 'NAME_5',
        ], Reflecty::enumNames(SampleEnum::class));
    }

    public function testEnumValues()
    {
        $this->assertSame([
            'Value 1', 'Value 2', 'Value 3', 'Value 4', 'Value 5',
        ], Reflecty::enumValues(SampleEnum::class));
    }

    public function testEnumEquals()
    {
        $this->assertTrue(Reflecty::enumEquals(SampleEnum::NAME_1, 'Value 1'));
        $this->assertFalse(Reflecty::enumEquals('Value 3', SampleEnum::NAME_4));
    }

    public function testAttribute()
    {
        $custom_attribute = Reflecty::attribute(MyClass::class);
        $this->assertInstanceOf(CustomAttribute::class, $custom_attribute);

        $custom_attribute = Reflecty::attribute(MyClass::class, CustomAttribute::class);
        $this->assertInstanceOf(CustomAttribute::class, $custom_attribute);
    }
}



