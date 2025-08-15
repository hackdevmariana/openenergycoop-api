<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

/**
 * Interface for models that support versioning
 */
interface Versionable
{
    /**
     * Create a new version of this model
     */
    public function createVersion(string $comment = null): void;

    /**
     * Get all versions of this model
     */
    public function getVersions(): Collection;

    /**
     * Get the latest version
     */
    public function getLatestVersion();

    /**
     * Restore a specific version
     */
    public function restoreVersion(int $versionId): bool;

    /**
     * Check if this is the latest version
     */
    public function isLatestVersion(): bool;

    /**
     * Get version number
     */
    public function getVersionNumber(): string;

    /**
     * Compare with another version
     */
    public function compareWith($otherVersion): array;
}
