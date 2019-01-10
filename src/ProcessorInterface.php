<?php

namespace MilesChou\Toggle\Simplify;

interface ProcessorInterface
{
    /**
     * @param mixed $config
     */
    public function setConfig($config);

    /**
     * @return array
     */
    public function toArray();
}
