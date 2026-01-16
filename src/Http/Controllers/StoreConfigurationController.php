<?php

namespace Ingenius\Core\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Routing\Controller;
use Ingenius\Core\Helpers\AuthHelper;
use Ingenius\Core\Models\Settings as ModelsSettings;
use Ingenius\Core\Services\StoreConfigurationManager;
use Ingenius\Core\Settings\ContactSettings;
use Ingenius\Core\Settings\CustomizeSettings;
use Ingenius\Core\Settings\PoliciesSettings;

class StoreConfigurationController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected StoreConfigurationManager $storeConfigManager
    ) {}

    /**
     * Get store configuration for backoffice.
     *
     * @return JsonResponse
     */
    public function getStoreConfiguration(): JsonResponse
    {
        // Get base store settings from CustomizeSettings
        $customizeSettings = new CustomizeSettings();
        $customizeSettings->load();

        $contactSettings = new ContactSettings();
        $contactSettings->load();

        $policiesSettings = new PoliciesSettings();
        $policiesSettings->load();

        $baseConfig = [
            'store_name' => $customizeSettings->store_name,
            'store_logo' => generate_tenant_aware_image_url($customizeSettings->store_logo),
            'store_black_white_logo' => generate_tenant_aware_image_url($customizeSettings->store_black_white_logo),
            'store_footer_logo' => generate_tenant_aware_image_url($customizeSettings->store_footer_logo),
            'store_footer_black_white_logo' => generate_tenant_aware_image_url($customizeSettings->store_footer_black_white_logo),
            'store_email' => $contactSettings->email,
            'store_favicon' => generate_tenant_aware_image_url($customizeSettings->store_favicon),
            'store_phone' => $contactSettings->phone,
            'store_about_us' => $contactSettings->about_us,
            'store_map_iframe' => $contactSettings->location_iframe,
            'store_schedule' => $contactSettings->schedule,
            'store_social_networks' => [
                'whatsapp' => $contactSettings->whatsapp,
                'facebook' => $contactSettings->facebook,
                'instagram' => $contactSettings->instagram,
                'twitter' => $contactSettings->twitter,
                'linkedin' => $contactSettings->linkedin,
                'youtube' => $contactSettings->youtube,
                'tiktok' => $contactSettings->tiktok,
                'pinterest' => $contactSettings->pinterest,
            ],
            'server_time' => now(),
            'policies' => [
                'return_policy' => $policiesSettings->return_policy,
                'shipping_policy' => $policiesSettings->shipping_policy,
                'warranty_policy' => $policiesSettings->warranty_policy,
            ]
        ];

        // Get all registered configuration extensions from packages
        $packageConfigurations = $this->storeConfigManager->getAllValues();

        // Merge base configuration with package extensions
        $storeConfiguration = array_merge($baseConfig, $packageConfigurations);

        // Add metadata about registered packages
        $metadata = [
            'registered_packages' => $this->storeConfigManager->getAvailablePackages(),
            'total_configurations' => count($this->storeConfigManager->getAvailableConfigurations()),
        ];

        return Response::api(
            message: 'Store configuration fetched successfully',
            data: [
                'configuration' => $storeConfiguration,
                'metadata' => $metadata,
            ]
        );
    }

    /**
     * Get store configuration by package.
     *
     * @param string $package
     * @return JsonResponse
     */
    public function getConfigurationByPackage(string $package): JsonResponse
    {
        $user = AuthHelper::getUser();
        $this->authorizeForUser($user, 'view', ModelsSettings::class);

        $configurations = $this->storeConfigManager->getConfigurationsByPackage($package);

        if (empty($configurations)) {
            return Response::api(
                message: 'No configurations found for package',
                code: 404
            );
        }

        $result = [];
        foreach ($configurations as $config) {
            if ($config->isAvailable()) {
                $result[$config->getKey()] = $config->getValue();
            }
        }

        return Response::api(
            message: "Configuration for package '{$package}' fetched successfully",
            data: $result
        );
    }
}
