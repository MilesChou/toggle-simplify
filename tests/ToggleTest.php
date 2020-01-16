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

    protected function setUp()
    {
        $this->target = new Toggle();
    }

    protected function tearDown()
    {
        $this->target = null;
    }

    /**
     * @test
     * @expectedException RuntimeException
     */
    public function shouldThrowExceptionWhenCallIsActiveWithNoDataAndStrictMode()
    {
        $this->target->setStrict(true);

        $this->target->isActive('not-exist');
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Feature key 'processor' is not found
     */
    public function shouldThrowExceptionWithoutFeatureProcessor()
    {
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
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Feature key 'params' must be array
     */
    public function shouldThrowExceptionWithFeatureParamsNotArray($invalidParams)
    {
        $this->target->add('whatever', [
            'processor' => function () {
                return true;
            },
            'params' => $invalidParams,
        ]);
    }

    /**
     * @test
     * @expectedException RuntimeException
     * @expectedExceptionMessage Feature 'not-exist' is not found
     */
    public function shouldThrowExceptionWhenGetFeatureAndFeatureNotFound()
    {
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
     * @expectedException RuntimeException
     * @expectedExceptionMessage foo
     */
    public function shouldThrowExceptionWhenCreateFeatureAndRemoveFeature()
    {
        $this->target->setStrict(true);

        $this->target->create('foo')
            ->remove('foo');

        $this->target->isActive('foo');
    }
}
