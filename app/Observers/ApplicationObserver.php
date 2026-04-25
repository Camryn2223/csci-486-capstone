<?php

namespace App\Observers;

use App\Models\Application;
use App\Models\Interview;

/**
 * Observes Application model events to automatically manage related interviews.
 */
class ApplicationObserver
{
    /**
     * Handle the Application "updating" event.
     * Cancels all scheduled interviews when application status changes to
     * "no_longer_under_consideration" or "withdrawn".
     */
    public function updating(Application $application): void
    {
        // Only proceed if status is actually changing
        if (!$application->isDirty('status')) {
            return;
        }

        $oldStatus = $application->getOriginal('status');
        $newStatus = $application->getAttribute('status');

        // Check if status is changing to one that requires canceling interviews
        $cancelStatuses = ['no_longer_under_consideration', 'withdrawn'];

        if (in_array($newStatus, $cancelStatuses, true)) {
            // Only cancel if previously in an active state
            $activeStatuses = ['submitted', 'under_review', 'needs_chairman_review'];
            
            if (in_array($oldStatus, $activeStatuses, true)) {
                $this->cancelScheduledInterviews($application);
            }
        }
    }

    /**
     * Cancel all scheduled interviews for the given application.
     */
    private function cancelScheduledInterviews(Application $application): void
    {
        Interview::where('application_id', $application->id)
            ->where('status', 'scheduled')
            ->update(['status' => 'canceled']);
    }
}