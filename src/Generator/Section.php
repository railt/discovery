<?php
/**
 * This file is part of Railt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Railt\Discovery\Generator;

use Railt\Io\File;
use Railt\Json\Validator;
use Railt\Json\ValidatorInterface;

/**
 * Class Section
 */
class Section
{
    /**
     * @var Package
     */
    private $package;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $data;

    /**
     * @var array|ValidatorInterface[]
     */
    private $schemas = [];

    /**
     * Section constructor.
     *
     * @param Package $package
     * @param string $name
     * @param array $data
     */
    public function __construct(Package $package, string $name, array $data)
    {
        $this->package = $package;
        $this->name = $name;
        $this->data = $data;

        \var_dump($data);
    }

    /**
     * @param Section $section
     * @throws \InvalidArgumentException
     * @throws \JsonException
     * @throws \Railt\Io\Exception\NotReadableException
     */
    public function merge(Section $section): void
    {
        foreach ($section->getSchemas() as $schema) {
            $this->schemas[] = $schema;
        }
    }

    /**
     * @return \Traversable|ValidatorInterface[]
     * @throws \InvalidArgumentException
     * @throws \JsonException
     * @throws \Railt\Io\Exception\NotReadableException
     */
    public function getSchemas(): \Traversable
    {
        $directory = $this->package->getDirectory();
        $schemas = (array)($this->data['schema'] ?? []);

        foreach ($schemas as $schema) {
            yield Validator::fromFile(File::fromPathname($directory . '/' .  $schema));
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
