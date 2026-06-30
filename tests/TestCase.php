<?php

namespace JeffersonGoncalves\FilamentErp\Umbrella\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Composer\InstalledVersions;
use Filament\Actions\ActionsServiceProvider;
use Filament\Facades\Filament;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Schemas\SchemasServiceProvider;
use Filament\Support\Livewire\Partials\DataStoreOverride;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use JeffersonGoncalves\Erp\Accounting\ErpAccountingServiceProvider;
use JeffersonGoncalves\Erp\Assets\ErpAssetsServiceProvider;
use JeffersonGoncalves\Erp\Buying\ErpBuyingServiceProvider;
use JeffersonGoncalves\Erp\Core\ErpCoreServiceProvider;
use JeffersonGoncalves\Erp\Crm\ErpCrmServiceProvider;
use JeffersonGoncalves\Erp\Hr\ErpHrServiceProvider;
use JeffersonGoncalves\Erp\Maintenance\ErpMaintenanceServiceProvider;
use JeffersonGoncalves\Erp\Manufacturing\ErpManufacturingServiceProvider;
use JeffersonGoncalves\Erp\Projects\ErpProjectsServiceProvider;
use JeffersonGoncalves\Erp\Quality\ErpQualityServiceProvider;
use JeffersonGoncalves\Erp\Selling\ErpSellingServiceProvider;
use JeffersonGoncalves\Erp\Stock\ErpStockServiceProvider;
use JeffersonGoncalves\Erp\Subcontracting\ErpSubcontractingServiceProvider;
use JeffersonGoncalves\Erp\Support\ErpSupportServiceProvider;
use JeffersonGoncalves\FilamentErp\Accounting\FilamentErpAccountingServiceProvider;
use JeffersonGoncalves\FilamentErp\Assets\FilamentErpAssetsServiceProvider;
use JeffersonGoncalves\FilamentErp\Buying\FilamentErpBuyingServiceProvider;
use JeffersonGoncalves\FilamentErp\Core\FilamentErpCoreServiceProvider;
use JeffersonGoncalves\FilamentErp\Crm\FilamentErpCrmServiceProvider;
use JeffersonGoncalves\FilamentErp\Hr\FilamentErpHrServiceProvider;
use JeffersonGoncalves\FilamentErp\Maintenance\FilamentErpMaintenanceServiceProvider;
use JeffersonGoncalves\FilamentErp\Manufacturing\FilamentErpManufacturingServiceProvider;
use JeffersonGoncalves\FilamentErp\Projects\FilamentErpProjectsServiceProvider;
use JeffersonGoncalves\FilamentErp\Quality\FilamentErpQualityServiceProvider;
use JeffersonGoncalves\FilamentErp\Selling\FilamentErpSellingServiceProvider;
use JeffersonGoncalves\FilamentErp\Stock\FilamentErpStockServiceProvider;
use JeffersonGoncalves\FilamentErp\Subcontracting\FilamentErpSubcontractingServiceProvider;
use JeffersonGoncalves\FilamentErp\Support\FilamentErpSupportServiceProvider;
use JeffersonGoncalves\FilamentErp\Umbrella\FilamentErpServiceProvider;
use JeffersonGoncalves\FilamentErp\Umbrella\Tests\Fixtures\TestPanelProvider;
use JeffersonGoncalves\FilamentErp\Umbrella\Tests\Fixtures\TestUser;
use Livewire\LivewireServiceProvider;
use Livewire\Mechanisms\DataStore;
use Orchestra\Testbench\TestCase as BaseTestCase;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * The ERP modules in global foreign-key-safe order. Each entry maps the
     * module key to its laravel-erp-<module> vendor package name and the
     * erp-<module> config key.
     *
     * @var array<string, array{package: string, config: string}>
     */
    protected array $modules = [
        'core' => ['package' => 'laravel-erp-core', 'config' => 'erp-core'],
        'accounting' => ['package' => 'laravel-erp-accounting', 'config' => 'erp-accounting'],
        'stock' => ['package' => 'laravel-erp-stock', 'config' => 'erp-stock'],
        'buying' => ['package' => 'laravel-erp-buying', 'config' => 'erp-buying'],
        'manufacturing' => ['package' => 'laravel-erp-manufacturing', 'config' => 'erp-manufacturing'],
        'assets' => ['package' => 'laravel-erp-assets', 'config' => 'erp-assets'],
        'selling' => ['package' => 'laravel-erp-selling', 'config' => 'erp-selling'],
        'subcontracting' => ['package' => 'laravel-erp-subcontracting', 'config' => 'erp-subcontracting'],
        'crm' => ['package' => 'laravel-erp-crm', 'config' => 'erp-crm'],
        'projects' => ['package' => 'laravel-erp-projects', 'config' => 'erp-projects'],
        'support' => ['package' => 'laravel-erp-support', 'config' => 'erp-support'],
        'quality' => ['package' => 'laravel-erp-quality', 'config' => 'erp-quality'],
        'maintenance' => ['package' => 'laravel-erp-maintenance', 'config' => 'erp-maintenance'],
        'hr' => ['package' => 'laravel-erp-hr', 'config' => 'erp-hr'],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        // Filament's SupportServiceProvider binds Livewire's DataStore to a
        // transient DataStoreOverride, which loses its WeakMap state between
        // resolutions during a single Livewire test render. Re-bind it as a
        // shared singleton so component state (e.g. the error bag) persists.
        $this->app->singleton(DataStore::class, DataStoreOverride::class);

        // The domain factories ship in the vendored packages; resolve them by
        // basename across every ERP package, leaf modules first.
        Factory::guessFactoryNamesUsing(function (string $modelName): string {
            $basename = class_basename($modelName);

            $packages = [
                'Maintenance', 'Quality', 'Support', 'Projects', 'Crm', 'Subcontracting',
                'Selling', 'Assets', 'Manufacturing', 'Buying', 'Stock', 'Accounting', 'Core',
            ];

            foreach ($packages as $package) {
                $factory = "JeffersonGoncalves\\Erp\\{$package}\\Database\\Factories\\{$basename}Factory";

                if (class_exists($factory)) {
                    return $factory;
                }
            }

            return "JeffersonGoncalves\\Erp\\Core\\Database\\Factories\\{$basename}Factory";
        });

        Filament::setCurrentPanel(Filament::getDefaultPanel());

        $this->withoutVite();

        $this->actingAs(TestUser::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
        ]));
    }

    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            BladeIconsServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeCaptureDirectiveServiceProvider::class,
            SupportServiceProvider::class,
            FilamentServiceProvider::class,
            FormsServiceProvider::class,
            SchemasServiceProvider::class,
            TablesServiceProvider::class,
            ActionsServiceProvider::class,
            InfolistsServiceProvider::class,
            NotificationsServiceProvider::class,
            WidgetsServiceProvider::class,

            // Domain (laravel-erp-*) service providers, dependency order.
            ErpCoreServiceProvider::class,
            ErpAccountingServiceProvider::class,
            ErpStockServiceProvider::class,
            ErpBuyingServiceProvider::class,
            ErpManufacturingServiceProvider::class,
            ErpAssetsServiceProvider::class,
            ErpSellingServiceProvider::class,
            ErpSubcontractingServiceProvider::class,
            ErpCrmServiceProvider::class,
            ErpProjectsServiceProvider::class,
            ErpSupportServiceProvider::class,
            ErpQualityServiceProvider::class,
            ErpMaintenanceServiceProvider::class,
            ErpHrServiceProvider::class,

            // UI (filament-erp-*) service providers.
            FilamentErpCoreServiceProvider::class,
            FilamentErpAccountingServiceProvider::class,
            FilamentErpStockServiceProvider::class,
            FilamentErpBuyingServiceProvider::class,
            FilamentErpManufacturingServiceProvider::class,
            FilamentErpAssetsServiceProvider::class,
            FilamentErpSellingServiceProvider::class,
            FilamentErpSubcontractingServiceProvider::class,
            FilamentErpCrmServiceProvider::class,
            FilamentErpProjectsServiceProvider::class,
            FilamentErpSupportServiceProvider::class,
            FilamentErpQualityServiceProvider::class,
            FilamentErpMaintenanceServiceProvider::class,
            FilamentErpHrServiceProvider::class,

            // Umbrella provider then the panel that registers ErpPanelPlugin.
            FilamentErpServiceProvider::class,
            TestPanelProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('auth.providers.users.model', TestUser::class);

        foreach ($this->modules as $module) {
            $config = InstalledVersions::getInstallPath("jeffersongoncalves/{$module['package']}")."/config/{$module['config']}.php";

            if (file_exists($config)) {
                $app['config']->set($module['config'], require $config);
            }
        }
    }

    protected function defineDatabaseMigrations(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->default('');
            $table->rememberToken();
        });

        $tempPath = sys_get_temp_dir().'/filament-erp-umbrella-migrations';

        if (is_dir($tempPath)) {
            array_map('unlink', (array) glob($tempPath.'/*.php'));
        } else {
            mkdir($tempPath, 0755, true);
        }

        // loadMigrationsFrom sorts by filename, so each stub is copied with a
        // numeric prefix that preserves dependency order across all 13 domain
        // packages in global foreign-key-safe order.
        $index = 0;

        foreach ($this->migrations() as $module => $names) {
            $path = InstalledVersions::getInstallPath("jeffersongoncalves/{$this->modules[$module]['package']}").'/database/migrations';

            foreach ($names as $name) {
                $stub = $path.'/'.$name.'.php.stub';

                if (file_exists($stub)) {
                    copy($stub, sprintf('%s/%04d_%s.php', $tempPath, $index, $name));
                }

                $index++;
            }
        }

        $this->loadMigrationsFrom($tempPath);
    }

    /**
     * The migration stub basenames per module, in global foreign-key-safe
     * order (core, accounting, stock, buying, manufacturing, assets, selling,
     * subcontracting, crm, projects, support, quality, maintenance).
     *
     * @return array<string, list<string>>
     */
    protected function migrations(): array
    {
        return [
            'core' => [
                'create_erp_companies_table',
                'create_erp_currencies_table',
                'create_erp_currency_exchanges_table',
                'create_erp_uoms_table',
                'create_erp_uom_conversions_table',
                'create_erp_fiscal_years_table',
                'create_erp_departments_table',
                'create_erp_designations_table',
                'create_erp_brands_table',
                'create_erp_terms_and_conditions_table',
                'create_erp_addresses_table',
                'create_erp_contacts_table',
                'create_erp_naming_series_table',
            ],
            'accounting' => [
                'create_erp_accounts_table',
                'create_erp_cost_centers_table',
                'create_erp_payment_terms_table',
                'create_erp_modes_of_payment_table',
                'create_erp_tax_templates_table',
                'create_erp_tax_template_taxes_table',
                'create_erp_banks_table',
                'create_erp_bank_accounts_table',
                'create_erp_budgets_table',
                'create_erp_budget_accounts_table',
                'create_erp_gl_entries_table',
                'create_erp_journal_entries_table',
                'create_erp_journal_entry_accounts_table',
                'create_erp_payment_entries_table',
                'create_erp_sales_invoices_table',
                'create_erp_sales_invoice_items_table',
                'create_erp_sales_invoice_taxes_table',
                'create_erp_purchase_invoices_table',
                'create_erp_purchase_invoice_items_table',
                'create_erp_purchase_invoice_taxes_table',
                'create_erp_period_closing_vouchers_table',
                'create_erp_bank_transactions_table',
            ],
            'stock' => [
                'create_erp_warehouses_table',
                'create_erp_items_table',
                'create_erp_price_lists_table',
                'create_erp_item_prices_table',
                'create_erp_batches_table',
                'create_erp_serial_nos_table',
                'create_erp_stock_ledger_entries_table',
                'create_erp_bins_table',
                'create_erp_stock_entries_table',
                'create_erp_stock_entry_details_table',
                'create_erp_material_requests_table',
                'create_erp_material_request_items_table',
                'create_erp_delivery_notes_table',
                'create_erp_delivery_note_items_table',
                'create_erp_purchase_receipts_table',
                'create_erp_purchase_receipt_items_table',
                'create_erp_stock_reconciliations_table',
                'create_erp_stock_reconciliation_items_table',
            ],
            'buying' => [
                'create_erp_supplier_groups_table',
                'create_erp_suppliers_table',
                'create_erp_buying_settings_table',
                'create_erp_request_for_quotations_table',
                'create_erp_request_for_quotation_items_table',
                'create_erp_request_for_quotation_suppliers_table',
                'create_erp_supplier_quotations_table',
                'create_erp_supplier_quotation_items_table',
                'create_erp_purchase_orders_table',
                'create_erp_purchase_order_items_table',
            ],
            'manufacturing' => [
                'create_erp_workstations_table',
                'create_erp_operations_table',
                'create_erp_boms_table',
                'create_erp_bom_items_table',
                'create_erp_bom_operations_table',
                'create_erp_routings_table',
                'create_erp_routing_operations_table',
                'create_erp_work_orders_table',
                'create_erp_work_order_items_table',
                'create_erp_job_cards_table',
                'create_erp_job_card_time_logs_table',
            ],
            'assets' => [
                'create_erp_asset_categories_table',
                'create_erp_assets_table',
                'create_erp_asset_depreciation_schedules_table',
                'create_erp_asset_movements_table',
                'create_erp_asset_repairs_table',
            ],
            'selling' => [
                'create_erp_customer_groups_table',
                'create_erp_customers_table',
                'create_erp_sales_partners_table',
                'create_erp_product_bundles_table',
                'create_erp_product_bundle_items_table',
                'create_erp_quotations_table',
                'create_erp_quotation_items_table',
                'create_erp_sales_orders_table',
                'create_erp_sales_order_items_table',
            ],
            'subcontracting' => [
                'create_erp_subcontracting_boms_table',
                'create_erp_subcontracting_bom_items_table',
                'create_erp_subcontracting_orders_table',
                'create_erp_subcontracting_order_items_table',
                'create_erp_subcontracting_order_supplied_items_table',
                'create_erp_subcontracting_receipts_table',
                'create_erp_subcontracting_receipt_items_table',
                'create_erp_subcontracting_receipt_supplied_items_table',
            ],
            'crm' => [
                'create_erp_lead_sources_table',
                'create_erp_campaigns_table',
                'create_erp_contracts_table',
                'create_erp_leads_table',
                'create_erp_opportunities_table',
                'create_erp_opportunity_items_table',
                'create_erp_appointments_table',
            ],
            'projects' => [
                'create_erp_activity_types_table',
                'create_erp_projects_table',
                'create_erp_tasks_table',
                'create_erp_timesheets_table',
                'create_erp_timesheet_details_table',
            ],
            'support' => [
                'create_erp_issue_types_table',
                'create_erp_service_level_agreements_table',
                'create_erp_service_level_priorities_table',
                'create_erp_issues_table',
                'create_erp_warranty_claims_table',
            ],
            'quality' => [
                'create_erp_quality_goals_table',
                'create_erp_quality_goal_objectives_table',
                'create_erp_quality_procedures_table',
                'create_erp_quality_procedure_processes_table',
                'create_erp_quality_inspection_templates_table',
                'create_erp_quality_inspection_template_parameters_table',
                'create_erp_quality_inspections_table',
                'create_erp_quality_inspection_readings_table',
                'create_erp_non_conformances_table',
                'create_erp_quality_actions_table',
                'create_erp_quality_action_resolutions_table',
                'create_erp_quality_reviews_table',
                'create_erp_quality_review_objectives_table',
            ],
            'maintenance' => [
                'create_erp_maintenance_schedules_table',
                'create_erp_maintenance_schedule_items_table',
                'create_erp_maintenance_schedule_details_table',
                'create_erp_maintenance_visits_table',
                'create_erp_maintenance_visit_purposes_table',
            ],
            'hr' => [
                'create_erp_leave_types_table',
                'create_erp_holiday_lists_table',
                'create_erp_holidays_table',
                'create_erp_salary_components_table',
                'create_erp_employees_table',
                'create_erp_salary_structures_table',
                'create_erp_salary_structure_components_table',
                'create_erp_salary_structure_assignments_table',
                'create_erp_attendances_table',
                'create_erp_leave_applications_table',
                'create_erp_salary_slips_table',
                'create_erp_salary_slip_components_table',
                'create_erp_payroll_entries_table',
            ],
        ];
    }
}
