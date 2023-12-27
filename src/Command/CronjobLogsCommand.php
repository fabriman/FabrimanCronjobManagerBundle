<?php

    namespace Fm\CronjobManagerBundle\Command;

    use Symfony\Component\Console\Command\Command;
    use Fm\CronjobManagerBundle\Service\FmCronjobManagerService;
    use Fm\CronjobManagerBundle\Repository\FmCronjobManagerRepository;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Style\SymfonyStyle;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    use Symfony\Component\Finder\Finder;
    use Symfony\Component\Validator\Validation;
    use Symfony\Component\Validator\Constraints\NotBlank;
    use Symfony\Component\Validator\Constraints\NotNull;
    use Symfony\Component\Validator\Constraints\Regex;
    use Throwable;
    use RuntimeException;

    #[AsCommand(
        name: "cronjob:log",
        description: "Cronjob Logs",
        hidden: false,
        aliases: ['cronjob:log']
    )]
    class CronjobLogsCommand extends Command
    {
        public function __construct(
            private FmCronjobManagerService       $cronjobManagerService,
            private FmCronjobManagerRepository $cronjobManagerRepository,
            private ParameterBagInterface $params
        )
        {
            parent::__construct();
        }

        protected function execute(InputInterface $input, OutputInterface $output): int
        {
            try {

                $finder = new Finder();

                // outputs multiple lines to the console (adding "\n" at the end of each line)
                $output->writeln([
                    '',
                    '<info>Welcome to Fm Cronjob Manager</info>',
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

                $id = $io->ask("For wich job you want to check the log ?", NULL,
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

                if($id){

                    if(!is_dir($this->params->get('cronjob_log_directory') . '/' . $id)){
                        throw new \Exception("Select a valid id!");
                    }

                    $directory = $this->params->get('cronjob_log_directory') . '/' . $id . "/";
                    $finder->files()->in($directory);
                    $finder->sortByModifiedTime();
                    $finder->reverseSorting();

                    $n = 0;
                    foreach ($finder as $file) {
                        $n++;
                        $absoluteFilePath = $file->getRealPath();
                        $fileNameWithExtension = $file->getRelativePathname();
                        $logs[$n] = $absoluteFilePath;
                        $output->writeln([
                            "<comment>[$n] $fileNameWithExtension</comment>",
                        ]);
                    }
                    $output->writeln([
                        "",
                    ]);

                    $log = $io->ask("which Log you want to check ?", NULL,
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



                    if(!array_key_exists($log, $logs) || !file_exists($logs[$log])){
                        throw new \Exception("Select a valid log!");
                    }

                    $output->writeln(file_get_contents($logs[$log]));
                }


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