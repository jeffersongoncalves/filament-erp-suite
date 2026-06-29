<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ERP Modules
    |--------------------------------------------------------------------------
    |
    | Each ERP UI module is registered on the panel by the ErpPanelPlugin only
    | when its toggle below is enabled. Set a module to false to keep its
    | resources, pages and widgets out of the panel while still shipping the
    | rest of the ecosystem.
    |
    */

    'modules' => [
        'core' => true,
        'accounting' => true,
        'stock' => true,
        'selling' => true,
        'buying' => true,
        'manufacturing' => true,
        'assets' => true,
        'subcontracting' => true,
        'crm' => true,
        'projects' => true,
        'support' => true,
        'quality' => true,
        'maintenance' => true,
    ],

];
