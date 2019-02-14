<?php
/**
 * This file is part of Railt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Railt\Discovery\Reader;

use Railt\Io\File;
use Railt\Json\Json;
use Railt\Json\Validator;
use Railt\Json\Validator\ResultInterface;
use Railt\Json\ValidatorInterface;

/**
 * Class Reader
 */
abstract class Reader implements ReaderInterface
{
    /**
     * @var string
     */
    protected const SCHEMA_PATHNAME = __DIR__ . '/../../resources/discovery.schema.json';

    /**
     * @var string
     */
    protected const VARIABLE_PATTERN = '/\$\{([a-z0-9_\-]+)\}/isum';

    /**
     * @var array|object|mixed
     */
    protected $data;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var bool
     */
    private $validated = false;

    /**
     * @var bool
     */
    private $empty;

    /**
     * @var array
     */
    private $variables = [];

    /**
     * ArrayReader constructor.
     *
     * @param array|object|mixed $data
     * @param bool $empty
     * @throws \JsonException
     * @throws \Railt\Io\Exception\NotReadableException
     */
    public function __construct($data, bool $empty = false)
    {
        $this->empty = $empty;
        $this->data = $data;
        $this->validator = Validator::fromFile(File::fromPathname(self::SCHEMA_PATHNAME));
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    private function bypass($data)
    {
        switch (true) {
            case \is_string($data):
                $data = $this->replace($data);
                break;

            case \is_iterable($data):
                $result = [];

                foreach ($data as $key => $value) {
                    if (\is_string($key)) {
                        $key = $this->replace($key);
                    }

                    $result[$key] = $this->bypass($value);
                }

                $data = $result;
                break;
        }

        return $data;
    }

    /**
     * @param string $data
     * @return string
     */
    private function replace(string $data): string
    {
        return \preg_replace_callback(self::VARIABLE_PATTERN, function (array $item) {
            $result = $this->variables[$item[1]] ?? null;

            if ($result instanceof \Closure) {
                return $result();
            }

            if ($result !== null) {
                return $result;
            }

            return $item[0];
        }, $data);
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->empty;
    }

    /**
     * @return ValidatorInterface
     */
    public function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }

    /**
     * @return array|mixed|object
     */
    public function get()
    {
        if ($this->validated === false && $this->empty === false) {
            $this->validate($this->validator);
            $this->validated = true;
        }

        return $this->bypass($this->data);
    }

    /**
     * @param ValidatorInterface $validator
     * @return ResultInterface
     */
    public function validate(ValidatorInterface $validator): ResultInterface
    {
        return $validator->validate($this->data);
    }

    /**
     * @param string $variable
     * @param mixed $value
     * @return ReaderInterface
     */
    public function define(string $variable, $value): ReaderInterface
    {
        $this->variables[$variable] = $value;

        return $this;
    }
}
