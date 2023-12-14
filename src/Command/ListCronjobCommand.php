<?php

    namespace Fm\CronjobManagerBundle\Command;

    use Symfony\Component\Console\Command\Command;
    use Fm\CronjobManagerBundle\Service\FmCronjobManagerService;
    use Fm\CronjobManagerBundle\Repository\FmCronjobManagerRepository;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Style\SymfonyStyle;
    use Symfony\Component\Validator\Validation;
    use Symfony\Component\Validator\Constraints\NotBlank;
    use Symfony\Component\Validator\Constraints\NotNull;
    use Symfony\Component\Validator\Constraints\Regex;
    use Throwable;
    use RuntimeException;

    #[AsCommand(
        name: "cronjob:list",
        description: "List Cronjob",
        hidden: false,
        aliases: ['cronjob:list']
    )]
    class ListCronjobCommand extends Command
    {
        public function __construct(
            private FmCronjobManagerService       $cronjobManagerService,
            private FmCronjobManagerRepository $cronjobManagerRepository
        )
        {
            parent::__construct();
        }

        protected function execute(InputInterface $input, OutputInterface $output): int
        {
            try {
                $output->writeln([
                    '',
                    '<info>Welcome to Fm Cronjob Manager</info>',
                    '',
                ]);

                $cronjobRepo = $this->cronjobManagerRepository->findToArray();

                foreach ($cronjobRepo as $cronjob) {
                    $colorStyle = $cronjob["active"] ? "comment" : "error" ;
                    $output->writeln("<$colorStyle>$cronjob[name]</$colorStyle>");

                    foreach ($cronjob['url'] as $url) {
                        $output->writeln("    <comment>$url</comment>");
                    }
                    $output->writeln("");
                }

                return Command::SUCCESS;

            } catch (RuntimeException $e) {
                $ed = $e->getMessage();
                $output->writeln([
                    "",
                    "<error>$ed</error>",
                    "",
                ]);
                return Command::FAILURE;
            }

        }

    }