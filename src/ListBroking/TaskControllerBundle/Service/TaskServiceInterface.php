<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\TaskControllerBundle\Service;


use ListBroking\TaskControllerBundle\Entity\Queue;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface TaskServiceInterface {

    /**
     * Finds queues by type
     * @param $type
     * @return mixed
     */
    public function findQueuesByType($type);

    /**
     * Created a new entry on the queue
     * @param $type
     * @param null $value1
     * @param null $value2
     * @param null $value3
     * @param null $value4
     * @throws \Exception
     * @internal param Form $form
     * @return Queue
     */
    public function addToQueue($type, $value1 = null, $value2 = null, $value3 = null, $value4 = null);

    /**
     * Stats a new task if possible
     * @param Command $command
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $max_running
     * @return mixed
     */
    public function start(Command $command, InputInterface $input, OutputInterface $output, $max_running);

    /**
     * Check if the task is already at the limit
     * of iterations running
     * @return mixed
     */
    public function isRunning();

    /**
     * Throws an error on the current task
     * @param \Exception $exception
     * @return mixed
     */
    public function throwError(\Exception $exception);

    /**
     * Ends the task
     * @return mixed
     */
    public function finish();

    /**
     * Writes a new line to the output
     * @param $comment
     * @return mixed
     */
    public function write($comment);

    /**
     * Creates a ProgressBar
     * @param $msg
     * @param $max
     * @return mixed
     */
    public function createProgressBar($msg, $max);

    /**
     * Advances the ProgressBar
     * @param $msg
     * @return mixed
     */
    public function advanceProgressBar($msg);

    /**
     * Changes the ProgressBar message
     * @param $msg
     */
    public function setProgressBarMessage($msg);

    /**
     * Finishes the ProgressBar
     * @return mixed
     */
    public function finishProgressBar();
} 