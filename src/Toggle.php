<?php

class Toggle
{
    /**
     * @var array
     */
    private $features = [];

    /**
     * @var bool
     */
    private $preserve = true;

    /**
     * @var array
     */
    private $preserveResult = [];

    /**
     * @var bool
     */
    private $strict = false;

    /**
     * @param array $config
     * @return static
     */
    public static function createFromArray(array $config)
    {
        $toggle = new static();

        if (empty($config)) {
            return $toggle;
        }

        foreach ($config as $name => $item) {
            $item = static::normalizeConfigItem($item);

            $toggle->create(
                $name,
                $item['processor'],
                $item['params'],
                $item['staticResult']
            );
        }

        return $toggle;
    }

    /**
     * @param array $feature
     * @throws InvalidArgumentException
     */
    private static function assertFeature(array $feature)
    {
        if (!array_key_exists('name', $feature)) {
            throw new InvalidArgumentException('Feature key `name` is not found');
        }

        if (!is_string($feature['name'])) {
            throw new InvalidArgumentException('Feature key `name` must be string');
        }

        if (!array_key_exists('processor', $feature)) {
            throw new InvalidArgumentException('Feature key `processor` is not found');
        }

        if (!is_callable($feature['processor'])) {
            throw new InvalidArgumentException('Feature key `processor` must be callable');
        }

        if (isset($feature['params']) && !is_array($feature['params'])) {
            throw new InvalidArgumentException('Feature key `params` must be array');
        }
    }

    /**
     * @param array $config
     * @return array
     */
    private static function normalizeConfigItem($config)
    {
        if (!isset($config['processor'])) {
            $config['processor'] = null;
        }

        if (!isset($config['params'])) {
            $config['params'] = [];
        }

        if (!isset($config['staticResult']) || !is_bool($config['staticResult'])) {
            $config['staticResult'] = null;
        }

        return $config;
    }

    /**
     * @param array $feature
     * @return static
     */
    public function add(array $feature)
    {
        static::assertFeature($feature);

        if ($this->has($feature['name'])) {
            throw new RuntimeException("Feature '{$feature['name']}' is exist");
        }

        $this->features[$feature['name']] = $feature;

        return $this;
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->features;
    }

    /**
     * @param array $features
     * @return static
     */
    public function append(array $features)
    {
        foreach ($features as $feature) {
            $this->add($feature);
        }

        return $this;
    }

    /**
     * @param string $key
     * @param string $name
     * @param mixed|null $value
     * @return mixed|static
     */
    public function attribute($name, $key, $value = null)
    {
        $this->assertFeatureExist($name);

        $feature = $this->feature($name);

        if (null === $value) {
            return $feature[$key];
        }

        $feature[$key] = $value;

        return $this->put($feature);
    }

    /**
     * @param string $name
     * @param callable|bool|null $processor
     * @param array $params
     * @param bool|null $staticResult
     * @return static
     */
    public function create($name, $processor = null, array $params = [], $staticResult = null)
    {
        // default is false
        if (null === $processor) {
            $processor = false;
        }

        if (is_bool($processor)) {
            $processor = function () use ($processor) {
                return $processor;
            };
        }

        return $this->add([
            'name' => $name,
            'processor' => $processor,
            'params' => $params,
            'staticResult' => $staticResult,
        ]);
    }

    /**
     * @param string $name
     * @return array
     * @throws RuntimeException
     */
    public function feature($name)
    {
        $this->assertFeatureExist($name);

        return $this->features[$name];
    }

    /**
     * @return void
     */
    public function flush()
    {
        $this->features = [];
        $this->preserveResult = [];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return array_key_exists($name, $this->features);
    }

    /**
     * @param string $name
     * @param array $context
     * @return bool
     */
    public function isActive($name, $context = [])
    {
        if (!$this->has($name)) {
            if ($this->strict) {
                throw new RuntimeException("Feature '{$name}' is not found");
            }

            return false;
        }

        $feature = $this->feature($name);

        if (isset($feature['staticResult'])) {
            return $feature['staticResult'];
        }

        if (isset($this->preserveResult[$name])) {
            return $this->preserveResult[$name];
        }

        $result = $this->process($feature, $context);

        if ($this->preserve) {
            $this->preserveResult[$name] = $result;
        }

        return $result;
    }

    /**
     * @return array
     */
    public function names()
    {
        return array_keys($this->features);
    }

    /**
     * @param string $name
     * @param array|null $key
     * @return mixed|static
     */
    public function params($name, $key = null)
    {
        $params = $this->attribute($name, 'params');

        if (is_array($key)) {
            $params = array_merge($params, $key);

            return $this->attribute($name, 'params', $params);
        }

        if (null === $key) {
            return $params;
        }

        return $params[$key];
    }

    /**
     * @param string $name
     * @param callable|null $processor
     * @return callable|static
     */
    public function processor($name, $processor = null)
    {
        return $this->attribute($name, 'processor', $processor);
    }

    /**
     * @param array $feature
     * @return static
     */
    public function put(array $feature)
    {
        static::assertFeature($feature);

        $this->features[$feature['name']] = $feature;

        return $this;
    }

    /**
     * @param string $name
     */
    public function remove($name)
    {
        unset($this->features[$name], $this->preserveResult[$name]);
    }

    /**
     * Import / export result data
     *
     * @param array|null $result
     * @return array
     */
    public function result(array $result = null)
    {
        if (null === $result) {
            return array_reduce($this->names(), function ($carry, $feature) {
                $carry[$feature] = isset($this->preserveResult[$feature])
                    ? $this->preserveResult[$feature]
                    : $this->isActive($feature);

                return $carry;
            }, []);
        }

        foreach ($result as $name => $feature) {
            $this->assertFeatureExist($name);
        }

        $this->preserveResult = array_merge($this->preserveResult, $result);

        return $result;
    }

    /**
     * @param array $features
     * @return static
     */
    public function set(array $features)
    {
        $this->flush();
        $this->append($features);

        return $this;
    }

    /**
     * @param bool $preserve
     * @return static
     */
    public function setPreserve($preserve)
    {
        $this->preserve = $preserve;

        return $this;
    }

    /**
     * @param bool $strict
     * @return static
     */
    public function setStrict($strict)
    {
        $this->strict = $strict;

        return $this;
    }

    /**
     * When $feature on, then call $callable
     *
     * @param string $name
     * @param callable $callable
     * @param array $context
     *
     * @return static
     */
    public function when($name, callable $callable, array $context = [])
    {
        if ($this->isActive($name, $context)) {
            $callable($context, $this->params($name));
        }

        return $this;
    }

    /**
     * @param $name
     */
    private function assertFeatureExist($name)
    {
        if (!$this->has($name)) {
            throw new RuntimeException("Feature '{$name}' is not found");
        }
    }

    /**
     * @param array $feature
     * @param array $context
     * @return mixed
     */
    private function process(array $feature, array $context)
    {
        $result = call_user_func($feature['processor'], $context, $feature['params']);

        if (!is_bool($result)) {
            throw new RuntimeException('Processed result is not valid');
        }

        return $result;
    }
}
