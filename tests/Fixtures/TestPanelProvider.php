<?php

namespace JeffersonGoncalves\FilamentErp\Umbrella\Tests\Fixtures;

use Filament\Panel;
use Filament\PanelProvider;
use JeffersonGoncalves\FilamentErp\Umbrella\ErpPanelPlugin;

class TestPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->plugin(ErpPanelPlugin::make());
    }
}
