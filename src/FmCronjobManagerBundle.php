<?php

namespace Fm\CronjobManagerBundle;

use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Fm\CronjobManagerBundle\DependencyInjection\FmCronjobManagerBundleExtension;

class FmCronjobManagerBundle extends AbstractBundle
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
