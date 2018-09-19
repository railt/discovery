<?php
/**
 * This file is part of Railt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Railt\Tests\Discovery;

use Composer\Autoload\ClassLoader;
use Composer\Composer;
use Composer\Config;
use Railt\Discovery\Discovery;

/**
 * Class ReaderTestCase
 */
class ReaderTestCase extends TestCase
{
    /**
     * @return array
     * @throws \RuntimeException
     */
    public function provider(): array
    {
        $loader = new ClassLoader();

        $composer = new Composer();
        $composer->setConfig(new Config(false, __DIR__ . '/../'));

        return [
            '__construct'     => [new Discovery(__DIR__ . '/../vendor/composer')],
            'fromClassLoader' => [Discovery::fromClassLoader($loader)],
            'fromComposer'    => [Discovery::fromComposer($composer)],
        ];
    }

    /**
     * @dataProvider provider
     *
     * @param Discovery $discovery
     * @throws \InvalidArgumentException
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     */
    public function testNamespaces(Discovery $discovery): void
    {
        $this->assertEquals(['allows-in-unit-tests'], \array_keys($discovery->all()));
    }

    /**
     * @dataProvider provider
     *
     * @param Discovery $discovery
     * @throws \InvalidArgumentException
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     */
    public function testUndefinedFields(Discovery $discovery): void
    {
        $this->assertNull($discovery->get('undefined.field'));
    }

    /**
     * @dataProvider provider
     *
     * @param Discovery $discovery
     * @throws \InvalidArgumentException
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     */
    public function testNonDiscoveredFields(Discovery $discovery): void
    {
        $this->assertNull($discovery->get('hidden-from-unit-tests'));
        $this->assertNull($discovery->get('hidden-from-unit-tests.example'));
    }

    /**
     * @dataProvider provider
     *
     * @param Discovery $discovery
     * @throws \InvalidArgumentException
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     */
    public function testDiscoveredFields(Discovery $discovery): void
    {
        $this->assertEquals(['example' => 'valid-value'], $discovery->get('allows-in-unit-tests'));
        $this->assertEquals('valid-value', $discovery->get('allows-in-unit-tests.example'));
        $this->assertNull($discovery->get('allows-in-unit-tests.example.undefined'));
    }
}
