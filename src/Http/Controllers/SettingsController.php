<?php

namespace Ingenius\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Ingenius\Core\Facades\Settings;
use Ingenius\Core\Http\Requests\UpdateSettingsRequest;

class SettingsController extends Controller
{
    /**
     * Get all settings for a group.
     *
     * @param string $group
     * @return JsonResponse
     */
    public function getGroup(string $group): JsonResponse
    {
        $settings = Settings::getAllInGroup($group);
        return response()->json($settings);
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
        $value = Settings::get($group, $name);
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
    public function updateSetting(UpdateSettingsRequest $request, string $group, string $name): JsonResponse
    {
        $value = $request->input('value');
        $encrypt = $request->input('encrypt', false);

        Settings::set($group, $name, $value, $encrypt);

        return response()->json(['message' => 'Setting updated successfully']);
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
}
