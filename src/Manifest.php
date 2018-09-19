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
use Composer\Script\Event;

/**
 * Class Manifest
 */
class Manifest
{
    /**
     * @var string
     */
    public const EXTRA_SECTIONS = 'discovery';

    /**
     * @var Reader
     */
    private $reader;

    /**
     * Manifest constructor.
     * @param Composer $composer
     */
    public function __construct(Composer $composer)
    {
        $this->reader = new Reader($composer);
    }

    /**
     * @return array
     * @throws \InvalidArgumentException
     */
    public function get(): array
    {
        $sections = $this->fetchExtraSections();

        $result = [];

        foreach ($sections as $section) {
            \assert(\is_string($section));

            $result[$section] = $this->fetchExtra($section);
        }

        return $result;
    }

    /**
     * @return array
     * @throws \InvalidArgumentException
     */
    public function fetchExtraSections(): array
    {
        return \array_unique($this->fetchExtra(self::EXTRA_SECTIONS));
    }

    /**
     * @param string $key
     * @return array
     * @throws \InvalidArgumentException
     */
    public function fetchExtra(string $key): array
    {
        $result = [];

        foreach ($this->reader->each($key) as $package => $data) {
            $result = \array_merge_recursive($result, $data);
        }

        return $result;
    }

    /**
     * @param Event $event
     * @throws \RuntimeException
     */
    public static function discover(Event $event): void
    {
        $composer = $event->getComposer();

        Discovery::fromComposer($composer)
            ->write((new static($composer))->get());
    }
}
