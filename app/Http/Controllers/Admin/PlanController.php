<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::withCount('subscriptions')->latest()->paginate(15);
        return view('admin.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.plans.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:plans,slug',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'features' => 'nullable|array',
            'status' => 'required|boolean',
        ]);

        // Convert features array to proper format
        if (isset($validated['features']) && is_array($validated['features'])) {
            $features = [];
            foreach ($validated['features'] as $feature) {
                if (!empty($feature['key']) && !empty($feature['value'])) {
                    $features[$feature['key']] = $feature['value'];
                }
            }
            $validated['features'] = $features;
        }

        $plan = Plan::create($validated);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan created successfully.');
    }

    public function show(Plan $plan)
    {
        $plan->load(['subscriptions' => function ($query) {
            $query->with('tenant')->latest()->limit(10);
        }]);

        return view('admin.plans.show', compact('plan'));
    }

    public function edit(Plan $plan)
    {
        return view('admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:plans,slug,' . $plan->id,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'features' => 'nullable|array',
            'status' => 'required|boolean',
        ]);

        // Convert features array to proper format
        if (isset($validated['features']) && is_array($validated['features'])) {
            $features = [];
            foreach ($validated['features'] as $feature) {
                if (!empty($feature['key']) && !empty($feature['value'])) {
                    $features[$feature['key']] = $feature['value'];
                }
            }
            $validated['features'] = $features;
        }

        $plan->update($validated);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan updated successfully.');
    }

    public function destroy(Plan $plan)
    {
        if ($plan->subscriptions()->count() > 0) {
            return redirect()->route('admin.plans.index')
                ->with('error', 'Cannot delete plan with active subscriptions.');
        }

        $plan->delete();

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan deleted successfully.');
    }
}
