<?php

    namespace Fm\CronjobManagerBundle\Service;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Fm\CronjobManagerBundle\Entity\FmCronjobManager;
    use Exception;
    use Doctrine\ORM\EntityManagerInterface;
    use Fm\CronjobManagerBundle\Repository\FmCronjobManagerLogsRepository;
    use Fm\CronjobManagerBundle\Entity\FmCronjobManagerLogs;
    use Symfony\Component\HttpFoundation\Response;
    use DateTime;
    use Symfony\Component\Filesystem\Filesystem;
    use Symfony\Component\Filesystem\Path;
    use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

    abstract class CronjobExecutionService extends AbstractController
    {

        public $cronjobEntity;
        public $jobName;
        public $jobId;
        public $full_log_file_path;
        public $filesystem;
        public $jobDuration;

        public function __construct($cronjob_name) {
            $this->jobName = $cronjob_name;
            $this->checkIfJobExists();
        }

        public function __destruct()
        {
            $this->jobDuration = $this->jobDuration->diff(new DateTime());
            $this->newCronLog("End");
            $this->newCronLog("Total " . $this->jobDuration->m . "." . $this->jobDuration->s . " Minutes");
        }

        public function checkIfJobExists (): bool
        {
            $this->cronjobEntity = $this->entityManager
                ->getRepository(FmCronjobManager::class)
                ->findOneBy(["name" => $this->jobName]);

            if (!$this->cronjobEntity) {
                throw new Exception("No Cronjob entity has been found with name $this->jobName");
            }

            $this->jobId = $this->cronjobEntity->getId();

            $this->filesystem = new Filesystem();
            $this->createFile($this->jobId);
            $this->newCronLog("Start");
            $this->jobDuration = new DateTime();
            return true;
        }

        public function newCronlog(string $log): void
        {
            $this->filesystem->appendToFile($this->full_log_file_path, (new DateTime())->format('d.m.Y H:i:s') . ": " . $log . "\r\n");
        }

        public function createFile(int $jobId): bool
        {

            $directory = $this->params->get('cronjob_log_directory') . '/' . $this->jobId . "/";
            $file_name = (new DateTime())->format("YmdHis") . ".txt";
            $this->full_log_file_path = $directory.$file_name;

            try {
                $this->filesystem->mkdir(
                    Path::normalize($directory),
                );
                $this->filesystem->touch($this->full_log_file_path);
            } catch (IOExceptionInterface $exception) {
                echo "An error occurred while creating your directory at ".$exception->getPath();
            }
            return true;
        }

    }