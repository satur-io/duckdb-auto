<?php

namespace Saturio\DuckDBInstaller;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Saturio\DuckDB\CLib\Installer as MainPackageInstaller;

class Installer implements PluginInterface, EventSubscriberInterface
{
    protected IOInterface $io;
    protected Composer $composer;

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->io->write('<info>DuckDB PHP plugin activate.</info>');
    }
    public function deactivate(Composer $composer, IOInterface $io) {}
    public function uninstall(Composer $composer, IOInterface $io) {}

    public static function getSubscribedEvents()
    {
        return [
            PackageEvents::POST_PACKAGE_INSTALL => 'onPostPackageEvent',
            PackageEvents::POST_PACKAGE_UPDATE => 'onPostPackageEvent'
        ];
    }

    public function onPostPackageEvent(PackageEvent $event): void
    {
        if  ($event->getOperation() instanceof InstallOperation) {
            $package = $event->getOperation()->getPackage();
        } elseif ($event->getOperation() instanceof UpdateOperation) {
            $package = $event->getOperation()->getTargetPackage();
        } else {
            return;
        }

        if ($package->getName() !== 'satur.io/duckdb-auto') {
            return;
        }

        $this->io->write('<comment>Downloading DuckDB C library for your OS</comment>');
        MainPackageInstaller::install();
        $this->io->write('<info>DuckDB C lib downloaded.</info>');
    }
}
