<?php
/**
 * This file is part of Railt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Railt\Discovery;

use Composer\Autoload\ClassLoader;
use Composer\Composer;

/**
 * Class Discovery
 */
class Discovery
{
    /**
     * @var string
     */
    public const DISCOVERY_MANIFEST_FILENAME = 'composer/discovery.json';

    /**
     * @var string
     */
    private $pathname;

    /**
     * @var array|null
     */
    private $data;

    /**
     * Discovery constructor.
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->pathname = $this->getInstallationPathname($path);
    }

    /**
     * @param Composer $composer
     * @return Discovery
     * @throws \RuntimeException
     */
    public static function fromComposer(Composer $composer): self
    {
        $path = $composer->getConfig()->get('vendor-dir');

        return new static($path);
    }

    /**
     * @param array $paths
     * @return Discovery
     * @throws \LogicException
     */
    public static function auto(array $paths = []): self
    {
        $paths = \array_merge($paths, [
            __DIR__ . '/../vendor',
            __DIR__ . '/../../..',
        ]);

        foreach ($paths as $path) {
            if (\is_file($path . '/autoload.php')) {
                return new static($path);
            }
        }

        $error = 'Unable to determine the installation directory of the composer';
        throw new \LogicException($error);
    }

    /**
     * @param ClassLoader $loader
     * @return Discovery
     */
    public static function fromClassLoader(ClassLoader $loader): self
    {
        $reflection = new \ReflectionObject($loader);

        return new static(\dirname($reflection->getFileName(), 2));
    }

    /**
     * @param string $path
     * @return string
     */
    private function getInstallationPathname(string $path): string
    {
        return $path . \DIRECTORY_SEPARATOR . self::DISCOVERY_MANIFEST_FILENAME;
    }

    /**
     * @param array $data
     * @return string
     * @throws \InvalidArgumentException
     */
    public function write(array $data): string
    {
        \file_put_contents($this->pathname, $this->encode($data), \LOCK_EX);

        return $this->pathname;
    }

    /**
     * @param array $data
     * @return string
     * @throws \InvalidArgumentException
     */
    public function append(array $data): string
    {
        return $this->write(\array_merge_recursive($this->all(), $data));
    }

    /**
     * @param array $data
     * @return Discovery
     */
    public function set(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param array $data
     * @return string
     * @throws \InvalidArgumentException
     */
    private function encode(array $data): string
    {
        $json = \json_encode($data, $this->getJsonFlags());

        if (\json_last_error() !== \JSON_ERROR_NONE) {
            throw new \InvalidArgumentException(\json_last_error_msg());
        }

        return $json;
    }

    /**
     * @return int
     */
    private function getJsonFlags(): int
    {
        return \defined('\\JSON_THROW_ON_ERROR') ? \JSON_THROW_ON_ERROR : 4194304;
    }

    /**
     * @param string $json
     * @return array
     * @throws \InvalidArgumentException
     */
    private function decode(string $json): array
    {
        $data = \json_decode($json, true, 512, $this->getJsonFlags());

        if (\json_last_error() !== \JSON_ERROR_NONE) {
            throw new \InvalidArgumentException(\json_last_error_msg());
        }

        return $data;
    }

    /**
     * @return array
     * @throws \InvalidArgumentException
     */
    public function all(): array
    {
        if ($this->data === null) {
            $this->data = \is_file($this->pathname)
                ? $this->decode((string)\file_get_contents($this->pathname))
                : [];
        }

        return $this->data;
    }

    /**
     * @param string $key
     * @param null $default
     * @return array|mixed
     * @throws \InvalidArgumentException
     */
    public function get(string $key, $default = null)
    {
        $array = $this->all();

        foreach (\explode('.', $key) as $segment) {
            $allowsNext = \is_array($array) && isset($array[$segment]) && \array_key_exists($segment, $array);

            if ($allowsNext) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }
}
