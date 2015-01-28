<?php
/**
 * This file is part of the ShootProof command line tool.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) ShootProof, LLC (https://www.shootproof.com)
 * @license http://opensource.org/licenses/MIT MIT
 */

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
