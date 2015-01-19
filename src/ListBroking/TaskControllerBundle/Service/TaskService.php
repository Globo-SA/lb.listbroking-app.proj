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


use Doctrine\ORM\EntityManager;
use ListBroking\TaskControllerBundle\Entity\Queue;
use ListBroking\TaskControllerBundle\Entity\Task;
use ListBroking\TaskControllerBundle\Exception\TaskControllerException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TaskService implements TaskServiceInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Command
     */
    private $command;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var integer
     */
    private $max_running;
    /**
     * @var Task
     */
    private $task;

    /**
     * @var ProgressBar
     */
    private $progress;

    function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Finds queues by type
     * @param $type
     * @return mixed
     */
    public function findQueuesByType($type){

        return $this->em->getRepository('ListBrokingTaskControllerBundle:Queue')->findBy(array(
            'type' => $type
        ));
    }

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
    public function addToQueue($type, $value1 = null, $value2 = null, $value3 = null, $value4 = null){

        if(empty($type)){
            throw new \Exception('Invalid or empty type');
        }

        $queue = new Queue();
        $queue->setType($value1);
        $queue->setValue1($value2);
        $queue->setValue2($value3);
        $queue->setValue3($value4);

        $this->em->persist($queue);
        $this->em->flush();

        return $queue;
    }

    /**
     * Stats a new task if possible
     * @param Command $command
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $max_running
     * @return mixed
     */
    public function start(Command $command, InputInterface $input, OutputInterface $output, $max_running)
    {
        $this->command = $command;
        $this->input = $input;
        $this->output = $output;
        $this->max_running = $max_running;

        if (!$this->isRunning($this->command))
        {
            $task_control = new Task();
            $task_control->setName($this->command->getName());
            $task_control->setStatus(Task::STATUS_RUNNING);
            $task_control->setPid(getmypid());

            $this->em->persist($task_control);
            $this->em->flush();

            $this->task = $task_control;

            $this->write('STARTING');
            return true;
        }

        return false;
    }

    /**
     * Check if the task is already at the limit
     * of iterations running
     * @return mixed
     */
    public function isRunning()
    {
        $this->isStarted();
        $running = $this->em->getRepository('ListBrokingTaskControllerBundle:Task')->findBy(array(
            "name" => $this->command->getName(),
            "status" => Task::STATUS_RUNNING
        ));

        return count($running) >= $this->max_running;
    }

    /**
     * Throws an error on the current task
     * @param \Exception $exception
     * @return mixed
     */
    public function throwError(\Exception $exception)
    {
        $this->isStarted();
        $this->task->setStatus(Task::STATUS_ERROR);
        $this->task->setMsg($exception->getMessage());
        $this->em->flush();

        $this->write('ENDING WITH ERROR - ' . $exception->getMessage());
    }

    /**
     * Ends the task
     * @return mixed
     */
    public function finish()
    {
        $this->isStarted();
        $this->task->setStatus(Task::STATUS_SUCCESS);
        $this->em->flush();

        $this->write('END');
    }

    /**
     * Writes a new line to the output
     * @param $comment
     * @return mixed
     */
    public function write($comment){
        $this->output->writeln('[' . date('Y-m-d h:i:s') . "] <info>{$this->command->getName()}</info> <comment>{$comment}</comment>\n");
    }

    /**
     * Creates a ProgressBar
     * @param $msg
     * @param $max
     * @return mixed
     */
    public function createProgressBar($msg, $max){
        $this->write($msg);
        $this->progress = new ProgressBar($this->output, $max);
        $this->progress->setBarCharacter('<info>=</info>');
        $this->progress->setFormat("%current%/%max% [<comment>%bar%</comment>] %percent%%\n<fg=white;bg=blue> %message% </>");
    }

    /**
     * Advances the ProgressBar
     * @param $msg
     * @return mixed
     */
    public function advanceProgressBar($msg){
        $this->progress->setMessage($msg);
        $this->progress->advance();
    }

    /**
     * Changes the ProgressBar message
     * @param $msg
     */
    public function setProgressBarMessage($msg){
        $this->progress->setMessage($msg);
    }

    /**
     * Finishes the ProgressBar
     * @return mixed
     */
    public function finishProgressBar(){
        $this->progress->setMessage("FINISHED");
        $this->progress->clear();
        $this->progress->finish();
        $this->output->writeln("");
    }

    /**
     * Checks if the task has been started
     * @throws TaskControllerException
     */
    private function isStarted()
    {
        if (!$this->command || !$this->input || !$this->output)
        {
            throw new TaskControllerException('Please first start the service using start()');
        }
    }

}