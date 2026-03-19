<?php

namespace App\Enums;

/**
 * Defines all valid organization-scoped permission names. This enum is the
 * single source of truth referenced by the permissions table migration, the
 * PermissionSeeder, and the AppServiceProvider gate definitions.
 *
 * To add a new permission, add a case here and the AppServiceProvider will automatically define a gate for it
 * to use in authorization checks.
 */
enum Permission: string
{
    case CreatePositions    = 'create_positions';
    case ManageTemplates    = 'manage_templates';
    case ReviewApplications = 'review_applications';
    case ScheduleInterviews = 'schedule_interviews';
    case ManageMembers      = 'manage_members';
    case CreateInvites      = 'create_invites';

    /**
     * Returns all permission values as a plain string array, used by the
     * migration for the enum column definition and by the seeder for inserts.
     *
     * @return string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}