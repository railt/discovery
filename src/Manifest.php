<?php
/**
 * This file is part of Railt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Railt\Discovery;

/**
 * This class is generated by railt/discovery, specifically by
 * @see \Railt\Discovery\Generator
 *
 * This file is overwritten at every run of `composer install`,
 * `composer dump-autoload` or `composer update`.
 */
class Manifest extends ManifestFallback
{
    /**
     * @var array[]
     */
    protected const CONFIGURATION = [
  'discovery' =>
  [
    'discovery' => 'C:\\Users\\Serafim\\Projects\\Railt\\Railt/packages/Discovery/resources/discovery.schema.json',
    'railt'     => 'C:\\Users\\Serafim\\Projects\\Railt\\Railt/resources/railt.schema.json',
  ],
  'railt' =>
  [
    'commands' =>
    [
      0 => \Railt\Foundation\Console\Command\RepoSyncCommand::class,
      1 => \Railt\Foundation\Console\Command\RepoMergeCommand::class,
      2 => \Railt\Foundation\Console\Command\ExtensionsListCommand::class,
      3 => \Railt\Parser\Console\ParserCompileCommand::class,
    ],
  ],
];
}
