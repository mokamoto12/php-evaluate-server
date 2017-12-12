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
     * @var OutputInterface
     */
    private $output;

    /**
     * Application constructor.
     *
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param $request
     */
    public function run($request)
    {
        if (!isset($request['eval'])) {
            return;
        }
        @$psysh = new Shell();
        $psysh->setOutput($this->output);
        $psysh->addCode($request['eval']);
        try {
            // evaluate the current code buffer
            ob_start(
                array($psysh, 'writeStdout'),
                version_compare(PHP_VERSION, '5.4', '>=') ? 1 : 2
            );

            set_error_handler(array($psysh, 'handleError'));
            $return = eval($psysh->flushCode() ?: Loop::NOOP_INPUT);
            restore_error_handler();

            ob_end_flush();

            $psysh->writeReturnValue($return);
        } catch (\TypeError $_e) {
            restore_error_handler();
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            $psysh->writeException(TypeErrorException::fromTypeError($_e));
        } catch (\Error $_e) {
            restore_error_handler();
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            $psysh->writeException(ErrorException::fromError($_e));
        } catch (\Exception $_e) {
            restore_error_handler();
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            $psysh->writeException($_e);
        }
    }
}
