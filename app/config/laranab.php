<?php

return [
    'newznab_apis' => array_filter(
        array_map('trim', explode(',', env('NEWZNAB_APIS', '')))
    ),
];
