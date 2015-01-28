<?php

namespace ShootProof\Cli\Utility;

class ShootproofFile
{
    protected $id = '';
    protected $name = '';
    protected $url = [];

    public function __construct($name = '', $id = '', $url = [])
    {
        $this->name = $name;
        $this->id = $id;
        $this->url = $url;
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function arrayFactory(array $data)
    {
        return new self($data['name'], $data['id'], $data['url']);
    }

    public function stringFactory($name)
    {
        return new self($name);
    }

    public function getName()
    {
        return (string) $this->name;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUrl($type)
    {
        return $this->url[$type];
    }
}
