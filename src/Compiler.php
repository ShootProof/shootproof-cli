<?php

namespace ShootProof\Cli;

use Symfony\Component\Finder\Finder;

/**
 * The Compiler class compiles shootproof-cli into a phar
 */
class Compiler
{
    /**
     * Compiles shootproof-cli into a single phar file
     *
     * @throws \RuntimeException
     * @param string $pharFile The full path to the file to create
     */
    public function compile($pharFile = 'shootproof-cli.phar')
    {
        if (file_exists($pharFile)) {
            unlink($pharFile);
        }

        $phar = new \Phar($pharFile, 0, 'shootproof-cli.phar');
        $phar->setSignatureAlgorithm(\Phar::SHA1);

        $phar->startBuffering();

        $finder = $this->getFinder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->notName('Compiler.php')
            ->in(__DIR__)
        ;

        foreach ($finder as $file) {
            $this->addFile($phar, $file);
        }

        $finder = $this->getFinder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->exclude('Tests')
            ->in(__DIR__ . '/../vendor/')
        ;

        foreach ($finder as $file) {
            $this->addFile($phar, $file);
        }

        $this->addFile($phar, new \SplFileInfo(__DIR__ . '/../bin/config.php'));
        $this->addBin($phar);

        // Stubs
        $phar->setStub($this->getStub());

        $phar->stopBuffering();

        $this->addFile($phar, new \SplFileInfo(__DIR__ . '/../LICENSE'), false);

        unset($phar);
    }

    private function addFile($phar, $file, $strip = true)
    {
        $path = str_replace(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR, '', $file->getRealPath());

        $content = file_get_contents($file);
        if ($strip) {
            $content = $this->stripWhitespace($content);
        } elseif ('LICENSE' === basename($file)) {
            $content = "\n" . $content . "\n";
        }

        $phar->addFromString($path, $content);
    }

    private function addBin($phar)
    {
        $content = file_get_contents(__DIR__ . '/../bin/shootproof-cli');
        $content = preg_replace('{^#!/usr/bin/env php\s*}', "", $content);
        $phar->addFromString('shootproof-cli/bin/shootproof-cli', $content);
    }

    /**
     * Removes whitespace from a PHP source string while preserving line numbers.
     *
     * @param  string $source A PHP string
     * @return string The PHP string with the whitespace removed
     */
    private function stripWhitespace($source)
    {
        if (!function_exists('token_get_all')) {
            return $source;
        }

        $output = "";
        foreach (token_get_all($source) as $token) {
            if (is_string($token)) {
                $output .= $token;
            } elseif (in_array($token[0], array(T_COMMENT, T_DOC_COMMENT))) {
                $output .= str_repeat("\n", substr_count($token[1], "\n"));
            } elseif (T_WHITESPACE === $token[0]) {
                // reduce wide spaces
                $whitespace = preg_replace("{[ \t]+}", " ", $token[1]);
                // normalize newlines to \n
                $whitespace = preg_replace("{(?:\r\n|\r|\n)}", "\n", $whitespace);
                // trim leading spaces
                $whitespace = preg_replace("{\n +}", "\n", $whitespace);
                $output .= $whitespace;
            } else {
                $output .= $token[1];
            }
        }

        return $output;
    }

    private function getStub()
    {
        $stub = <<<EOF
#!/usr/bin/env php
<?php
Phar::mapPhar('shootproof-cli.phar');
require 'phar://shootproof-cli.phar/shootproof-cli/bin/shootproof-cli';
__HALT_COMPILER();
EOF;

        return $stub;
    }

    /**
     * Returns a Finder object for finding files/dirs
     *
     * @return \Symfony\Component\Finder\Finder
     */
    public function getFinder()
    {
        return new Finder();
    }
}
