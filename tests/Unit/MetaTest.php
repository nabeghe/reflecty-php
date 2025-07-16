<?php declare(strict_types=1);

use Nabeghe\Reflecty\Meta;
class MetaTest extends \PHPUnit\Framework\TestCase
{
    public Meta $meta;

    public function __construct()
    {
        parent::__construct();

        $this->meta = \Nabeghe\Reflecty\Reflecty::meta(\Nabeghe\Reflecty\Tests\Fixtures\MyClass2::class);
    }

    public function testSet()
    {
        $this->assertSame('nabeghe/reflecty', $this->meta->get('reflecty.name'));
    }

    public function testGet()
    {
        $this->assertSame('https://github.com/nabeghe/reflecty-php', $this->meta->get('reflecty.homepage'));
    }
}



