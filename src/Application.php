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
     * @param $code
     */
    public function eval($code)
    {
        (function (Shell $psysh) use ($code) {
            $handle = function (\Exception $exception) use ($psysh) {
                restore_error_handler();
                if (ob_get_level() > 0) {
                    ob_end_clean();
                }
                $psysh->writeException($exception);
            };

            try {
                $psysh->addCode($code);

                ob_start(function (string $out) {
                    if ($out !== '' && substr($out, -1) !== "\n") {
                        return $out . "⏎\n";
                    }
                    return $out;
                });

                set_error_handler(array($psysh, 'handleError'));
                $return = eval($psysh->flushCode() ?: Loop::NOOP_INPUT);
                restore_error_handler();

                ob_end_flush();

                $psysh->writeReturnValue($return);
            } catch (\TypeError $e) {
                $handle(TypeErrorException::fromTypeError($e));
            } catch (\Error $e) {
                $handle(ErrorException::fromError($e));
            } catch (\Exception $e) {
                $handle($e);
            }
        })->bindTo(null, null)($this->psysh);
    }
}
