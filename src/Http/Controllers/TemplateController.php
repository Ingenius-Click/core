<?php

namespace Ingenius\Core\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Ingenius\Core\Actions\UpdateTemplateAction;
use Ingenius\Core\Http\Requests\UpdateTemplateRequest;
use Ingenius\Core\Models\Template;
use Ingenius\Core\Resources\TemplateResource;

class TemplateController extends Controller
{
    use AuthorizesRequests;

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Template::class);

        $templates = Template::all()->map(function (Template $template) {
            return new TemplateResource($template);
        });

        return response()->api(
            message: 'Templates fetched successfully',
            data: $templates,
        );
    }

    public function update(UpdateTemplateRequest $request, string $template, UpdateTemplateAction $action): JsonResponse
    {
        $template = Template::where('identifier', $template)->firstOrFail();

        $this->authorize('update', $template);

        $template = $action->handle($template, $request->validated());

        return response()->api(
            message: 'Template updated successfully',
            data: $template,
        );
    }

    public function getStyles(string $template): JsonResponse
    {
        $this->authorize('viewAny', Template::class);

        $template = Template::where('identifier', $template)->firstOrFail();

        return response()->api(
            message: 'Template styles fetched successfully',
            data: $template->styles_vars,
        );
    }
}
