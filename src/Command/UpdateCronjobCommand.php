<?php

    namespace Fm\CronjobManagerBundle\Command;

    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Question\Question;
    use Symfony\Component\Console\Style\SymfonyStyle;
    use Symfony\Component\Validator\Validation;
    use Symfony\Component\Validator\Constraints\Regex;
    use Symfony\Component\Validator\Constraints\NotBlank;
    use Symfony\Component\Validator\Constraints\NotNull;
    use Symfony\Component\Validator\Constraints as Assert;
    use Fm\CronjobManagerBundle\Service\FmCronjobManagerService;
    use function Symfony\Component\Translation\t;
    use Fm\CronjobManagerBundle\Repository\FmCronjobManagerRepository;
    use Fm\CronjobManagerBundle\Entity\FmCronjobManager;
    use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
    use RuntimeException;
    use Exception;
    use Throwable;

    #[AsCommand(
        name: "cronjob:update",
        description: "Update Cronjob",
        hidden: false,
        aliases: ['cronjob:update']
    )]
    class UpdateCronjobCommand extends Command
    {

        private ?FmCronjobManager $entity = null;

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
                // outputs multiple lines to the console (adding "\n" at the end of each line)
                $output->writeln([
                    '',
                    '<info>Welcome to Fm Cronjob Manager</info>',
                    '<info>Please, select the Job to delete</info>',
                    '',
                ]);

                $io = new SymfonyStyle($input, $output);

                $cronjobRepo = $this->cronjobManagerRepository->findToArray();

                foreach ($cronjobRepo as $cronjob) {

                    $output->writeln("<comment>[$cronjob[id]] $cronjob[name]</comment>");

                    foreach ($cronjob['url'] as $url) {
                        $output->writeln("    <comment>$url</comment>");
                    }
                    $output->writeln("");
                }

                $id = $io->ask("Which Job ID you want to update?", NULL,
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

                $this->entity = $this->cronjobManagerRepository->findOneBy(["id" => $id]);

                if (!$this->entity) {
                   throw new Exception("No Job found with this id!");
                }

                $title = $io->ask("Cronjob Name", $this->entity->getName(),
                    Validation::createCallable(
                        new NotBlank(),
                        new NotNull(),
                        new Regex([
                                'pattern' => '/^[A-Za-z0-9 ]+$/',
                                'message' => 'Invalid name {{ value }}. Please, use only letters, numbers and spaces!',
                            ]
                        ),
                        new Assert\Callback(function (mixed $value): void {
                            if ($this->entity->getName() != $value && $this->cronjobManagerRepository->findOneBy(["name" => $value])) {
                                throw new \RuntimeException("Name '$value' already exists");
                            }
                        })
                    ),
                );

                $urlArray = [];
                foreach ($this->entity->getUrl() as $url) {
                    $url = $io->ask("Write the full destination path (one per line. Leave empty to stop)", $url,
                        Validation::createCallable(
                            new Regex([
                                    'pattern' => '/^[A-Za-z0-9.:&$_=\-\/]+$/',
                                    'message' => 'Invalid Url!',
                                ]
                            )
                        )
                    );
                    if ($url != "") {
                        $urlArray[] = $url;
                    }
                }

                do {
                    $url = $io->ask("Write the full destination path (one per line. Leave empty to stop)", NULL,
                        Validation::createCallable(
                            new Regex([
                                    'pattern' => '/^[A-Za-z0-9.:&$_=\-\/]+$/',
                                    'message' => 'Invalid Url!',
                                ]
                            )
                        )
                    );
                    if ($url != "") {
                        $urlArray[] = $url;
                    }
                } while ($url != NULL || count($urlArray) == 0);

                $expression = $io->ask("Cronjob Expession", $this->entity->getJobExpression(),
                    Validation::createCallable(
                        new Regex([
                                'pattern' => '/^((((\d+,)+\d+|(\d+(\/|-)\d+)|\d+|\*\/|\*|) ?){5,7})$/',
                                'message' => 'Invalid Format for this Job expression!',
                            ]
                        ),
                        new NotBlank(),
                        new NotNull()
                    )
                );

                $timeout = $io->ask("Timeout", "".$this->entity->getJobTimeout()."",
                    Validation::createCallable(
                        new Regex([
                                'pattern' => '/^[0-9]+$/',
                                'message' => 'Only integer numbers are accepted!',
                            ]
                        ),
                    )
                );

                $isActive = $io->ask("Activate Cronjob ?", $this->entity->getActive() ? "true" : "false",
                    function (mixed $value): bool {
                        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                        if (!is_bool($value)) {
                            throw new \RuntimeException('Boolean expected!');
                        }
                        return (bool)$value;
                    }
                );

                $response = $this->cronjobManagerService->updateJob($this->entity, $title, $urlArray, $expression, $timeout, $isActive);

                $output->writeln("Job succesfully updated!");

                return Command::SUCCESS;

            } catch (Throwable $e) {
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