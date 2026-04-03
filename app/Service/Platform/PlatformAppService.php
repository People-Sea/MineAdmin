<?php

declare(strict_types=1);

namespace App\Service\Platform;

use App\Library\Security\PlatformAppSecretCipher;
use App\Model\PlatformApp;
use App\Repository\Platform\PlatformAppRepository;
use App\Service\IService;
use Carbon\Carbon;
use Hyperf\Collection\Collection;

/**
 * @extends IService<PlatformApp>
 */
final class PlatformAppService extends IService
{
    public function __construct(
        protected readonly PlatformAppRepository $repository,
        private readonly PlatformAppSecretCipher $cipher
    ) {}

    public function options(): Collection
    {
        return $this->repository->listEnabled()->map(static function (PlatformApp $app) {
            return [
                'id' => $app->id,
                'name' => $app->name,
                'app_id' => $app->app_id,
                'status' => $app->status,
                'availability_status' => $app->availability_status,
            ];
        });
    }

    public function create(array $data): mixed
    {
        return parent::create($this->preparePayload($data));
    }

    public function updateById(mixed $id, array $data): mixed
    {
        /** @var PlatformApp|null $app */
        $app = $this->findById($id);
        if ($app === null) {
            return false;
        }

        return parent::updateById($id, $this->preparePayload($data, $app));
    }

    public function enableById(int $id, int $operatorId): bool
    {
        /** @var PlatformApp|null $app */
        $app = $this->findById($id);
        if ($app === null) {
            return false;
        }

        return (bool) parent::updateById($id, $this->buildAvailabilityFields([
            'status' => PlatformApp::STATUS_ENABLED,
            'updated_by' => $operatorId,
        ], $app));
    }

    public function disableById(int $id, int $operatorId): bool
    {
        /** @var PlatformApp|null $app */
        $app = $this->findById($id);
        if ($app === null) {
            return false;
        }

        return (bool) parent::updateById($id, $this->buildAvailabilityFields([
            'status' => PlatformApp::STATUS_DISABLED,
            'updated_by' => $operatorId,
        ], $app));
    }

    /**
     * @param array<string,mixed> $data
     * @return array<string,mixed>
     */
    private function preparePayload(array $data, ?PlatformApp $app = null): array
    {
        $payload = $data;
        $appSecret = trim((string) ($payload['app_secret'] ?? ''));
        unset($payload['app_secret']);

        if ($appSecret !== '') {
            $payload['secret_ciphertext'] = $this->cipher->encrypt($appSecret);
        }

        return $this->buildAvailabilityFields($payload, $app);
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    private function buildAvailabilityFields(array $payload, ?PlatformApp $app = null): array
    {
        $appId = trim((string) ($payload['app_id'] ?? $app?->app_id ?? ''));
        $callbackUrl = trim((string) ($payload['callback_url'] ?? $app?->callback_url ?? ''));
        $secretCiphertext = trim((string) ($payload['secret_ciphertext'] ?? $app?->secret_ciphertext ?? ''));
        $status = (int) ($payload['status'] ?? $app?->status ?? PlatformApp::STATUS_ENABLED);

        $payload['last_check_at'] = Carbon::now();

        if ($status === PlatformApp::STATUS_DISABLED) {
            return $payload + [
                'availability_status' => PlatformApp::AVAILABILITY_UNAVAILABLE,
                'last_error_code' => 'platform_disabled',
                'last_error_message' => '平台应用已停用',
            ];
        }

        if ($appId === '') {
            return $payload + [
                'availability_status' => PlatformApp::AVAILABILITY_UNAVAILABLE,
                'last_error_code' => 'missing_app_id',
                'last_error_message' => '缺少应用ID',
            ];
        }

        if ($secretCiphertext === '') {
            return $payload + [
                'availability_status' => PlatformApp::AVAILABILITY_UNAVAILABLE,
                'last_error_code' => 'missing_app_secret',
                'last_error_message' => '缺少应用密钥',
            ];
        }

        if ($callbackUrl === '') {
            return $payload + [
                'availability_status' => PlatformApp::AVAILABILITY_UNAVAILABLE,
                'last_error_code' => 'missing_callback_url',
                'last_error_message' => '缺少回调地址',
            ];
        }

        if (! filter_var($callbackUrl, FILTER_VALIDATE_URL)) {
            return $payload + [
                'availability_status' => PlatformApp::AVAILABILITY_UNAVAILABLE,
                'last_error_code' => 'invalid_callback_url',
                'last_error_message' => '回调地址格式不正确',
            ];
        }

        return $payload + [
            'availability_status' => PlatformApp::AVAILABILITY_UNKNOWN,
            'last_error_code' => '',
            'last_error_message' => '',
        ];
    }
}
