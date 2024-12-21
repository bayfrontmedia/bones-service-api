<?php

namespace Bayfront\BonesService\Api\Traits;

trait Auditable
{

    /**
     * Get array of function names to audit.
     *
     * @return array
     */
    abstract public function getAuditableFunctions(): array;

    /*
     * Auditable actions
     * See: https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/events.md
     */

    public const AUDIT_ACTION_CREATED = 'created';
    public const AUDIT_ACTION_UPDATED = 'updated';
    public const AUDIT_ACTION_TRASHED = 'trashed';
    public const AUDIT_ACTION_RESTORED = 'restored';
    public const AUDIT_ACTION_DELETED = 'deleted';

    /**
     * Get array of actions to audit.
     *
     * @return array
     */
    public function getAuditableActions(): array
    {
        return [];
    }

}