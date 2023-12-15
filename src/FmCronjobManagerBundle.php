<?php

namespace Fm\CronjobManagerBundle;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Fm\CronjobManagerBundle\DependencyInjection\FmCronjobManagerBundleExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class FmCronjobManagerBundle extends Bundle
{
    public function getPath(): string
    {
        return dirname(__DIR__);
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new FmCronjobManagerBundleExtension();
    }
}
