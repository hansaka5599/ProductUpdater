<?php
namespace Ecommistry\ProductUpdater\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ProductMessagesCommand
 * @package Ecommistry\ProductUpdater\Console\Command
 */
class ProductMessagesCommand extends Command
{
    /**
     * App state
     *
     * @var \Magento\Framework\App\State
     */
    protected $appState;


    /**
     * Cron process
     *
     * @var \Ecommistry\ProductUpdater\Cron\ProductUpdater
     */
    protected $cronProcess;


    public function __construct(
        \Ecommistry\ProductUpdater\Cron\MessagesUpdater $cron
    ) {
        $this->cronProcess = $cron;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('animates:product-messages')
            ->setDescription('Animates product-messages');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->cronProcess->execute();
    }
}