<?php

return [
    // Set to 0 to disable automatic deletion until an approved retention policy exists.
    'visitor_retention_days' => (int) env('VISITOR_RETENTION_DAYS', 0),
];
