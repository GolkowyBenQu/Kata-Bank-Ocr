<?php

namespace App\Command;

use App\Service\OcrScannerService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScanSegmentAccountsCommand extends Command
{
    protected static $defaultName = 'app:scan-segment-accounts';

    private const SEPARATOR = '=================================================================';

    protected function configure(): void
    {
        $this
            ->addArgument('filepath', InputArgument::REQUIRED, 'Filepath.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filepath = $input->getArgument('filepath');

        $ocrScannerService = new OcrScannerService();

        try {
            $accounts = $ocrScannerService->scanFile($filepath);
        } catch (\Exception $exception) {
            $output->writeln('Error: '.$exception->getMessage());
            return Command::FAILURE;
        }

        $output->writeln(self::SEPARATOR);
        foreach ($accounts as $account) {
            $output->writeln($account->getSegmentAccountNumber());
            $output->writeln($account);

            $output->writeln('');
            $output->writeln(self::SEPARATOR);
        }

        return Command::SUCCESS;
    }
}
