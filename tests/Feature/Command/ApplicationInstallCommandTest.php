<?php

declare(strict_types=1);

namespace HyperfTests\Feature\Command;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ApplicationInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * @internal
 * @coversNothing
 */
#[Group('migrations')]
final class ApplicationInstallCommandTest extends TestCase
{
    public function testRun(): void
    {
        $app = ApplicationContext::getContainer()->get(ApplicationInterface::class);
        $app->setAutoExit(false);
        $app->run(new ArrayInput(['migrate']), new ConsoleOutput());
        $app->run(new ArrayInput(['db:seed']), new ConsoleOutput());
        self::assertTrue(true);
    }
}
