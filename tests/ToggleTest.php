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
    public function shouldReturnFalseDefaultWhenCallIsActive()
    {
        $this->assertFalse($this->target->isActive('not-exist'));
        $this->assertTrue($this->target->isInactive('not-exist'));
    }

    public function invalidFeature()
    {
        return [
            [123],
            [3.14],
            [new \stdClass()],
        ];
    }

    /**
     * @test
     * @dataProvider invalidFeature
     */
    public function shouldThrowExceptionWithInvalidFeature($invalidFeature)
    {
        $this->setExpectedException(InvalidArgumentException::class, 'Feature key `name` must be string');

        $this->target->create($invalidFeature);
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
    public function shouldThrowExceptionWithInvalidProcessor($invalidProcessor)
    {
        $this->setExpectedException(InvalidArgumentException::class, 'Feature key `processor` must be callable');

        $this->target->create('foo', $invalidProcessor);
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
    public function shouldThrowExceptionWhenAddFeatureButFeatureExist()
    {
        $this->setExpectedException(RuntimeException::class, "Feature 'foo' is exist");

        $this->target->create('foo');
        $this->target->create('foo');
    }

    /**
     * @test
     */
    public function shouldFlushAllConfigWhenCallFlush()
    {
        $this->target->create('foo');

        $this->assertTrue($this->target->has('foo'));

        $this->target->flush();

        $this->assertFalse($this->target->has('foo'));
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
    public function shouldReturnDefaultValueWhenCallAttributeWhenNotFoundTheFeature()
    {
        $this->assertNull($this->target->attribute('not-exist', 'whatever'));
        $this->assertSame('same', $this->target->attribute('not-exist', 'whatever', 'same'));
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
    public function shouldThrowExceptionWhenCreateFeatureAndReturnNull()
    {
        $this->setExpectedException(RuntimeException::class);

        $this->target->create('foo', function () {
            return null;
        });

        $this->target->isActive('foo');
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
        $this->setExpectedException(RuntimeException::class, 'foo');

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

        $this->target->result([
            'foo' => true,
        ]);

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
    public function shouldReturnResultResultWhenGetTheResult()
    {
        $actual = $this->target
            ->create('f1', true)
            ->create('f2', false)
            ->result();

        $this->assertSame(['f1' => true, 'f2' => false], $actual);
    }

    /**
     * @test
     */
    public function shouldReturnResultResultWhenGetTheResultWithSomeExistPreserveResult()
    {
        $this->target
            ->create('f1', true)
            ->create('f2', false);

        $this->target->isActive('f1');

        $actual = $this->target->result();

        $this->assertSame(['f1' => true, 'f2' => false], $actual);
    }

    /**
     * @test
     */
    public function shouldReturnResultResultWhenPutTheResult()
    {
        $this->target
            ->create('f1')
            ->create('f2')
            ->result(['f1' => true, 'f2' => false]);

        $this->assertTrue($this->target->isActive('f1'));
        $this->assertFalse($this->target->isActive('f2'));
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

    /**
     * @test
     */
    public function shouldBeWorkWhenCallProcessor()
    {
        $this->target->create('f1', null, [
            'foo' => 'a',
        ]);

        $this->assertInternalType('callable', $this->target->processor('f1'));
    }

    /**
     * @covers \MilesChou\Toggle\Simplify\Toggle::when
     * @test
     */
    public function shouldBeWorkWhenCallWhen()
    {
        $this->target->create('f1', true, ['bar' => 'b']);

        $this->target->when('f1', function ($params, $context) {
            $this->assertSame('a', $context['foo']);
            $this->assertSame('b', $params['bar']);
        }, null, ['foo' => 'a']);
    }

    /**
     * @covers \MilesChou\Toggle\Simplify\Toggle::when
     * @test
     */
    public function shouldBeWorkWhenCallWhenWithDefault()
    {
        $this->target->create('f1', false, ['bar' => 'b']);

        $this->target->when(
            'f1',
            function () {
            },
            function () {
                $this->assertTrue(true);
            },
            ['foo' => 'a']
        );
    }

    /**
     * @covers \MilesChou\Toggle\Simplify\Toggle::when
     * @test
     */
    public function shouldBeWorkWhenCallWhenWithoutDefault()
    {
        $this->target->create('f1', false, ['bar' => 'b']);

        $actual = $this->target->when('f1', function () {
        });

        $this->assertInstanceOf(Toggle::class, $actual);
    }

    /**
     * @covers \MilesChou\Toggle\Simplify\Toggle::unless
     * @test
     */
    public function shouldBeWorkWhenCallUnless()
    {
        $this->target->create('f1', false, ['bar' => 'b']);

        $this->target->unless('f1', function ($params, $context) {
            $this->assertSame('a', $context['foo']);
            $this->assertSame('b', $params['bar']);
        }, null, ['foo' => 'a']);
    }

    /**
     * @covers \MilesChou\Toggle\Simplify\Toggle::unless
     * @test
     */
    public function shouldBeWorkWhenCallUnlessWithDefault()
    {
        $this->target->create('f1', true, ['bar' => 'b']);

        $this->target->unless(
            'f1',
            function () {
            },
            function () {
                $this->assertTrue(true);
            },
            ['foo' => 'a']
        );
    }

    /**
     * @covers \MilesChou\Toggle\Simplify\Toggle::unless
     * @test
     */
    public function shouldBeWorkWhenCallUnlessWithoutDefault()
    {
        $this->target->create('f1', true, ['bar' => 'b']);

        $actual = $this->target->unless('f1', function () {
        });

        $this->assertInstanceOf(Toggle::class, $actual);
    }
}
