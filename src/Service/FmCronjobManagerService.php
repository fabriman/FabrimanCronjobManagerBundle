<?php

namespace Fm\CronjobManagerBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Fm\CronjobManagerBundle\Repository\FmCronjobManagerRepository;
use Fm\CronjobManagerBundle\Entity\FmCronjobManager;

class FmCronjobManagerService extends AbstractController
{
    /**
     * In this class, array instead of string would be the standard input / output format.
     * Legacy way to add a job: $output = shell_exec('(crontab -l; echo "'.$job.'") | crontab -');
     * Standard job format: 1 1 * * 0  /usr/bin/curl -m 120 --silent http://example.come/some.php &>/dev/null
     */

    public function __construct(private FmCronjobManagerRepository $fmCronjobManagerRepository) {}

    /**
     * @param string|null $jobs
     * @return string[]
     */
    private static function stringToArray(string $jobs = null): array
    {
        $array = explode("\r\n", trim($jobs)); // trim() gets rid of the last \r\n
        foreach ($array as $key => $item) {
            if ($item == '') {
                unset($array[$key]);
            }
        }
        return $array;
    }

    /**
     * @param array $jobs
     * @return string
     */
    private static function arrayToString(array $jobs = array()): string
    {
        return implode("\r\n", $jobs);
    }

    /**
     * @return false|string[]
     */
    public static function getJobs(): array|bool
    {
        $output = shell_exec('crontab -u www-data -l');
        return self::stringToArray($output);
    }

    /**
     * @param array $jobs
     * @return false|string|null
     */
    public static function saveJobs(array $jobs = array()): bool|string|null
    {
        return shell_exec('echo "' . self::arrayToString($jobs) . '" |  crontab -u www-data -');
    }

    /**
     * @param string $job
     * @return bool
     */
    public static function doesJobExist(string $job = ''): bool
    {
        $jobs = self::getJobs();
        if (!in_array($job, $jobs) && $job != "") {
            return false;
        }
        return true;
    }

    /**
     * @param string $job
     * @return false|string|null
     */
    public static function addJob(string $job = ''): bool|string|null
    {
        $routes = explode(";", $job);
        $jobs = self::getJobs();
        foreach ($routes as $route) {
            if (!self::doesJobExist($route) && $route != "") {
                $jobs[] = $route;
            }
        }
        return self::saveJobs($jobs);
    }

    /**
     * @param string $job
     * @return false|string|null
     */
    public static function removeJob(string $job = ''): bool|string|null
    {
        $routes = explode(";", $job);
        $jobs = self::getJobs();
        foreach ($routes as $route) {
            if (self::doesJobExist(trim($route)) && $route != "") {
                unset($jobs[array_search($route, $jobs)]);
            }
        }
        return self::saveJobs($jobs);
    }

    /**
     * @param $entity
     * @return string
     */
    public static function createExpression($entity): string
    {
        $output = "";
//        $routes = explode("\r\n", $entity->getUrl());
        $routes = $entity->getUrl();
        foreach ($routes as $route) {
            if ($route != "") {
                $output .= implode(" ", [
                    $entity->getJobExpression(),
                    "/usr/bin/curl",
                    "--insecure",
                    "-m " . $entity->getJobTimeout(),
                    "--silent",
                    $route,
                    "&>/dev/null;"
                ]);
            }
        }
        return $output;
    }

    public function createNewJob(
        string $title,
        array $url,
        string $expression,
        int $timeout,
        bool $isActive
    ): bool{

        $cronjobEntity = new FmCronjobManager();
        $cronjobEntity->setName($title);
        $cronjobEntity->setUrl($url);
        $cronjobEntity->setJobExpression($expression);
        $cronjobEntity->setJobTimeout($timeout);
        $cronjobEntity->setActive($isActive);
        $new_command_expression = self::createExpression($cronjobEntity);
        $cronjobEntity->setFullExpression($new_command_expression);
        $routes = array_filter(explode(";", $new_command_expression));

        foreach ($routes as $route) {
            //                dd($route);
            if ($results = $this->fmCronjobManagerRepository->createQueryBuilder("r")
                ->where("r.fullExpression LIKE '%$route%'")
                ->getQuery()
                ->getArrayResult()) {
                throw new \RuntimeException("This job already exists");
            }
        }

        if ($cronjobEntity->getActive()) {
            self::addJob($new_command_expression);
        }

        foreach ($routes as $route) {
            if (!self::doesJobExist($route) && $cronjobEntity->getActive() == 1) {
                $cronjobEntity->setActive(false);
                throw new \RuntimeException('Something went wrong, please check your cronjob');
            }
        }

        $this->fmCronjobManagerRepository->save($cronjobEntity, true);
        return true;
    }

    public function updateJob(
        FmCronjobManager $cronjobEntity,
        string           $title,
        array            $url,
        string           $expression,
        int              $timeout,
        bool             $isActive
    ): bool{

        $old_command_expression = self::createExpression($cronjobEntity);

        $cronjobEntity->setName($title);
        $cronjobEntity->setUrl($url);
        $cronjobEntity->setJobExpression($expression);
        $cronjobEntity->setJobTimeout($timeout);
        $cronjobEntity->setActive($isActive);
        $new_command_expression = self::createExpression($cronjobEntity);
        $cronjobEntity->setFullExpression($new_command_expression);
        $routes = array_filter(explode(";", $new_command_expression));

        self::removeJob($old_command_expression);

        foreach ($routes as $route) {
            if ($results = $this->fmCronjobManagerRepository->createQueryBuilder("r")
                ->where("r.fullExpression LIKE '%$route%'")
                ->andWhere("r.id != ". $cronjobEntity->getId())
                ->getQuery()
                ->getArrayResult()) {
                throw new \RuntimeException("This job already exists");
            }
        }

        if ($cronjobEntity->getActive()) {
//            dd($new_command_expression);
            self::addJob($new_command_expression);
        }

        foreach ($routes as $route) {
            if (!self::doesJobExist($route) && $cronjobEntity->getActive() == 1) {
                $cronjobEntity->setActive(false);
                throw new \RuntimeException('Something went wrong, please check your cronjob');
            }
        }

        $this->fmCronjobManagerRepository->save($cronjobEntity, true);
        return true;
    }
}
