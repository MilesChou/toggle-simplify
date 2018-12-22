<?php

namespace Tests;

use InvalidArgumentException;
use MilesChou\Toggle\Simplify\Toggle;
use RuntimeException;

class ToggleTest extends \PHPUnit_Framework_TestCase
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
     */
    public function shouldThrowExceptionWhenCallIsActiveWithNoDataAndStrictMode()
    {
        $this->setExpectedException(RuntimeException::class);

        $this->target->setStrict(true);

        $this->target->isActive('not-exist');
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWithoutFeatureProcessor()
    {
        $this->setExpectedException(InvalidArgumentException::class, 'Feature key `processor` is not found');

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
        $this->setExpectedException(InvalidArgumentException::class, 'Feature key `params` must be array');

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
        $this->setExpectedException(RuntimeException::class, "Feature 'not-exist' is not found");

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
        $this->setExpectedException(RuntimeException::class, 'foo');

        $this->target->setStrict(true);

        $this->target->create('foo')
            ->remove('foo');

        $this->target->isActive('foo');
    }
}
