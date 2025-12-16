<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Record;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class FileController extends Controller
{
    /**
     * Get the view prefix based on current route.
     */
    private function getViewPrefix(): string
    {
        $prefix = request()->route()->getPrefix();
        return str_contains($prefix, 'tenant') ? 'tenant' : 'admin';
    }

    /**
     * Get the route prefix based on current route.
     */
    private function getRoutePrefix(): string
    {
        $prefix = request()->route()->getPrefix();
        return str_contains($prefix, 'tenant') ? 'tenant' : 'admin';
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $files = File::where('tenant_id', $currentTenant->id)
                    ->with(['record', 'creator'])
                    ->paginate(15);

        $viewPrefix = $this->getViewPrefix();
        return view("{$viewPrefix}.files.index", compact('files'));
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

        $records = Record::where('tenant_id', $currentTenant->id)->get();
            $lastRecord = Record::where('tenant_id', $currentTenant->id)
        ->latest('id') // ممكن تستخدم latest('created_at') لو عندك العمود
        ->first();
        $viewPrefix = $this->getViewPrefix();
        return view("{$viewPrefix}.files.create", compact('records','lastRecord'));
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
            'record_id' => 'required|exists:records,id',
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        $uploadedFile = $request->file('file');

        // Store file in tenant-specific directory
        $path = $uploadedFile->store('tenants/' . $currentTenant->id . '/files', 'public');

        $file = File::create([
            'tenant_id' => $currentTenant->id,
            'record_id' => $request->record_id,
            'name' => $uploadedFile->getClientOriginalName(),
            'path' => $path,
            'type' => $uploadedFile->getClientOriginalExtension(),
            'size' => $uploadedFile->getSize(),
            'mime_type' => $uploadedFile->getMimeType(),
            'created_by' => Auth::id(),
        ]);

        $routePrefix = $this->getRoutePrefix();
        return redirect()->route("{$routePrefix}.files.index")
                        ->with('success', 'File uploaded successfully.');
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

        $file = File::where('tenant_id', $currentTenant->id)
                   ->with(['record', 'creator'])
                   ->findOrFail($id);

        $viewPrefix = $this->getViewPrefix();
        return view("{$viewPrefix}.files.show", compact('file'));
    }

    /**
     * Download the specified file.
     */
    public function download(string $id)
    {
        $currentTenant = session('tenant_context.current_tenant');
        if (!$currentTenant) {
            abort(403, 'No tenant context available.');
        }

        $file = File::where('tenant_id', $currentTenant->id)->findOrFail($id);

        if (!Storage::disk('public')->exists($file->path)) {
            abort(404, 'File not found.');
        }

        return response()->download(Storage::disk('public')->path($file->path), $file->name);
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

        $file = File::where('tenant_id', $currentTenant->id)->findOrFail($id);
        $records = Record::where('tenant_id', $currentTenant->id)->get();

        $viewPrefix = $this->getViewPrefix();
        return view("{$viewPrefix}.files.edit", compact('file', 'records'));
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

        $file = File::where('tenant_id', $currentTenant->id)->findOrFail($id);
           $lastRecord = Record::where('tenant_id', $currentTenant->id)
        ->latest('id') // ممكن تستخدم latest('created_at') لو عندك العمود
        ->first();
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $file->update([
            'record_id' => $lastRecord->id,
            'name' => $request->name,
        ]);

        $routePrefix = $this->getRoutePrefix();
        return redirect()->route("{$routePrefix}.files.index")
                        ->with('success', 'File updated successfully.');
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

        $file = File::where('tenant_id', $currentTenant->id)->findOrFail($id);

        // Delete file from storage
        if (Storage::disk('public')->exists($file->path)) {
            Storage::disk('public')->delete($file->path);
        }

        $file->delete();

        $routePrefix = $this->getRoutePrefix();
        return redirect()->route("{$routePrefix}.files.index")
                        ->with('success', 'File deleted successfully.');
    }
}
