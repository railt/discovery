<?php
/**
 * This file is part of Railt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Railt\Discovery\Generator;

use Composer\Composer;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;
use Railt\Discovery\Exception\ConfigurationException;
use Railt\Discovery\Exception\ValidationException;
use Railt\Discovery\Reader\ComposerReader;
use Railt\Discovery\Reader\ReaderInterface;
use Railt\Json\Exception\JsonValidationExceptionInterface;

/**
 * Class Package
 */
class Package
{
    /**
     * @var PackageInterface
     */
    private $package;

    /**
     * @var ReaderInterface
     */
    private $sections;

    /**
     * @var Composer
     */
    private $composer;

    /**
     * Package constructor.
     *
     * @param Composer $composer
     * @param PackageInterface $package
     * @throws \JsonException
     * @throws \Railt\Io\Exception\NotReadableException
     */
    public function __construct(Composer $composer, PackageInterface $package)
    {
        $this->package = $package;
        $this->composer = $composer;


        $sections = new ComposerReader($package, 'discovery');
        $sections = $this->withVariables($sections);

        $this->sections = $sections;
    }

    /**
     * @param ReaderInterface $reader
     * @return ReaderInterface
     */
    private function withVariables(ReaderInterface $reader): ReaderInterface
    {
        $reader->define('dir', function () {
            return $this->getDirectory();
        });

        $reader->define('root.dir', function () {
            return $this->getRootDirectory();
        });

        $reader->define('root.composer', function () {
            return $this->getRootComposer();
        });

        return $reader;
    }

    /**
     * @return string
     */
    private function getRootComposer(): string
    {
        $config = $this->composer->getConfig();
        $source = $config->getConfigSource();

        return $source->getName();
    }

    /**
     * @return string
     */
    private function getRootDirectory(): string
    {
        return \dirname($this->getRootComposer());
    }

    /**
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getDirectory(): string
    {
        if ($this->isRoot()) {
            return $this->getRootDirectory();
        }

        $installator = $this->composer->getInstallationManager();

        return $installator->getInstallPath($this->package);
    }

    /**
     * @return bool
     */
    public function isRoot(): bool
    {
        return $this->package instanceof RootPackageInterface;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->package->getName();
    }

    /**
     * @return \Traversable|Section[]
     * @throws ConfigurationException
     */
    public function getSections(): \Traversable
    {
        try {
            foreach ((array)$this->sections->get() as $name => $data) {
                [$name, $data] = \is_int($name) ? [$data, []] : [$name, $data];

                yield new Section($this, $name, (array)$data);
            }
        } catch (ValidationException | JsonValidationExceptionInterface $e) {
            throw new ConfigurationException($e->getMessage());
        }
    }
}
