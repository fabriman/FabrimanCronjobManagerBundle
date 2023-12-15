## FmCronjobManager

---

### This version is not mantained anymore

---


A Symfony 5.4 Bundle to manage recurring tasks

## Installation

    composer require fabriman/cronjob-manager

### Create the database table

    php bin/console make:migration
    php bin/console doctrine:migration:migrate

## Usage

Get existing jobs

    php bin/console cronjob:list

Create new job

    php bin/console cronjob:new

Update existing job

    php bin/console cronjob:update

Delete existing job

    php bin/console cronjob:delete


## Example of a new Cronjob task
Create a cronjob controller:

    <?php

    namespace App\Controller\Cronjobs\YourClassName;
    
    use Symfony\Component\Routing\Annotation\Route;
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Component\HttpFoundation\Response;
    use Fm\CronjobManagerBundle\Service\CronjobExecutionService;
    use Throwable;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    
    #[Route("/main/route")]
    class YourClassName extends CronjobExecutionService
    {
        
        // Change the cronjob name wih your cronjob name (Names are uniques)
        public string $cronjob_name = "test";

        // START MANDATORY
       public function __construct(
            public EntityManagerInterface $entityManager,
            public ParameterBagInterface $params
        )
        {
            parent::__construct($this->cronjob_name);
        }
        // END MANDATORY
    
        #[Route("/YOUR_ROUTE", name: "your_route_nam")]
        public function yourMethod(): Response
        {
            try {
                $this->newCronLog("Test Log");
            } catch (Throwable $e) {
                $this->newCronLog("Error: " . $e->getMessage());
            }

            return new Response();
        }
    }
