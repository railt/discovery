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
    private const EXTRA_KEYS_IMPLODE = ':';

    /**
     * @var string
     */
    public const EXTRA_SECTIONS = 'discovery';

    /**
     * @var string
     */
    public const EXTRA_SECTIONS_EXCEPT = 'discovery:except';

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var array|string[]
     */
    private $except = [];

    /**
     * Manifest constructor.
     * @param Composer $composer
     */
    public function __construct(Composer $composer)
    {
        $this->reader = new Reader($composer);

        $this->loadExceptExtras();
    }

    /**
     * @return void
     */
    private function loadExceptExtras(): void
    {
        $this->except = \array_unique($this->reader->loadRootExtras(self::EXTRA_SECTIONS_EXCEPT));
    }

    /**
     * @param array $array
     * @param \Closure $filter
     * @param array $prefix
     * @return array
     */
    private function exceptFilter(array $array, \Closure $filter, array $prefix = []): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $realPrefix = $this->resolvePrefix($key, $value, $prefix);

            if (! $filter(\implode(self::EXTRA_KEYS_IMPLODE, $realPrefix))) {
                continue;
            }

            $result[$key] = \is_array($value)
                ? $this->exceptFilter($value, $filter, $realPrefix)
                : $value;
        }

        return $result;
    }

    /**
     * @param string|int $key
     * @param mixed $value
     * @param array $prefix
     * @return array
     */
    private function resolvePrefix($key, $value, array $prefix): array
    {
        $prefix = \is_string($value)
            ? \array_merge($prefix, [$key, $value])
            : \array_merge($prefix, [$key]);

        return \array_filter($prefix, '\\is_string');
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

        return $this->except($result);
    }

    /**
     * @param array $data
     * @return array
     */
    private function except(array $data): array
    {
        return $this->exceptFilter($data, function (string $key) {
            foreach ($this->except as $exceptKey) {
                if (\stripos($key, $exceptKey) === 0) {
                    return false;
                }
            }

            return true;
        });
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

        foreach ($this->reader->loadExtras($key) as $package => $data) {
            $result = \array_merge_recursive($result, $data);
        }

        return $result;
    }

    /**
     * @param Event $event
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public static function discover(Event $event): void
    {
        $composer = $event->getComposer();

        $discovery = Discovery::fromComposer($composer);
        $discovery->write((new static($composer))->get());
    }
}
