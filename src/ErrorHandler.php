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

namespace ShootProof\Cli;

use Monolog\ErrorHandler as MonologErrorHandler;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use ShootProof\Cli\Validators\ValidatorException;

/**
 * A custom error handler for dealing with ShootProof command line tool error messages
 */
class ErrorHandler
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var MonologErrorHandler
     */
    protected $monologHandler;

    /**
     * One of the LogLevel::* constants
     * @var string
     */
    protected $uncaughtExceptionLevel;

    /**
     * @var callable
     */
    protected $previousExceptionHandler;

    /**
     * Registers a new ErrorHandler for a given Logger
     *
     * By default it will handle errors, exceptions and fatal errors
     *
     * @param LoggerInterface $logger
     * @param array|false $errorLevelMap an array of E_* constant to LogLevel::* constant mapping,
     *     or false to disable error handling
     * @param int|false $exceptionLevel a LogLevel::* constant, or false to disable exception handling
     * @param int|false $fatalLevel a LogLevel::* constant, or false to disable fatal error handling
     * @return ErrorHandler
     */
    public static function register(LoggerInterface $logger, $errorLevelMap = array(), $exceptionLevel = null, $fatalLevel = null)
    {
        // Use our own exception handler, but continue to use Monolog's error and fatal handlers
        $monolog = MonologErrorHandler::register($logger, $errorLevelMap, false, $fatalLevel);
        $handler = new static($logger, $monolog);

        if ($exceptionLevel !== false) {
            // Our own exception handler
            $handler->registerExceptionHandler($exceptionLevel);
        }

        return $handler;
    }

    /**
     * @param LoggerInterface $logger A logger for capturing and writing error messages
     * @param MonologErrorHandler $monolog Monolog error handler for passing through to Monolog
     */
    public function __construct(LoggerInterface $logger, MonologErrorHandler $monolog)
    {
        $this->logger = $logger;
        $this->monologHandler = $monolog;
    }

    /**
     * Registers an exception handler
     *
     * @param string $level One of the LogLevel::* constants
     * @param boolean $callPrevious Whether to also call previous exceptions in the chain
     */
    public function registerExceptionHandler($level = null, $callPrevious = true)
    {
        $prev = set_exception_handler(array($this, 'handleException'));
        $this->uncaughtExceptionLevel = $level;
        if ($callPrevious && $prev) {
            $this->previousExceptionHandler = $prev;
        }
    }

    /**
     * Handles an exception
     *
     * @param \Exception $e The exception to handle
     */
    public function handleException(\Exception $e)
    {
        if ($e instanceof ValidatorException) {
            $this->logger->log(
                $this->uncaughtExceptionLevel === null ? LogLevel::ERROR : $this->uncaughtExceptionLevel,
                $e->getMessage(),
                []
            );
        } else {
            $this->monologHandler->handleException($e);
        }

        if ($this->previousExceptionHandler) {
            call_user_func($this->previousExceptionHandler, $e);
        }
    }
}
