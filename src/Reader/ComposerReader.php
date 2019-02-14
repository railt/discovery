<?php
/**
 * This file is part of Railt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Railt\Discovery\Reader;

use Composer\Package\PackageInterface;
use Railt\Discovery\Exception\ValidationException;
use Railt\Json\Exception\JsonValidationExceptionInterface;
use Railt\Json\Validator\ResultInterface;
use Railt\Json\ValidatorInterface;

/**
 * Class ComposerReader
 */
class ComposerReader extends Reader
{
    /**
     * @var string
     */
    private $section;

    /**
     * @var PackageInterface
     */
    private $package;

    /**
     * ComposerReader constructor.
     *
     * @param PackageInterface $package
     * @param string $section
     * @throws \JsonException
     * @throws \Railt\Io\Exception\NotReadableException
     */
    public function __construct(PackageInterface $package, string $section)
    {
        $this->section = $section;
        $this->package = $package;

        $extra = $package->getExtra();

        parent::__construct($extra[$section] ?? null, ! isset($extra[$section]));
    }

    /**
     * @param ValidatorInterface $validator
     * @return ResultInterface
     * @throws ValidationException
     */
    public function validate(ValidatorInterface $validator): ResultInterface
    {
        try {
            return parent::validate($validator);
        } catch (JsonValidationExceptionInterface $e) {
            throw new ValidationException($this->getValidationExceptionMessage($e));
        }
    }

    /**
     * @param JsonValidationExceptionInterface $e
     * @return string
     */
    private function getValidationExceptionMessage(JsonValidationExceptionInterface $e): string
    {
        $path = \array_merge(['extra', $this->section], $e->getPath());
        $message = '%s in "%s" of "%s" composer package';

        return \sprintf($message, $e->getMessage(), \implode('.', $path), $this->package->getName());
    }
}
