<?php
/**
 * This file is part of Railt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Railt\Discovery;

use Composer\Script\Event;

/**
 * Class Manifest
 *
 * @deprecated Please use "Railt\Discovery\Generator::build" instead
 */
class Manifest
{
    /**
     * @var string
     */
    private const DEPRECATION_NOTICE =
        '<comment>The "%s" composer script is deprecated and may be ' .
        'removed in future releases, please use "%s" instead</comment>';

    /**
     * @deprecated Please use "Railt\Discovery\Generator::build" instead
     *
     * @param Event $event
     */
    public static function discover(Event $event): void
    {
        $event->getIO()->write(self::deprecationMessage());

        Generator::build($event);
    }

    /**
     * @return string
     */
    private static function deprecationMessage(): string
    {
        return \vsprintf(self::DEPRECATION_NOTICE, [
            static::class . '::discover',
            Generator::class . '::build',
        ]);
    }
}
