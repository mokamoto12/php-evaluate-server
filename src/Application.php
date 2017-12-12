<?php

namespace Mokamoto12\Evaluate;

use Psy\Shell;
use Psy\ExecutionLoop\Loop;
use Psy\Exception\TypeErrorException;
use Psy\Exception\ErrorException;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Application
 */
class Application
{
    /**
     * @var Shell
     */
    private $psysh;

    /**
     * Application constructor.
     *
     * @param Shell $psysh
     * @param OutputInterface $output
     */
    public function __construct(Shell $psysh, OutputInterface $output)
    {
        $psysh->setOutput($output);
        $this->psysh = $psysh;
    }

    /**
     * @param $request
     */
    public function run($request)
    {
        if (!isset($request['eval'])) {
            return;
        }
        $this->psysh->addCode($request['eval']);
        try {
            // evaluate the current code buffer
            ob_start(
                array($this->psysh, 'writeStdout'),
                version_compare(PHP_VERSION, '5.4', '>=') ? 1 : 2
            );

            set_error_handler(array($this->psysh, 'handleError'));
            $return = eval($this->psysh->flushCode() ?: Loop::NOOP_INPUT);
            restore_error_handler();

            ob_end_flush();

            $this->psysh->writeReturnValue($return);
        } catch (\TypeError $e) {
            $this->terminateWith(TypeErrorException::fromTypeError($e));
        } catch (\Error $e) {
            $this->terminateWith(ErrorException::fromError($e));
        } catch (\Exception $e) {
            $this->terminateWith($e);
        }
    }

    /**
     * @param \Exception $exception
     */
    private function terminateWith(\Exception $exception)
    {
        restore_error_handler();
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        $this->psysh->writeException($exception);
    }
}
