<?php declare(strict_types = 1);

namespace Tests\Utils\Messages\Transformers;

use AppBundle\Utils\Messages\Transformers\NewLineTransformer;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class NewLineTransformerTest extends TestCase
{
    /**
     * @var NewLineTransformer
     */
    private $newLineTransformer;

    protected function setUp()
    {
        parent::setUp();
        $this->newLineTransformer = new NewLineTransformer();
    }

    public function testTransformLine(): void
    {
        $this->assertSame(
            'test<br />test',
            $this->newLineTransformer->transformLine("test\ntest")
        );
        $this->assertSame(
            'test<br />test',
            $this->newLineTransformer->transformLine("test\r\ntest")
        );
        $this->assertSame(
            'test<br />test',
            $this->newLineTransformer->transformLine("test\rtest")
        );
        $this->assertSame(
            'test test',
            $this->newLineTransformer->transformLine("test test")
        );
        $this->assertSame(
            'test<br /><br />test',
            $this->newLineTransformer->transformLine("test\n\ntest")
        );
    }
}
