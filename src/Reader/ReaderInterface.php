<?php
/**
 * This file is part of Railt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Railt\Discovery\Reader;

use Railt\Json\Validator\ResultInterface;
use Railt\Json\ValidatorInterface;

/**
 * Interface ReaderInterface
 */
interface ReaderInterface
{
    /**
     * @return array|object|mixed
     */
    public function get();

    /**
     * @param ValidatorInterface $validator
     * @return ResultInterface
     */
    public function validate(ValidatorInterface $validator): ResultInterface;

    /**
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * @param string $variable
     * @param mixed $value
     * @return ReaderInterface
     */
    public function define(string $variable, $value): ReaderInterface;
}
