<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class WebhookController extends Controller
{
    /**
     * Available webhook events.
     */
    private $availableEvents = [
        'form.created',
        'form.updated',
        'form.deleted',
        'work_order.created',
        'work_order.updated',
        'work_order.completed',
        'record.created',
        'record.submitted',
        'record.approved',
        'user.created',
        'user.updated',
        'project.created',
        'project.updated',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $webhooks = Webhook::where('tenant_id', $currentTenant->id)
                          ->with('creator')
                          ->orderBy('created_at', 'desc')
                          ->paginate(15);

        return view('admin.webhooks.index', compact('webhooks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        return view('admin.webhooks.create', [
            'availableEvents' => $this->availableEvents
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'events' => 'required|array|min:1',
            'events.*' => 'string',
            'status' => 'required|integer|in:0,1',
        ]);

        // Validate that all events are valid
        foreach ($request->events as $event) {
            if (!in_array($event, $this->availableEvents)) {
                return back()->withErrors(['events' => 'Invalid event: ' . $event]);
            }
        }

        $webhook = Webhook::create([
            'tenant_id' => $currentTenant->id,
            'name' => $request->name,
            'url' => $request->url,
            'events' => $request->events,
            'secret' => Str::random(32),
            'status' => $request->status,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('admin.webhooks.index')
                        ->with('success', 'Webhook created successfully. Secret: ' . $webhook->secret);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $webhook = Webhook::where('tenant_id', $currentTenant->id)
                         ->with('creator')
                         ->findOrFail($id);

        return view('admin.webhooks.show', compact('webhook'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $webhook = Webhook::where('tenant_id', $currentTenant->id)->findOrFail($id);

        return view('admin.webhooks.edit', [
            'webhook' => $webhook,
            'availableEvents' => $this->availableEvents
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $webhook = Webhook::where('tenant_id', $currentTenant->id)->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'events' => 'required|array|min:1',
            'events.*' => 'string',
            'status' => 'required|integer|in:0,1',
        ]);

        // Validate that all events are valid
        foreach ($request->events as $event) {
            if (!in_array($event, $this->availableEvents)) {
                return back()->withErrors(['events' => 'Invalid event: ' . $event]);
            }
        }

        $webhook->update([
            'name' => $request->name,
            'url' => $request->url,
            'events' => $request->events,
            'status' => $request->status,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('admin.webhooks.index')
                        ->with('success', 'Webhook updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $webhook = Webhook::where('tenant_id', $currentTenant->id)->findOrFail($id);
        $webhook->delete();

        return redirect()->route('admin.webhooks.index')
                        ->with('success', 'Webhook deleted successfully.');
    }

    /**
     * Test webhook.
     */
    public function test(string $id)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $webhook = Webhook::where('tenant_id', $currentTenant->id)->findOrFail($id);

        // Send test payload
        $payload = [
            'event' => 'webhook.test',
            'timestamp' => now()->toISOString(),
            'tenant_id' => $currentTenant->id,
            'data' => [
                'message' => 'This is a test webhook from Smart Kodes',
                'test_id' => Str::random(10)
            ]
        ];

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Webhook-Signature' => hash_hmac('sha256', json_encode($payload), $webhook->secret),
                    'User-Agent' => 'SmartKodes-Webhook/1.0'
                ])
                ->post($webhook->url, $payload);

            $webhook->markAsTriggered();

            return redirect()->back()->with('success', 'Test webhook sent successfully. Response: ' . $response->status());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to send test webhook: ' . $e->getMessage());
        }
    }
}
