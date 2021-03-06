<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * source:
 * https://github.com/zendframework/zend-ldap/blob/release-2.8.0/src/ErrorHandler.php
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 *
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 *
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace App\Ldap;

/**
 * Handle Errors that might occur during execution of ldap_*-functions
 */
class ErrorHandler implements ErrorHandlerInterface
{
    /**
     * @var ErrorHandlerInterface The Errror-Handler instance
     */
    protected static $errorHandler;

    /**
     * Start the Error-Handling
     *
     * You can specify which errors to handle by passing a combination of PHPs
     * Error-constants like E_WARNING or E_NOTICE or E_WARNING ^ E_DEPRECATED
     *
     * @param int $level The Error-level(s) to handle by this ErrorHandler
     *
     * @return void
     */
    public static function start(int $level = E_WARNING): void
    {
        self::getErrorHandler()->startErrorHandling($level);
    }

    /**
     * @param bool|false $throw
     *
     * @return mixed
     */
    public static function stop(bool $throw = false)
    {
        return self::getErrorHandler()->stopErrorHandling($throw);
    }

    /**
     * This method does nothing on purpose.
     *
     * @param int $level
     *
     * @see ErrorHandlerInterface::startErrorHandling()
     *
     * @return void
     */
    public function startErrorHandling(int $level = E_WARNING): void
    {
        set_error_handler(function ($errNo, $errString) {
        });
    }

    /**
     * This method does nothing on purpose.
     *
     * @param bool|false $throw
     *
     * @see ErrorHandlerInterface::stopErrorHandling()
     *
     * @return void
     */
    public function stopErrorHandling(bool $throw = false): void
    {
        restore_error_handler();
    }

    /**
     * Get an error handler
     *
     * @return ErrorHandlerInterface
     */
    protected static function getErrorHandler(): ErrorHandlerInterface
    {
        if (! self::$errorHandler && ! self::$errorHandler instanceof ErrorHandlerInterface) {
            self::$errorHandler = new self();
        }

        return self::$errorHandler;
    }
}
