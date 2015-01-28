<?php

namespace ShootProof\Cli\Utility;

class ShootproofFile
{
    public $id = '';
    public $name = '';
    public $url = [];

    public function __construct($name = '', $id = '', $url = [])
    {
        $this->name = $name;
        $this->id = $id;
        $this->url = $url;
    }

    public function __toString()
    {
        return (string) $this->name;
    }

    public function arrayFactory(array $data)
    {
        extract($data);
        return new self($name, $id, $url);
    }

    public function stringFactory($name)
    {
        return new self($name);
    }
}
