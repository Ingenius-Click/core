<?php

namespace Ingenius\Core\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Ingenius\Core\Helpers\AuthHelper;
use Ingenius\Core\Facades\Settings;
use Ingenius\Core\Http\Requests\UpdateSettingsRequest;
use Ingenius\Core\Models\Settings as ModelsSettings;

class SettingsController extends Controller
{
    use AuthorizesRequests;

    /**
     * Settings that should be treated as images
     */
    protected array $imageSettings = [
        'store_logo',
        'store_black_white_logo',
        'store_favicon',
        'logo',
        'favicon',
        'header_image',
        'background_image'
    ];

    /**
     * Get all settings for a group.
     *
     * @param string $group
     * @return JsonResponse
     */
    public function getGroup(Request $request, string $group): JsonResponse
    {
        $user = AuthHelper::getUser();
        $this->authorizeForUser($user, 'view', ModelsSettings::class);

        $settings = Settings::getAllInGroup($group);

        // Convert image paths to URLs
        $settings = $this->convertImagePathsToUrls($settings);

        return response()->api(message: 'Settings fetched successfully', data: $settings);
    }

    /**
     * Get a specific setting.
     *
     * @param string $group
     * @param string $name
     * @return JsonResponse
     */
    public function getSetting(string $group, string $name): JsonResponse
    {
        $user = AuthHelper::getUser();
        $this->authorizeForUser($user, 'view', ModelsSettings::class);

        $value = Settings::get($group, $name);

        // Convert image path to URL if this is an image setting
        if (in_array($name, $this->imageSettings) && $value) {
            $value = $this->generateTenantAwareImageUrl($value);
        }

        return response()->json(['value' => $value]);
    }

    /**
     * Update a specific setting.
     *
     * @param UpdateSettingsRequest $request
     * @param string $group
     * @param string $name
     * @return JsonResponse
     */
    public function updateSettings(UpdateSettingsRequest $request, string $group): JsonResponse
    {
        $user = AuthHelper::getUser();
        $this->authorizeForUser($user, 'edit', ModelsSettings::class);

        $requestSettings = $request->input('settings');

        $notFoundSettings = [];

        foreach ($requestSettings as $setting) {
            $name = $setting['name'];
            $value = $setting['value'];
            $encrypt = $setting['encrypt'] ?? false;

            $settingModel = ModelsSettings::where('group', $group)->where('name', $name)->first();

            if (!$settingModel) {
                $notFoundSettings[] = $name;
                continue;
            }

            // Handle image settings
            if (in_array($name, $this->imageSettings) && $value) {
                $value = $this->handleImageSetting($name, $value, $settingModel->payload);
            }

            $settingModel->payload = $value;
            $settingModel->save();
        }


        return response()->api(message: 'Setting updated successfully', data: ['notFoundSettings' => $notFoundSettings]);
    }

    /**
     * Delete a specific setting.
     *
     * @param string $group
     * @param string $name
     * @return JsonResponse
     */
    public function deleteSetting(string $group, string $name): JsonResponse
    {
        Settings::forget($group, $name);

        return response()->json(['message' => 'Setting deleted successfully']);
    }

    /**
     * Get all available settings groups.
     *
     * @return JsonResponse
     */
    public function getGroups(): JsonResponse
    {
        $groups = Config::get('settings.groups', []);
        return response()->json($groups);
    }

    /**
     * Update settings for a specific settings class.
     *
     * @param Request $request
     * @param string $class
     * @return JsonResponse
     */
    public function updateSettingsClass(Request $request, string $class): JsonResponse
    {
        $settingsClasses = Config::get('settings.settings_classes', []);
        $className = null;

        foreach ($settingsClasses as $settingsClass) {
            $parts = explode('\\', $settingsClass);
            $shortName = end($parts);

            if (strtolower($shortName) === strtolower($class)) {
                $className = $settingsClass;
                break;
            }
        }

        if (!$className) {
            return response()->json(['message' => 'Settings class not found'], 404);
        }

        $instance = new $className();
        $data = $request->all();

        foreach ($data as $key => $value) {
            if (property_exists($instance, $key)) {
                $instance->{$key} = $value;
            }
        }

        $instance->save();

        return response()->json(['message' => 'Settings updated successfully']);
    }

    /**
     * Handle image setting by saving base64 data to file
     */
    private function handleImageSetting(string $name, string $value, ?string $oldPath = null): string
    {
        // Check if value is base64 encoded image
        if (preg_match('/^data:image\/(\w+);base64,(.+)$/', $value, $matches)) {
            $extension = $matches[1];
            $imageData = base64_decode($matches[2]);

            // Delete old image if exists
            if ($oldPath && Storage::exists($oldPath)) {
                Storage::delete($oldPath);
            }

            // Generate unique filename
            $filename = $name . '_' . uniqid() . '.' . $extension;
            $path = 'settings/images/' . $filename;

            // Save new image
            Storage::put($path, $imageData);

            return $path;
        }

        // If not base64, return as is (might be existing path or URL)
        return $value;
    }

    /**
     * Convert image paths to URLs in settings array
     */
    private function convertImagePathsToUrls(array $settings): array
    {
        foreach ($settings as $key => $value) {
            if (in_array($key, $this->imageSettings) && $value) {
                $settings[$key] = $this->generateTenantAwareImageUrl($value);
            }
        }

        return $settings;
    }

    /**
     * Generate tenant-aware URL for image
     */
    private function generateTenantAwareImageUrl(string $path): string
    {
        if (tenant()) {
            // For tenant context, use asset() which is tenant-aware
            return asset($path);
        }

        // For central app, use Storage::url()
        return Storage::url($path);
    }
}
