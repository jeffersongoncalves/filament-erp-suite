<?php

use Filament\Panel;
use JeffersonGoncalves\FilamentErp\Accounting\Resources\Accounts\AccountResource;
use JeffersonGoncalves\FilamentErp\Assets\Resources\Assets\AssetResource;
use JeffersonGoncalves\FilamentErp\Core\Resources\Companies\CompanyResource;
use JeffersonGoncalves\FilamentErp\Core\Resources\Companies\Pages\ListCompanies;
use JeffersonGoncalves\FilamentErp\Crm\Resources\Leads\LeadResource;
use JeffersonGoncalves\FilamentErp\Selling\Resources\Customers\CustomerResource;
use JeffersonGoncalves\FilamentErp\Stock\Resources\Items\ItemResource;
use JeffersonGoncalves\FilamentErp\Umbrella\ErpPanelPlugin;
use Livewire\Livewire;

it('boots the panel with the umbrella plugin registered', function () {
    $panel = filament()->getPanel('admin');

    expect($panel)->toBeInstanceOf(Panel::class)
        ->and($panel->getPlugin('filament-erp'))->toBeInstanceOf(ErpPanelPlugin::class);
});

it('registers resources contributed by multiple ERP modules on one panel', function () {
    $resources = filament()->getPanel('admin')->getResources();

    expect($resources)
        ->toContain(CompanyResource::class)   // core
        ->toContain(AccountResource::class)   // accounting
        ->toContain(ItemResource::class)      // stock
        ->toContain(CustomerResource::class)  // selling
        ->toContain(AssetResource::class)     // assets
        ->toContain(LeadResource::class)      // crm
        ->and(count($resources))->toBeGreaterThan(40);
});

it('skips a module whose config toggle is disabled while keeping the others', function () {
    config(['filament-erp.modules.crm' => false]);

    $panel = Panel::make()->id('toggle-test');

    ErpPanelPlugin::make()->register($panel);

    expect($panel->getResources())
        ->toContain(CompanyResource::class)
        ->not->toContain(LeadResource::class);
});

it('honours the exceptModules() fluent override', function () {
    $panel = Panel::make()->id('except-test');

    ErpPanelPlugin::make()->exceptModules(['crm'])->register($panel);

    expect($panel->getResources())
        ->toContain(CompanyResource::class)
        ->not->toContain(LeadResource::class);
});

it('serves an aggregated core resource list page', function () {
    Livewire::test(ListCompanies::class)->assertSuccessful();
});
