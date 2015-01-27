<?php

namespace ShootProof\Cli\Utility;

class FileSetCalculator
{
    protected $a;
    protected $b;
    protected $replace;

    public function __construct(array $a, array $b, $replace = true)
    {
        $this->a = $a;
        $this->b = $b;
        $this->replace = $replace;
    }

    public function add()
    {
        return array_diff($this->a, $this->b);
    }

    public function remove()
    {
        return array_diff($this->b, $this->a);
    }

    public function replace()
    {
        return $this->replace ? array_intersect($this->a, $this->b) : [];
    }
}
