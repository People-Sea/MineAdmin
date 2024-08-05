<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */

namespace App\Listener;

use App\Kernel\Traits\GetDebugTrait;
use Hyperf\Command\Event\FailToHandle;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;

#[Listener]
class FailToHandleListener implements ListenerInterface
{
    use GetDebugTrait;

    private LoggerInterface $logger;

    public function __construct(
        private readonly StdoutLoggerInterface $stdoutLogger,
        private readonly FormatterInterface $formatter,
        LoggerFactory $loggerFactory,
    ) {
        $this->logger = $loggerFactory->get('command');
    }

    public function listen(): array
    {
        return [
            FailToHandle::class,
        ];
    }

    /**
     * @param FailToHandle $event
     */
    public function process(object $event): void
    {
        $format = sprintf('%s Command failed to handle, %s', $event->getCommand()->getName(), $this->formatter->format($event->getThrowable()));
        $this->isDebug() ? $this->logger->error($format) : $this->stdoutLogger->error($format);
    }
}