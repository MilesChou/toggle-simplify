<?php

namespace Tests;

use Generator;
use InvalidArgumentException;
use MilesChou\Toggle\Simplify\Toggle;
use MilesChou\Toggle\Simplify\ToggleInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

class ToggleBasicTest extends TestCase
{
    /**
     * @var ToggleInterface
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
            [new stdClass()],
        ];
    }

    /**
     * @test
     * @dataProvider invalidFeature
     */
    public function shouldThrowExceptionWithInvalidFeature($invalidFeature)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Feature key 'name' must be string");

        $this->target->create($invalidFeature);
    }

    public function invalidProcessor(): Generator
    {
        yield [123];
        yield [3.14];
        yield [''];
        yield ['str'];
        yield [new stdClass()];
    }

    /**
     * @test
     * @dataProvider invalidProcessor
     */
    public function shouldThrowExceptionWithInvalidProcessor($invalidProcessor)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Feature key 'processor' must be callable");

        $this->target->create('foo', $invalidProcessor);
    }

    public function invalidParams(): Generator
    {
        yield [null];
        yield [true];
        yield [false];
        yield [123];
        yield [3.14];
        yield [''];
        yield ['str'];
        yield [new stdClass()];
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenAddFeatureButFeatureExist()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Feature 'foo' is exist");

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
    public function shouldReturnDefaultWhenCreateWithoutParam()
    {
        $this->target->create('foo', null, ['some' => 'thing']);

        $this->assertSame('default', $this->target->params('foo', 'not-exist', 'default'));
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
    public function shouldReturnFalseWhenFeatureIsNotInitialButResultSet()
    {
        $this->target->create('f1', true);

        $this->target->result([
            'f2' => true,
        ]);

        $actual = $this->target->result();

        $this->assertArrayHasKey('f1', $actual);
        $this->assertArrayNotHasKey('f2', $actual);
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenCreateFeatureAndReturnNull()
    {
        $this->expectException(RuntimeException::class);

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
    public function shouldReturnSameResultWhenIsActiveWithDifferentContextBecausePreserveResult()
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
    public function shouldReturnResultWhenIsActiveWithDefaultContext()
    {
        $this->target->context(['id' => 2]);
        $this->target->create('f1', function ($context) {
            return 0 === $context['id'] % 2;
        });

        $this->assertTrue($this->target->isActive('f1'));
    }

    /**
     * @test
     */
    public function shouldOverwriteDefaultContextWhenPassWithCustomContext()
    {
        $this->target->context(['id' => 2]);
        $this->target->create('f1', function ($context) {
            return 0 === $context['id'] % 2;
        });

        $this->assertFalse($this->target->isActive('f1', ['id' => 3]));
    }

    /**
     * @test
     */
    public function shouldReturnDifferentResultWhenCallIsActiveAfterDuplicateWithPreserveParameter()
    {
        $this->target->create('f1', function ($context) {
            return 0 === $context['id'] % 2;
        });

        $this->assertTrue($this->target->isActive('f1', ['id' => 2]));

        $this->assertFalse($this->target->duplicate(false)->isActive('f1', ['id' => 3]));
        $this->assertTrue($this->target->duplicate(true)->isActive('f1', ['id' => 3]));
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
                'processor' => true,
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
                'static' => false,
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
