<?php
/**
 * This file is part of Railt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Railt\Discovery\Composer;

use Phplrt\Io\File;
use Railt\Discovery\Exception\ConfigurationException;
use Railt\Discovery\Exception\ValidationException;
use Railt\Json\Exception\JsonException;
use Railt\Json\Exception\JsonValidationException;
use Railt\Json\Validator\Validator;
use Railt\Json\Validator\ValidatorInterface;

/**
 * Class DiscoverySection
 */
class DiscoverySection implements \IteratorAggregate
{
    /**
     * @var string
     */
    public const KEY_DISCOVERY = 'discovery';

    /**
     * @var string
     */
    private const JSON_SCHEMA_CONFIG_FILE = __DIR__ . '/../Resources/discovery.schema.json5';

    /**
     * @var Section
     */
    private $section;

    /**
     * @var Package
     */
    private $package;

    /**
     * DiscoverySection constructor.
     *
     * @param Package $package
     * @param Section $section
     */
    public function __construct(Package $package, Section $section)
    {
        $this->section = $section;
        $this->package = $package;
    }

    /**
     * @return \Generator|\Traversable
     * @throws ValidationException
     */
    public function getIterator()
    {
        try {
            $this->section->validate($this->getDiscoveryValidator())->throwOnError();

            foreach ($this->section->get() as $name => $configs) {
                [$name, $configs] = \is_int($name) ? [$configs, null] : [$name, $configs];

                yield $name => new DiscoveryConfiguration((array)$configs);
            }
        } catch (JsonValidationException $e) {
            throw ConfigurationException::fromJsonException($e, $this->package, $this->section);
        } catch (\Throwable $e) {
            throw ConfigurationException::fromException($e, $this->package);
        }
    }

    /**
     * @return ValidatorInterface
     * @throws JsonException
     */
    public function getDiscoveryValidator(): ValidatorInterface
    {
        $file = File::fromPathname(self::JSON_SCHEMA_CONFIG_FILE);

        return Validator::fromFile($file);
    }
}
