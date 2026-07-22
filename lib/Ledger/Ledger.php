<?php

namespace Loom73\Ledger;


use Loom73\Woodframe\Config;
use Loom73\Woodframe\Logger;
use Loom73\Woodframe\OwnerRegistry;

class Ledger
{
    protected ?LedgerEvent $Event = null;
    protected ?OwnerRegistry $Owners = null;


    public function record(
        ?int $actorId,
        string $action,
        string $ownerType,
        string $ownerId,
        string $summary,
        array $metadata = []
    ): bool {
        if (!Config::get('ledger.enabled')) {
            return false;
        }

        $action = trim($action);
        $ownerType = trim($ownerType);
        $ownerId = trim($ownerId);
        $summary = trim($summary);

        if (!$this->validate(
            actorId: $actorId,
            action: $action,
            ownerType: $ownerType,
            ownerId: $ownerId,
            summary: $summary,
            metadata: $metadata
        )) {
            Logger::error(
                'Ledger',
                'Event validation failed',
                [
                    'action' => $action,
                    'actor' => $actorId,
                    'owner_type' => $ownerType,
                    'owner_id' => $ownerId,
                ]
            );

            return false;
        }


        try {
            $encodedMetadata = $metadata === []
                ? null
                : json_encode(
                    $metadata,
                    JSON_THROW_ON_ERROR |
                    JSON_UNESCAPED_UNICODE |
                    JSON_UNESCAPED_SLASHES
                );
        } catch (\JsonException $e) {
            Logger::error(
                'Ledger',
                'Failed to serialize event metadata',
                [
                    'action' => $action,
                    'actor' => $actorId,
                    'owner_type' => $ownerType,
                    'owner_id' => $ownerId,
                    'error' => $e->getMessage(),
                ]
            );

            return false;
        }
        try {

            $result = $this->event()->create([
                'actor_user' => $actorId,
                'action' => $action,
                'owner_type' => $ownerType,
                'owner_id' => $ownerId,
                'summary' => $summary,
                'metadata' => $encodedMetadata,
                'ip_address' => $this->ipAddress(),
                'user_agent' => $this->userAgent(),
            ]);

            if ($result->fails()) {
                Logger::error(
                    'Ledger',
                    'Failed to persist event',
                    [
                        $action,
                        $actorId,
                        $ownerType,
                        $ownerId,
                        $result->errorMessage()
                    ]
                );

                return false;
            }

            return true;

        } catch (\Throwable $e) {
            Logger::error(
                'Ledger',
                'Unexpected error while recording event',
                [
                    $action,
                    $actorId,
                    $ownerType,
                    $ownerId,
                    $e->getMessage()
                ]
            );

            return false;
        }
    }

    protected function event(): LedgerEvent
    {
        return $this->Event ??= new LedgerEvent();
    }
    protected function owners(): ?OwnerRegistry
    {
        return $this->Owners ??= new OwnerRegistry();
    }

    protected function ipAddress(): ?string
    {
        return isset($_SERVER['REMOTE_ADDR'])
            ? substr($_SERVER['REMOTE_ADDR'], 0, 45)
            : null;
    }

    protected function userAgent(): ?string
    {
        return isset($_SERVER['HTTP_USER_AGENT'])
            ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255)
            : null;
    }

    /** Validate Event Data */
    protected function validate(
        ?int $actorId,
        string $action,
        string $ownerType,
        string $ownerId,
        string $summary,
        array $metadata
    ): bool {
        if ($actorId !== null && $actorId < 1) {
            return false;
        }

        if (
            $action === '' ||
            strlen($action) > 120 ||
            !preg_match('/^[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)+$/', $action)
        ) {
            return false;
        }

        if (
            $ownerType === '' ||
            strlen($ownerType) > 100 ||
            !$this->owners()->has($ownerType)
        ) {
            return false;
        }

        if ($ownerId === '' || strlen($ownerId) > 100) {
            return false;
        }

        if ($summary === '' || strlen($summary) > 255) {
            return false;
        }
        return true;
    }
}