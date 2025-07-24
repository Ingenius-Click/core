<?php

namespace Ingenius\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Ingenius\Core\Models\Tenant;
use Ingenius\Core\Models\Template;
use Ingenius\Core\Support\TenantInitializationManager;

class CreateTenantCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ingenius:tenant:create
                            {--id= : The ID for the tenant}
                            {--domain= : The domain for the tenant}
                            {--name= : The name of the tenant}
                            {--template= : The template identifier to use for the tenant}
                            {--no-init : Skip tenant initialization}
                            {--no-migrate : Skip running migrations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new tenant with optional initialization from registered packages';

    /**
     * Create a new command instance.
     */
    public function __construct(
        protected TenantInitializationManager $initializationManager
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Prompt for tenant ID if not provided
        $id = $this->option('id');
        if (empty($id)) {
            $id = $this->ask('Enter the tenant ID');
        }

        // Prompt for tenant name if not provided
        $name = $this->option('name');
        if (empty($name)) {
            $name = $this->ask('Enter the tenant name', $id);
        }

        // Generate or prompt for domain if not provided
        $domain = $this->option('domain');
        if (empty($domain)) {
            $defaultDomain = $id . '.' . config('tenancy.central_domains')[0];
            $domain = $this->ask('Enter the tenant domain', $defaultDomain);
        }

        // Select template for the tenant
        $template = $this->selectTemplate();

        // Validate input
        $validator = Validator::make(
            [
                'id' => $id,
                'domain' => $domain,
                'name' => $name,
            ],
            [
                'id' => ['required', 'string', 'max:255'],
                'domain' => ['required', 'string', 'max:255'],
                'name' => ['required', 'string', 'max:255'],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return 1;
        }

        // Check if tenant already exists
        if (Tenant::find($id)) {
            $this->error("Tenant with ID '{$id}' already exists.");
            return 1;
        }

        $this->info("Creating tenant '{$id}' with template '{$template->name}'...");

        try {
            // Create the tenant with the selected template
            $tenant = Tenant::create([
                'id' => $id,
                'template_id' => $template->id,
            ]);

            // Set the tenant name
            $tenant->setName($name);

            // Create domain
            $tenant->domains()->create([
                'domain' => $domain,
            ]);

            $this->info("Tenant created successfully with domain '{$domain}' using template '{$template->name}'.");

            // Check if initialization should be skipped
            if ($this->option('no-init')) {
                $this->info("Tenant initialization skipped.");
                return 0;
            }

            // Run migrations for the newly created tenant if not skipped
            if (!$this->option('no-migrate')) {
                $this->info("Running migrations for the tenant...");
                $this->call('ingenius:tenants:migrate', [
                    '--tenants' => [$id],
                    '--force' => true,
                ]);
                $this->info("Tenant migrations completed successfully.");
            } else {
                $this->info("Tenant migrations skipped.");
            }

            // Initialize the tenant with all initializers
            $this->info("Initializing tenant...");
            $this->initializationManager->initializeTenant($tenant, $this, []);
            $this->info("Tenant initialized successfully.");

            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to create tenant: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Select a template for the tenant
     *
     * @return Template
     */
    protected function selectTemplate(): Template
    {
        // Check if template was provided via option
        $templateOption = $this->option('template');

        // Get all available templates
        $templates = Template::where('active', true)->get();

        if ($templates->isEmpty()) {
            $this->error('No active templates found. Please create templates first using the install command.');
            exit(1);
        }

        // If template option was provided, try to find it
        if ($templateOption) {
            $template = $templates->firstWhere('identifier', $templateOption);
            if ($template) {
                $this->info("Using template: {$template->name}");
                return $template;
            } else {
                $this->warn("Template '{$templateOption}' not found. Please choose from available templates.");
            }
        }

        // Try to get the basic template as default
        $defaultTemplate = $templates->firstWhere('identifier', 'basic');

        if (!$defaultTemplate) {
            // If no basic template, use the first available template
            $defaultTemplate = $templates->first();
        }

        // Display available templates
        $this->info('Available templates:');
        foreach ($templates as $template) {
            $featureCount = count($template->features ?? []);
            $this->line("  - {$template->identifier}: {$template->name} ({$featureCount} features)");
            if ($template->description) {
                $this->line("    {$template->description}");
            }
        }

        // Prompt user to select template
        $templateChoices = $templates->pluck('identifier')->toArray();
        $selectedIdentifier = $this->choice(
            'Select a template for this tenant',
            $templateChoices,
            $defaultTemplate->identifier
        );

        $selectedTemplate = $templates->firstWhere('identifier', $selectedIdentifier);

        $this->info("Selected template: {$selectedTemplate->name}");

        return $selectedTemplate;
    }
}
