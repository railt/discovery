<?php
/**
 * This file is part of Railt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Railt\Discovery;

use Composer\Composer;
use Composer\Installer\InstallationManager;
use Composer\Package\RootPackageInterface;
use Composer\Repository\InstalledRepositoryInterface;

/**
 * Class ExtraLoader
 */
class Reader
{
    /**
     * @var InstallationManager
     */
    private $installer;

    /**
     * @var InstalledRepositoryInterface
     */
    private $local;

    /**
     * @var RootPackageInterface
     */
    private $package;

    /**
     * ExtraLoader constructor.
     * @param Composer $composer
     */
    public function __construct(Composer $composer)
    {
        $this->package   = $composer->getPackage();
        $this->installer = $composer->getInstallationManager();
        $this->local     = $composer->getRepositoryManager()->getLocalRepository();
    }

    /**
     * @param string $key
     * @return iterable
     * @throws \InvalidArgumentException
     */
    public function each(string $key): iterable
    {
        yield from $this->loadRootExtras($key);
        yield from $this->loadDependencyExtras($key);
    }

    /**
     * @param string $key
     * @return iterable
     * @throws \InvalidArgumentException
     */
    private function loadDependencyExtras(string $key): iterable
    {
        foreach ($this->local->getPackages() as $package) {
            if ($this->installer->isPackageInstalled($this->local, $package)) {
                yield $package => $this->readExtra($package->getExtra(), $key);
            }
        }
    }

    /**
     * @param array $extra
     * @param string $key
     * @return array
     */
    private function readExtra(array $extra, string $key): array
    {
        return (array)($extra[$key] ?? []);
    }

    /**
     * @param string $key
     * @return iterable
     */
    private function loadRootExtras(string $key): iterable
    {
        yield $this->package => $this->readExtra($this->package->getExtra(), $key);
    }
}
