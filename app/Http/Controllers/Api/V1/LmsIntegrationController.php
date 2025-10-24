<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\AuthorizesRoles;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Integration\LmsShipmentSyncRequest;
use App\Http\Requests\Api\V1\Integration\LmsWebhookRequest;
use App\Models\LmsSyncJob;
use App\Models\LmsWebhookLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LmsIntegrationController extends Controller
{
    use AuthorizesRoles;

    public function sync(LmsShipmentSyncRequest $request): JsonResponse
    {
        $this->ensureAdmin($request->user());

        $job = LmsSyncJob::create([
            'shipment_id' => $request->input('shipment_id'),
            'payload' => $request->input('payload'),
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Shipment sync queued.',
            'job_id' => $job->id,
        ]);
    }

    public function webhook(LmsWebhookRequest $request): JsonResponse
    {
        LmsWebhookLog::create([
            'event_type' => $request->input('event_type'),
            'payload' => $request->all(),
            'headers' => $request->headers->all(),
            'processed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Webhook received.',
        ]);
    }

    public function health(Request $request): JsonResponse
    {
        $this->ensureAdmin($request->user());

        $pendingJobs = LmsSyncJob::where('status', 'pending')->count();
        $lastWebhook = LmsWebhookLog::latest('created_at')->first();

        return response()->json([
            'status' => 'ok',
            'pending_jobs' => $pendingJobs,
            'last_webhook_at' => $lastWebhook?->created_at?->toIso8601String(),
        ]);
    }
}

