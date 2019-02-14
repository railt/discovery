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
use Composer\IO\IOInterface;
use Composer\Script\Event;
use Railt\Discovery\Exception\ConfigurationException;
use Railt\Discovery\Generator\Package;
use Railt\Discovery\Generator\Section;
use Railt\Io\Exception\NotReadableException;

/**
 * Class Generator
 */
class Generator
{
    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var array|Section[]
     */
    private $sections = [];

    /**
     * Generator constructor.
     *
     * @param Composer $composer
     * @param IOInterface $io
     * @throws ConfigurationException
     * @throws \InvalidArgumentException
     */
    public function __construct(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;

        try {
            $this->bootSections();
        } catch (NotReadableException | \JsonException $e) {
            throw new ConfigurationException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @throws \JsonException
     * @throws \Railt\Io\Exception\NotReadableException
     * @throws \InvalidArgumentException
     * @throws ConfigurationException
     */
    private function bootSections(): void
    {
        foreach ($this->getPackages() as $package) {
            foreach ($package->getSections() as $section) {
                if (isset($this->sections[$section->getName()])) {
                    $this->sections[$section->getName()]->merge($section);
                    continue;
                }

                $this->sections[$section->getName()] = $section;
            }
        }
    }

    /**
     * @return \Traversable|Package[]
     * @throws \JsonException
     * @throws \Railt\Io\Exception\NotReadableException
     */
    protected function getPackages(): \Traversable
    {
        $local = $this->composer->getRepositoryManager()->getLocalRepository();

        foreach ($local->getPackages() as $package) {
            yield new Package($this->composer, $package);
        }

        yield new Package($this->composer, $this->composer->getPackage());
    }

    /**
     * @param Event $event
     * @throws ConfigurationException
     * @throws \InvalidArgumentException
     */
    public static function build(Event $event): void
    {
        new static($event->getComposer(), $event->getIO());
    }
}
