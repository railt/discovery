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
use Railt\Discovery\Composer\Package;
use Railt\Discovery\Composer\Reader;
use Railt\Discovery\Composer\Section;
use Railt\Discovery\Composer\SectionConfiguration;
use Railt\Discovery\Exception\ValidationException;
use Railt\Json\Exception\JsonValidationExceptionInterface;
use Railt\Json\ValidatorInterface;

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
     * Generator constructor.
     *
     * @param Composer $composer
     * @param IOInterface $io
     */
    public function __construct(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    /**
     * @return array
     * @throws ValidationException
     */
    public function run(): array
    {
        $reader = new Reader($this->composer);

        $sections = [];

        foreach ($this->collect($reader, $this->io) as $section) {
            $name = $section->getName();

            if (! isset($sections[$name])) {
                $sections[$name] = [];
            }

            /** @noinspection SlowArrayOperationsInLoopInspection */
            $sections[$name] = \array_merge_recursive($sections[$name], $section->get());
        }

        return $sections;
    }

    /**
     * @param Reader $reader
     * @param IOInterface $io
     * @return \Traversable|Section[]
     * @throws ValidationException
     */
    private function collect(Reader $reader, IOInterface $io): \Traversable
    {
        $sections = $this->loadConfigs($reader);

        foreach ($sections as $name => $validators) {
            $io->write(\sprintf('Discovery: <info>%s</info>', $name));

            /**
             * @var Package $package
             * @var Section $section
             */
            foreach ($this->readSection($name, $reader) as $package => $section) {
                $io->write(\sprintf('    import from <comment>%s</comment>: ', $package->getName()), false);

                try {
                    $section->validateAll($validators);
                    $io->write('<info>OK</info>');
                } catch (JsonValidationExceptionInterface $e) {
                    $io->write('<error>FAIL</error>');
                    throw ValidationException::fromJsonException($e, $package, $section);
                } catch (\Throwable $e) {
                    $io->write('<error>FAIL</error>');
                    throw ValidationException::fromException($e, $package);
                }

                yield $section;
            }
        }
    }

    /**
     * @param string $name
     * @param Reader $reader
     * @return \Traversable
     */
    private function readSection(string $name, Reader $reader): \Traversable
    {
        foreach ($reader->getPackages() as $package) {
            $section = $package->getSection($name);

            if ($section) {
                yield $package => $section;
            }
        }
    }

    /**
     * @param Reader $reader
     * @return array
     */
    private function loadConfigs(Reader $reader): array
    {
        $sections = [];

        foreach ($this->readConfigs($reader) as $name => $validator) {
            if (! isset($sections[$name])) {
                $sections[$name] = [];
            }

            if ($validator !== null) {
                $sections[$name][] = $validator;
            }
        }

        return $sections;
    }

    /**
     * @param Reader $reader
     * @return \Traversable|ValidatorInterface[]|null[]
     */
    private function readConfigs(Reader $reader): \Traversable
    {
        foreach ($reader->getPackages() as $package) {
            $section = $package->getSection(SectionConfiguration::KEY_DISCOVERY);

            if ($section !== null) {
                foreach ($section->getConfiguration() as $name => $validator) {
                    yield $name => $validator;
                }
            }
        }
    }
}
