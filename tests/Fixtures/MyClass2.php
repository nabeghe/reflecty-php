<?php namespace Nabeghe\Reflecty\Tests\Fixtures;

use Nabeghe\Reflecty\Meta;
use Nabeghe\Reflecty\Tests\Fixtures\Attributes\CustomAttribute;
use Nabeghe\Reflecty\Tests\Fixtures\Traits\Trait1;
use Nabeghe\Reflecty\Tests\Fixtures\Traits\Trait2;

#[CustomAttribute]
#[Meta(['reflecty' => ['homepage' => 'https://github.com/nabeghe/reflecty-php']])]
class MyClass2 extends MyClass
{
}