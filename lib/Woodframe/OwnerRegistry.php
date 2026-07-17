<?php

namespace Loom73\Woodframe;

use InvalidArgumentException;

class OwnerRegistry
{
    protected array $owners;

    public function __construct(?array $owners = null)
    {
        $this->owners = $owners ?? Config::get('owners', []);
    }

    public function all(): array
    {
        return $this->owners;
    }

    public function has(string $owner): bool
    {
        return isset($this->owners[$owner]);
    }

    public function get(string $owner): array
    {
        if (!$this->has($owner)) {
            throw new InvalidArgumentException("Unknown owner type: {$owner}");
        }

        return $this->owners[$owner];
    }

    public function label(string $owner): string
    {
        return $this->get($owner)['label'] ?? $owner;
    }

    public function model(string $owner): ?string
    {
        return $this->get($owner)['model'] ?? null;
    }

    public function primaryKey(string $owner): ?string
    {
        return $this->get($owner)['primary_key'] ?? null;
    }

    public function assetSlots(string $owner): array
    {
        return $this->get($owner)['assets'] ?? [];
    }

    public function hasAssetSlot(string $owner, string $slot): bool
    {
        return isset($this->assetSlots($owner)[$slot]);
    }

    public function assetSlot(string $owner, string $slot): array
    {
        $slots = $this->assetSlots($owner);

        if (!isset($slots[$slot])) {
            throw new InvalidArgumentException("Unknown asset slot '{$slot}' for owner '{$owner}'.");
        }

        return $slots[$slot];
    }

    public function slotAllowsMultiple(string $owner, string $slot): bool
    {
        return (bool) ($this->assetSlot($owner, $slot)['multiple'] ?? false);
    }

    public function slotReplacesExisting(string $owner, string $slot): bool
    {
        return (bool) ($this->assetSlot($owner, $slot)['replace_existing'] ?? false);
    }

    public function defaultAssetType(string $owner, string $slot): ?string
    {
        return $this->assetSlot($owner, $slot)['default_asset_type'] ?? null;
    }

    public function defaultVisibility(string $owner, string $slot): ?string
    {
        return $this->assetSlot($owner, $slot)['default_visibility'] ?? null;
    }

    public function slotAllowsAssetType(
        string $owner,
        string $slot,
        string $assetType
    ): bool {
        $allowed = $this->assetSlot($owner, $slot)['allowed_asset_types'] ?? [];

        if (empty($allowed)) {
            return true;
        }

        return in_array($assetType, $allowed, true);
    }
}