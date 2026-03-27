<?php

declare(strict_types=1);

namespace App\Http\Admin\Subscriber\Logstash;

use App\Http\Common\Event\RequestOperationEvent;
use App\Service\Logstash\UserOperationLogService;
use App\Service\Permission\UserService;
use Hyperf\Engine\Coroutine;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;

#[Listener]
class UserOperationSubscriber implements ListenerInterface
{
    public function __construct(
        private readonly UserOperationLogService $logService,
        private readonly UserService $userService
    ) {}

    public function listen(): array
    {
        return [
            RequestOperationEvent::class,
        ];
    }

    public function process(object $event): void
    {
        if ($event instanceof RequestOperationEvent) {
            $userId = $event->getUserId();
            $user = $this->userService->findById($userId);
            if (empty($user)) {
                return;
            }
            Coroutine::create(fn () => $this->logService->save([
                'tenant_id' => $user->tenant_id,
                'username' => $user->username,
                'method' => $event->getMethod(),
                'router' => $event->getPath(),
                'remark' => $event->getRemark(),
                'ip' => $event->getIp(),
                'service_name' => $event->getOperation(),
            ]));
        }
    }
}
