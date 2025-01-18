<?php

namespace App\Traits;

use App\Models\Activity;
use App\Notifications\ProspectAssigned;
use Illuminate\Support\Facades\Auth;

trait TracksAssignmentChanges
{
    protected static function bootTracksAssignmentChanges()
    {
        static::updating(function ($model) {
            if ($model->isDirty('assigned_to')) {
                $oldAssignee = $model->getOriginal('assigned_to');
                $newAssignee = $model->assigned_to;
                
                Activity::create([
                    'description' => "Changement d'assignation",
                    'causer_type' => get_class(Auth::user()),
                    'causer_id' => Auth::id(),
                    'subject_type' => get_class($model),
                    'subject_id' => $model->id,
                    'properties' => [
                        'type' => 'assignment_change',
                        'old_value' => $oldAssignee,
                        'new_value' => $newAssignee,
                        'changed_by' => Auth::id(),
                    ],
                ]);

                // Notification au nouveau responsable
                if ($newAssignee && $model->assignedTo) {
                    $model->assignedTo->notify(new ProspectAssigned($model));
                }
            }
        });
    }
}
