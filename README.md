## FmCronjobManager

***

**Author**: <small>Fabrizio Manca</small>

**Email**: <small>contact@fabriziomanca.fr</small>

**Requires**: <small>Symfony >= 6.3</small>

**Requires**: <small>PHP >= 8.2</small>

**License**: <small>MIT</small>

## Installation

    composer require fabriman/cronjob-manager

## New Cronjob File Controller Example
Create a cronjob controller:

    <?php

    namespace App\Controller\Cronjobs\YourClassName;
    
    use Symfony\Component\Routing\Annotation\Route;
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Component\HttpFoundation\Response;
    use Fm\CronjobManagerBundle\Service\CronjobExecutionServcie;
    use Throwable;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    
    #[Route("/main/route")]
    class YourClassName extends CronjobExecutionServcie
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
