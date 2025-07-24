<?php

namespace Ingenius\Core\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Ingenius\Core\Models\Template;

class TemplateController extends Controller
{
    use AuthorizesRequests;

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Template::class);

        $templates = Template::all();

        return response()->api(
            message: 'Templates fetched successfully',
            data: $templates,
        );
    }
}
