<?php

namespace Ingenius\Core\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Ingenius\Core\Interfaces\FeatureInterface;
use Ingenius\Core\Models\Template;
use Ingenius\Core\Services\FeatureManager;

class FeatureController extends Controller {

    use AuthorizesRequests;

    public function index(): JsonResponse {

        $this->authorize('viewAny', Template::class);

        $featureManager = app(FeatureManager::class);

        $features = array_values(array_map(function(FeatureInterface $feature){
            return [
                'identifier' => $feature->getIdentifier(),
                'name' => $feature->getName(),
                'group' => $feature->getGroup(),
                'is_basic' => $feature->isBasic(),
            ];
        }, $featureManager->getFeatures()));

        return Response::api(
            message: 'Features fetched successfully',
            data: $features,
        );
    }

}