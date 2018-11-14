<?php

namespace Tests;

use Toggle;

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
        $this->setExpectedException('RuntimeException');

        $this->target->setStrict(true);

        $this->target->isActive('not-exist');
    }

    /**
     * @test
     */
    public function shouldReturnFalseDefaultWhenCallIsActive()
    {
        $this->assertFalse($this->target->isActive('not-exist'));
    }

    public function invalidProcessor()
    {
        return [
            [123],
            [3.14],
            [''],
            ['str'],
            [new \stdClass()],
        ];
    }

    /**
     * @test
     * @dataProvider invalidProcessor
     */
    public function shouldThrowExceptionWithInvalidFeature($invalidProcessor)
    {
        $this->setExpectedException('InvalidArgumentException', 'Feature key `processor` must be callable');

        $this->target->create('foo', $invalidProcessor);
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenGetFeatureAndFeatureNotFound()
    {
        $this->setExpectedException('RuntimeException', "Feature 'not-exist' is not found");

        $this->target->feature('not-exist');
    }

    /**
     * @test
     */
    public function shouldBeOkayWhenCreateWithProcessorInsteadOfParam()
    {
        $this->target->create('foo', null, ['some' => 'thing']);

        $this->assertSame('thing', $this->target->params('foo', 'some'));
    }

    /**
     * @test
     */
    public function shouldReturnTrueWhenCreateFeatureAndReturnTrue()
    {
        $this->target->create('foo', function () {
            return true;
        });

        $this->assertTrue($this->target->isActive('foo'));
    }

    /**
     * @test
     */
    public function shouldReturnTrueWhenCreateFeatureAndReturnFalse()
    {
        $this->target->create('foo', function () {
            return false;
        });

        $this->assertFalse($this->target->isActive('foo'));
    }

    /**
     * @test
     */
    public function shouldReturnTrueWhenCreateFeatureUsingStaticAndReturnTrue()
    {
        $this->target->create('foo', true);

        $this->assertTrue($this->target->isActive('foo'));
    }

    /**
     * @test
     */
    public function shouldReturnTrueWhenCreateFeatureUsingStaticAndReturnFalse()
    {
        $this->target->create('foo', false);

        $this->assertFalse($this->target->isActive('foo'));
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenCreateFeatureAndRemoveFeature()
    {
        $this->setExpectedException('RuntimeException', 'foo');

        $this->target->setStrict(true);

        $this->target->create('foo')
            ->remove('foo');

        $this->target->isActive('foo');
    }

    /**
     * @test
     */
    public function shouldReturnSameResultWhenIsActiveWithDifferentContext()
    {
        $this->target->create('f1', function ($context) {
            return 0 === $context['id'] % 2;
        });

        $this->assertTrue($this->target->isActive('f1', ['id' => 2]));
        $this->assertTrue($this->target->isActive('f1', ['id' => 3]));
    }

    /**
     * @test
     */
    public function shouldReturnDifferentResultWhenIsActiveWithDifferentContextWithoutPreserve()
    {
        $this->target->create('f1', function ($context) {
            return 0 === $context['id'] % 2;
        });

        $this->target->setPreserve(false);

        $this->assertTrue($this->target->isActive('f1', ['id' => 2]));
        $this->assertFalse($this->target->isActive('f1', ['id' => 3]));
    }

    /**
     * @test
     */
    public function shouldReturnStaticResultWhenCreateFeatureUsingStatic()
    {
        $this->target->create('foo', null, [], false);

        $data = [
            'foo' => true,
        ];

        $this->target->result($data);

        $this->assertFalse($this->target->isActive('foo'));
    }


    /**
     * @test
     */
    public function shouldReturnCorrectResultWhenCreateFromConfigBasic()
    {
        $actual = Toggle::createFromArray([
            'f1' => [],
            'f2' => [],
            'f3' => [
                'processor' => true
            ],
        ]);

        $this->assertInstanceOf(Toggle::class, $actual);
        $this->assertFalse($actual->isActive('f1'));
        $this->assertFalse($actual->isActive('f2'));
        $this->assertTrue($actual->isActive('f3'));
    }

    /**
     * @test
     */
    public function shouldReturnCorrectResultWhenCreateFromConfigBasicWithProcessor()
    {
        $actual = Toggle::createFromArray([
            'f1' => [],
            'f2' => [],
            'f3' => [
                'processor' => function () {
                    return true;
                },
            ],
        ]);

        $this->assertInstanceOf(Toggle::class, $actual);
        $this->assertFalse($actual->isActive('f1'));
        $this->assertFalse($actual->isActive('f2'));
        $this->assertTrue($actual->isActive('f3'));
    }

    /**
     * @test
     */
    public function shouldReturnCorrectResultWhenCreateFromConfigProcessWithStaticResult()
    {
        $actual = Toggle::createFromArray([
            'f1' => [
                'staticResult' => false,
            ],
        ]);

        $this->assertInstanceOf(Toggle::class, $actual);
        $this->assertFalse($actual->isActive('f1'));

        $actual->result(['f1' => true]);

        $this->assertFalse($actual->isActive('f1'));
    }

    /**
     * @test
     */
    public function shouldReturnCorrectResultWhenCreateFromArrayWithEmptyData()
    {
        $actual = Toggle::createFromArray([]);

        $this->assertEmpty($actual->all());
    }

    /**
     * @test
     */
    public function shouldBeWorkWhenCallParams()
    {
        $this->target->create('f1', null, [
            'foo' => 'a',
        ]);

        $this->assertSame(['foo' => 'a'], $this->target->params('f1'));
        $this->assertSame('a', $this->target->params('f1', 'foo'));

        $this->target->params('f1', ['bar' => 'b']);

        $this->assertSame(['foo' => 'a', 'bar' => 'b'], $this->target->params('f1'));
        $this->assertSame('a', $this->target->params('f1', 'foo'));
        $this->assertSame('b', $this->target->params('f1', 'bar'));
    }
}
