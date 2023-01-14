<?php declare(strict_types=1);

namespace App\Command;

use DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use App\Repository\ResetPasswordRequestRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:delete-expired-tokens',
    description: 'Deletes all reset password codes which are expired.',
    hidden: false
)]
final class DeleteExpiredTokens extends Command
{
    public function __construct(
        private readonly ResetPasswordRequestRepository $resetPasswordRequestRepository
    )
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument('table', InputArgument::REQUIRED, 'Name of the table to be cleaned. Available table: resetPassword, auth, access, refresh, user')
        ;
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    )
    {
        $availableTable = [
            'user',
            'auth',
            'access',
            'refresh',
            'resetPassword'
        ];

        $pastedTable = $input->getArgument('table');

        if (!in_array($pastedTable, $availableTable)) {
            $output->writeln('Entity ' . $pastedTable .' does not exists');
            $output->writeln('Use the command | app:reset-expired-tokens --help | to find the available entities.');
            return Command::FAILURE;
        }

        $progressBarOutput = $output->section();
        $messageOutput = $output->section();

        $progressBar = new ProgressBar($progressBarOutput, 2);

        $this->createMessage($messageOutput, 'Building environment...');

        $repository = $this->buildRepository($pastedTable);
        $progressBar->advance();

        $this->createMessage($messageOutput, 'Searching and deleting expired tokens...');

        $expiredTokens = $this->searchingAndDeletingExpiredTokens($repository);
        $progressBar->advance();

        if ($expiredTokens === 0) {
            $progressBar->finish();
            $output->writeln('No expired tokens found.');
            return Command::SUCCESS;
        }

        $this->createMessage($messageOutput, 'Expired tokens found: ' . $expiredTokens);
        $progressBar->finish();

        $output->writeln('Successfully deleted all expired tokens');
        return Command::SUCCESS;
    }

    private function createMessage(
        OutputInterface $output,
        string $message
    )
    {
        $output->writeln('[ ' . (new DateTime())->format('d-m-Y H:i:s') . ' ] ' . $message);
    }

    private function buildRepository(
        string $pastedTable
    ): object
    {
        if ($pastedTable === 'resetPassword') {
            return $this->resetPasswordRequestRepository;
        }

        $repositoryName = $pastedTable . 'TokenRepository';

        return $this->$repositoryName;
    }

    private function searchingAndDeletingExpiredTokens(
        object $repository
    ): int
    {
        $data = $repository->findAll();
        $expiredToken = 0;

        foreach($data as $token) {
            if ($token->getExpireDate() < new DateTime()) {
                $expiredToken += 1;
                $repository->deleteEntry($token);
            }
        }

        return $expiredToken;
    }


}
