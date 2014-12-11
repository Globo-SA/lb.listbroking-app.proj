<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\TaskControllerBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseCommand extends ContainerAwareCommand {

    const MAX_RUNNING = 1;

    private $input;
    private $output;
    /**
     * @var ProgressBar
     */
    private $progress;

    protected function execute(InputInterface $input, OutputInterface $output){
        $this->input = $input;
        $this->output = $output;
    }

    public function isRunning(){


    }

    public function write($comment){
        $this->output->writeln("<info>{$this->getName()}</info> <comment>{$comment}</comment>\n");
    }


    public function createProgress($msg, $max){
        $this->write($msg);
        $this->progress = new ProgressBar($this->output, $max);
        $this->progress->setBarCharacter('<info>=</info>');
        $this->progress->setFormat("%current%/%max% [<comment>%bar%</comment>] %percent%%\n<fg=white;bg=blue> %message% </>");
    }

    public function advanceProgress($msg){
        $this->progress->setMessage($msg);
        $this->progress->advance();
    }

    public function finishProgress(){
        $this->progress->setMessage("DONE");
        $this->progress->finish();
    }
} 