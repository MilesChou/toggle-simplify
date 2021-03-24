<?php

namespace Tests;

use InvalidArgumentException;
use MilesChou\Toggle\Simplify\Toggle;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ToggleTest extends TestCase
{
    /**
     * @var Toggle
     */
    private $target;

    protected function setUp(): void
    {
        $this->target = new Toggle();
    }

    protected function tearDown(): void
    {
        $this->target = null;
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenCallIsActiveWithNoDataAndStrictMode()
    {
        $this->expectException(RuntimeException::class);

        $this->target->setStrict(true);

        $this->target->isActive('not-exist');
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWithoutFeatureProcessor()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Feature key 'processor' is not found");

        $this->target->add('whatever', [
            'params' => [],
        ]);
    }

    public function invalidParams()
    {
        return [
            [null],
            [true],
            [false],
            [123],
            [3.14],
            [''],
            ['str'],
            [new \stdClass()],
        ];
    }

    /**
     * @test
     * @dataProvider invalidParams
     */
    public function shouldThrowExceptionWithFeatureParamsNotArray($invalidParams)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Feature key 'params' must be array");

        $this->target->add('whatever', [
            'processor' => function () {
                return true;
            },
            'params' => $invalidParams,
        ]);
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenGetFeatureAndFeatureNotFound()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Feature 'not-exist' is not found");

        $this->target->feature('not-exist');
    }

    /**
     * @test
     */
    public function shouldReturnDefaultValueWhenCallAttributeWhenNotFoundTheFeature()
    {
        $this->assertNull($this->target->attribute('not-exist', 'whatever'));
        $this->assertSame('same', $this->target->attribute('not-exist', 'whatever', 'same'));
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenCreateFeatureAndRemoveFeature()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('foo');

        $this->target->setStrict(true);

        $this->target->create('foo')
            ->remove('foo');

        $this->target->isActive('foo');
    }
}
