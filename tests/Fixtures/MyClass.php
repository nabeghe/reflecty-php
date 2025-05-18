<?php namespace Nabeghe\Reflecty\Tests\Fixtures;

use Nabeghe\Reflecty\Tests\Fixtures\Attributes\CustomAttribute;
use Nabeghe\Reflecty\Tests\Fixtures\Traits\Trait1;
use Nabeghe\Reflecty\Tests\Fixtures\Traits\Trait2;

#[CustomAttribute]
class MyClass extends MyParent
{
    use Trait1, Trait2;

    public const CONST_1 = 'VALUE_1';
    public const CONST_2 = 'VALUE_2';
    private const PRIVATE_CONST_1 = 'PRIVATE_VALUE_1';

    public string $prop_1 = "value_1";
    private string $prop_private_1 = "private_value_1";
    private string $prop_protected_1 = "protected_value_1";

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