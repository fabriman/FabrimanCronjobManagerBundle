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
        name: "cronjob:delete",
        description: "Delete Cronjob",
        hidden: false,
        aliases: ['cronjob:delete']
    )]
    class DeleteCronjobCommand extends Command
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
                    '<info>Please, select the Job to delete</info>',
                    '',
                ]);

                $cronjobRepo = $this->cronjobManagerRepository->findToArray();

                foreach ($cronjobRepo as $cronjob) {

                    $output->writeln("<comment>[$cronjob[id]] $cronjob[name]</comment>");

                    foreach ($cronjob['url'] as $url) {
                        $output->writeln("    <comment>$url</comment>");
                    }
                    $output->writeln("");
                }

                $io = new SymfonyStyle($input, $output);

                $id = $io->ask("Which Job ID you want to delete?", NULL,
                    Validation::createCallable(
                        new NotBlank(),
                        new NotNull(),
                        new Regex([
                                'pattern' => '/^[0-9]+$/',
                                'message' => 'Please, use only integer numbers!',
                            ]
                        )
                    ),
                );

                $entity = $this->cronjobManagerRepository->findOneBy(["id" => $id]);
                $expression = $this->cronjobManagerService::createExpression($entity);
                $this->cronjobManagerService::removeJob($expression);
                $this->cronjobManagerRepository->remove($entity, true);

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