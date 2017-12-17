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
        $this->output = $output;
        $this->psysh = $psysh;
    }

    /**
     * @param $code
     */
    public function eval($code)
    {
        (function (Shell $psysh, OutputInterface $output) use ($code) {
            $handle = function (\Exception $exception) use ($psysh, $output) {
                restore_error_handler();
                if (ob_get_level() > 0) {
                    ob_end_clean();
                }
                $output->writeln($exception->getMessage());
            };

            try {
                $psysh->addCode($code);

                ob_start(function (string $out) {
                    if ($out !== '' && substr($out, -1) !== "\n") {
                        return $out . "⏎\n";
                    }
                    return $out;
                });

                set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline) {
                    ErrorException::throwException($errno, $errstr, '', $errline);
                });
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
        })->bindTo(null, null)($this->psysh, $this->output);
    }
}
