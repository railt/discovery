<?php
/**
 * This file is part of Railt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Railt\Discovery\Reader;

use Railt\Discovery\Exception\ValidationException;
use Railt\Json\Exception\JsonValidationExceptionInterface;
use Railt\Json\Validator\ResultInterface;
use Railt\Json\ValidatorInterface;

/**
 * Class DataReader
 */
class DataReader extends Reader
{
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
            throw new ValidationException($e->getMessage());
        }
    }
}
