<?php

    namespace Fm\CronjobManagerBundle\Tests;

    use PHPUnit\Framework\TestCase;
    use Symfony\Component\Console\Application;
    use Symfony\Component\Console\Tester\CommandTester;
    use Fm\CronjobManagerBundle\Service\FmCronjobManagerService;
    use Fm\CronjobManagerBundle\Repository\FmCronjobManagerRepository;
    use Fm\CronjobManagerBundle\Command\CreateCronjobCommand;

    class CreateCronjobCommandTest extends TestCase
    {
        public function testExecute()
        {
            // Create a mock for the dependencies
            $cronjobManagerService = $this->createMock(FmCronjobManagerService::class);
            $cronjobManagerRepository = $this->createMock(FmCronjobManagerRepository::class);

            $command = new CreateCronjobCommand($cronjobManagerService, $cronjobManagerRepository);

            // Create a new application, add our command and get it
            $application = new Application();
            $application->add($command);
            $command = $application->find('cronjob:new');
            $commandTester = new CommandTester($command);

            // Here we mock the interactions with the dependencies, you might have to adapt it to your needs
            $cronjobManagerRepository->method('findOneBy')->willReturn(null);
            $cronjobManagerService->method('createNewJob')->willReturn(true);

            $commandTester->setInputs(
                [
                    'New Job',
                    'http://localhost',
                    '',
                    '* * * * *',
                    '200',
                    'true'
                ]
            );

            // Execute the command with the parameters as user input
            $commandTester->execute(['command'  => $command->getName()]);

            // Validate the output
            $output = $commandTester->getDisplay();

            $this->assertStringContainsString('Job succesfully created!', $output);
        }
    }