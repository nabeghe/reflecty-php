<?php namespace Nabeghe\Reflecty\Tests\Fixtures;

use Nabeghe\Reflecty\Tests\Fixtures\Traits\TraitParent;

class MyParent extends \stdClass
{
    use TraitParent;

    public const CONST_0 = 'VALUE_0';

    public function parent_method_1()
    {

    }
}