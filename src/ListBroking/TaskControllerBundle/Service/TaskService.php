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

    public function isRunning()
    {
        $this->isStarted();
        $running = $this->em->getRepository('ListBrokingTaskControllerBundle:Task')->findBy(array(
            "name" => $this->command->getName(),
            "status" => Task::STATUS_RUNNING
        ));

        return count($running) >= $this->max_running;
    }

    public function throwError(\Exception $exception)
    {
        $this->write('ENDING WITH ERROR - ' . $exception->getMessage());

        $this->isStarted();
        $this->task->setStatus(Task::STATUS_ERROR);
        $this->task->setMsg($exception->getMessage());
        $this->em->flush();

    }

    public function finish()
    {
        $this->isStarted();
        $this->task->setStatus(Task::STATUS_SUCCESS);
        $this->em->flush();

        $this->write('END');
    }

    public function write($comment){
        $this->output->writeln('[' . date('Y-m-d h:i:s') . "] <info>{$this->command->getName()}</info> <comment>{$comment}</comment>\n");
    }

    public function createProgressBar($msg, $max){
        $this->write($msg);
        $this->progress = new ProgressBar($this->output, $max);
        $this->progress->setBarCharacter('<info>=</info>');
        $this->progress->setFormat("%current%/%max% [<comment>%bar%</comment>] %percent%%\n<fg=white;bg=blue> %message% </>");
    }

    public function advanceProgressBar($msg){
        $this->progress->setMessage($msg);
        $this->progress->advance();
    }

    public function setProgressBarMessage($msg){
        $this->progress->setMessage($msg);
    }

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