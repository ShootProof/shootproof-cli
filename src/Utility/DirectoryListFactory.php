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
 * Utility for getting a list of local directories
 */
class DirectoryListFactory
{
    /**
     * Directory option was specified from the command line
     */
    const SOURCE_COMMAND_LINE = 'command_line';

    /**
     * Directory option was specified from standard input
     */
    const SOURCE_STDIN = 'stdin';

    /**
     * Directory option was determined from the current working directory
     */
    const SOURCE_DEFAULT = 'cwd';

    /**
     * The source from which the directory option was specified
     * @var string
     */
    protected $source = self::SOURCE_DEFAULT;

    /**
     * List of directories
     * @var array
     */
    protected $dirList;

    /**
     * Returns a list of directories
     *
     * @return array
     */
    public function getList()
    {
        if ($this->dirList) {
            return $this->dirList;
        } else {
            return [ getcwd() ];
        }
    }

    /**
     * Loads a list of directories from the command line
     *
     * @param array $optionData Command line options
     * @param string $offset The option value to search for in $optionData
     */
    public function loadFromCommandline(array $optionData, $offset)
    {
        if (! empty($this->dirList)) {
            return;
        }

        // If a wildcard expression is given, PHP will automatically expand it.
        // We need to get all the elements at or after key value 2.
        $i = array_search($offset, array_keys($optionData));
        if ($i > -1) {
            $fileList = array_slice($optionData, $i);
            $this->dirList = [];
            foreach ($fileList as $path) {
                foreach ($this->excludeFiles($this->normalizePathExpression($path)) as $normalizedDir) {
                    array_push($this->dirList, $normalizedDir);
                }
            }
            $this->source = self::SOURCE_COMMAND_LINE;
        }
    }

    /**
     * Loads a list of directories from standard input
     *
     * @param StdinReader $reader Tool for reading lines from standard input data
     * @throws \RuntimeException if reading from stdin times out
     */
    public function loadFromStdin(StdinReader $reader)
    {
        if (! empty($this->dirList)) {
            return;
        }

        $dirList = [];

        try {
            $reader->read(function ($line) use (&$dirList) {
                foreach ($this->normalizePathExpression(trim($line)) as $dir) {
                    array_push($dirList, $dir);
                }
            });
        } catch (\RuntimeException $e) {
            if (! empty($dirList)) {
                // There was data, but the stream timed out
                throw $e;
            }
        }

        if ($dirList) {
            $this->dirList = $this->excludeFiles($dirList);
            $this->source = self::SOURCE_STDIN;
        }
    }

    /**
     * Takes an expression matching local files and returns an array of
     * real, absolute file paths for all paths matching $expression
     *
     * @param string $expression Expression to match local files
     * @return array
     */
    protected function normalizePathExpression($expression)
    {
        // expand tilde, expand wildcards, expand to absolute path
        return array_map(
            function ($path) {
                if ($normalizedPath = realpath($path)) {
                    return $normalizedPath;
                } else {
                    return $path;
                }
            },
            glob(new TildeExpander($expression), GLOB_NOCHECK)
        );
    }

    /**
     * Filters the directory list, excluding any paths that are files
     *
     * @param array $list List of file paths
     * @return array
     */
    protected function excludeFiles(array $list)
    {
        return array_filter($list, function ($path) {
            return ! is_file($path);
        });
    }
}
