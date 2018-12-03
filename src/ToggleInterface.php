<?php

namespace MilesChou\Toggle\Simplify;

interface ToggleInterface
{
    /**
     * @return array
     */
    public function all();

    /**
     * @param string $name
     * @param string $key
     * @param mixed|null $value
     * @return mixed|static
     */
    public function attribute($name, $key, $value = null);

    /**
     * @param string $name
     * @param callable|bool|null $processor
     * @param array $params
     * @param bool|null $staticResult
     * @return static
     */
    public function create($name, $processor = null, array $params = [], $staticResult = null);

    /**
     * @return void
     */
    public function flush();

    /**
     * @param string $name
     * @return bool
     */
    public function has($name);

    /**
     * @param string $name
     * @param array $context
     * @return bool
     */
    public function isActive($name, array $context = []);

    /**
     * @param string $name
     * @param array $context
     * @return bool
     */
    public function isInactive($name, array $context = []);

    /**
     * @return array
     */
    public function names();

    /**
     * @param string $name
     * @param array|null $key
     * @return mixed|static
     */
    public function params($name, $key = null);

    /**
     * @param string $name
     * @param callable|null $processor
     * @return callable|static
     */
    public function processor($name, $processor = null);

    /**
     * @param string $name
     * @return void
     */
    public function remove($name);

    /**
     * Import / export result data
     *
     * @param array|null $result
     * @return array|static
     */
    public function result(array $result = null);

    /**
     * When $feature on, then call $callable
     *
     * @param string $name
     * @param callable $callback
     * @param callable $default
     * @param array $context
     * @return mixed|static
     */
    public function when($name, callable $callback, callable $default = null, array $context = []);

    /**
     * Unless $feature on, otherwise call $callable
     *
     * @param string $name
     * @param callable $callback
     * @param callable $default
     * @param array $context
     * @return mixed|static
     */
    public function unless($name, callable $callback, callable $default = null, array $context = []);
}
