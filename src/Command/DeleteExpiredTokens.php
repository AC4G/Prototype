<?php declare(strict_types=1);

namespace App\Command;

use DateTime;
use App\Repository\UserTokenRepository;
use App\Repository\AuthTokenRepository;
use App\Repository\AccessTokenRepository;
use App\Repository\RefreshTokenRepository;
use Symfony\Component\Console\Command\Command;
use App\Repository\ResetPasswordTokenRepository;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:delete-expired-tokens',
    description: 'Deletes all tokens from table which are expired.',
    hidden: false
)]
final class DeleteExpiredTokens extends Command
{
    public function __construct(
        private readonly ResetPasswordTokenRepository $resetPasswordTokenRepository,
        private readonly RefreshTokenRepository $refreshTokenRepository,
        private readonly AccessTokenRepository $accessTokenRepository,
        private readonly AuthTokenRepository $authTokenRepository,
        private readonly UserTokenRepository $userTokenRepository
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
            $output->writeln('Table ' . $pastedTable .' does not exists!');
            $output->writeln('Use the command | app:reset-expired-tokens --help | to find out the available tables.');
            return Command::FAILURE;
        }

        $messageOutput = $output->section();
        $progressBarOutput = $output->section();

        $progressBar = new ProgressBar($progressBarOutput, 2);

        $this->createMessage($messageOutput, 'Building environment');

        $repository = $this->buildRepository($pastedTable);
        $progressBar->advance();

        $this->createMessage($messageOutput, 'Searching and deleting expired tokens');

        $expiredTokens = $this->searchingAndDeletingExpiredTokens($repository);
        $progressBar->advance();

        if ($expiredTokens === 0) {
            $progressBar->finish();
            $output->writeln('No expired tokens found.');
            return Command::SUCCESS;
        }

        $this->createMessage($messageOutput, 'Done');
        $progressBar->finish();

        $output->writeln('Expired tokens found: ' . $expiredTokens);
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
                $repository->deleteEntry($token);
                $expiredToken += 1;
            }
        }

        return $expiredToken;
    }


}
