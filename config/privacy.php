<?php

return [
    // Set to 0 to disable automatic deletion until an approved retention policy exists.
    'visitor_retention_days' => (int) env('VISITOR_RETENTION_DAYS', 0),

    // Production must either define a finite retention period or explicitly
    // record that indefinite retention has been approved by the data owner.
    'indefinite_retention_accepted' => (bool) env('VISITOR_INDEFINITE_RETENTION_ACCEPTED', false),
];
