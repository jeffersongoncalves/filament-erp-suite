<?php

namespace JeffersonGoncalves\FilamentErp\Umbrella;

use Filament\Contracts\Plugin;
use Filament\Panel;
use JeffersonGoncalves\FilamentErp\Accounting\FilamentErpAccountingPlugin;
use JeffersonGoncalves\FilamentErp\Assets\FilamentErpAssetsPlugin;
use JeffersonGoncalves\FilamentErp\Buying\FilamentErpBuyingPlugin;
use JeffersonGoncalves\FilamentErp\Core\FilamentErpCorePlugin;
use JeffersonGoncalves\FilamentErp\Crm\FilamentErpCrmPlugin;
use JeffersonGoncalves\FilamentErp\Hr\FilamentErpHrPlugin;
use JeffersonGoncalves\FilamentErp\Maintenance\FilamentErpMaintenancePlugin;
use JeffersonGoncalves\FilamentErp\Manufacturing\FilamentErpManufacturingPlugin;
use JeffersonGoncalves\FilamentErp\Projects\FilamentErpProjectsPlugin;
use JeffersonGoncalves\FilamentErp\Quality\FilamentErpQualityPlugin;
use JeffersonGoncalves\FilamentErp\Selling\FilamentErpSellingPlugin;
use JeffersonGoncalves\FilamentErp\Stock\FilamentErpStockPlugin;
use JeffersonGoncalves\FilamentErp\Subcontracting\FilamentErpSubcontractingPlugin;
use JeffersonGoncalves\FilamentErp\Support\FilamentErpSupportPlugin;

class ErpPanelPlugin implements Plugin
{
    /**
     * The ERP UI module plugins, keyed by module, in foreign-key-safe
     * registration order (core first, then the dependent modules).
     *
     * @var array<string, class-string<Plugin>>
     */
    protected const MODULE_PLUGINS = [
        'core' => FilamentErpCorePlugin::class,
        'accounting' => FilamentErpAccountingPlugin::class,
        'stock' => FilamentErpStockPlugin::class,
        'selling' => FilamentErpSellingPlugin::class,
        'buying' => FilamentErpBuyingPlugin::class,
        'manufacturing' => FilamentErpManufacturingPlugin::class,
        'assets' => FilamentErpAssetsPlugin::class,
        'subcontracting' => FilamentErpSubcontractingPlugin::class,
        'crm' => FilamentErpCrmPlugin::class,
        'projects' => FilamentErpProjectsPlugin::class,
        'support' => FilamentErpSupportPlugin::class,
        'quality' => FilamentErpQualityPlugin::class,
        'maintenance' => FilamentErpMaintenancePlugin::class,
        'hr' => FilamentErpHrPlugin::class,
    ];

    /**
     * Explicit allow-list of modules to register. When set, it overrides the
     * config toggles entirely.
     *
     * @var list<string>|null
     */
    protected ?array $only = null;

    /**
     * Modules to exclude, merged on top of the config toggles and the
     * allow-list.
     *
     * @var list<string>
     */
    protected array $except = [];

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function getId(): string
    {
        return 'filament-erp';
    }

    /**
     * Restrict the panel to the given modules only. Any module not listed is
     * skipped regardless of its config toggle.
     *
     * @param  list<string>  $modules
     */
    public function modules(array $modules): static
    {
        $this->only = $modules;

        return $this;
    }

    /**
     * Exclude the given modules from the panel.
     *
     * @param  list<string>  $modules
     */
    public function exceptModules(array $modules): static
    {
        $this->except = array_values(array_unique([...$this->except, ...$modules]));

        return $this;
    }

    public function register(Panel $panel): void
    {
        foreach (static::MODULE_PLUGINS as $module => $plugin) {
            if ($this->isModuleEnabled($module)) {
                // Each module plugin's make() simply resolves itself from the
                // container; resolve it directly so the interface type is kept.
                $panel->plugin(app($plugin));
            }
        }
    }

    public function boot(Panel $panel): void
    {
        //
    }

    protected function isModuleEnabled(string $module): bool
    {
        if (in_array($module, $this->except, true)) {
            return false;
        }

        if ($this->only !== null) {
            return in_array($module, $this->only, true);
        }

        return (bool) config("filament-erp.modules.{$module}", true);
    }
}
