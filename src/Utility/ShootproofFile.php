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

/**
 * Represents a ShootProof file
 */
class ShootproofFile
{
    /**
     * ShootProof ID of the file
     * @var int
     */
    protected $id = '';

    /**
     * File name
     * @var string
     */
    protected $name = '';


    /**
     * Array of URLs for the file
     *
     * <pre>
     * [
     *     'small' => 'http://...',
     *     'medium' => 'http://...',
     *     'large' => 'http://...',
     *     'original' => 'http://...',
     * ]
     * </pre>
     *
     * @var array
     */
    protected $url = [];

    /**
     * Constructs a ShootProof file object
     *
     * @param string $name The file name
     * @param string $id The ShootProof ID for the file
     * @param array $url An array of URLs for the file (refer to self::$url)
     * @see self::$url
     */
    public function __construct($name = '', $id = '', $url = [])
    {
        $this->name = $name;
        $this->id = $id;
        $this->url = $url;
    }

    /**
     * Converts a ShootProof file to a string representation
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Creates a new ShootProof file from an array of data
     *
     * @param array $data An associative array containing name, id, and url data
     * @return self
     */
    public function arrayFactory(array $data)
    {
        return new self($data['name'], $data['id'], $data['url']);
    }

    /**
     * Creates a new ShootProof file from a file name
     *
     * @param string $name File name
     * @return self
     */
    public function stringFactory($name)
    {
        return new self($name);
    }

    /**
     * Gets the file name
     *
     * @return string
     */
    public function getName()
    {
        return (string) $this->name;
    }

    /**
     * Gets the ShootProof ID for the file
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets the URL identified by $type
     *
     * @return string
     * @see self::$url
     */
    public function getUrl($type)
    {
        return $this->url[$type];
    }
}
