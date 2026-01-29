<?php

namespace Ingenius\Core\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Ingenius\Core\Http\Requests\ContactRequest;

class ContactController extends Controller
{
    use AuthorizesRequests;

    public function contact(ContactRequest $request): JsonResponse {

        $validated = $request->validated();

        event(new \Ingenius\Core\Events\ContactFormReceived(
            $validated['email'],
            $validated['name'],
            $validated['message']
        ));

        return response()->api(message: 'Contact form submitted successfully');

    }
}