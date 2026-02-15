<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends BaseApiController
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $query = Notification::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc');
            if ($request->boolean('unread_only')) {
                $query->whereNull('read_at');
            }
            $notifications = $query->paginate($perPage);
            $items = $notifications->getCollection()->map(fn ($n) => [
                'id' => (string) $n->id,
                'type' => (string) ($n->type ?? ''),
                'title' => (string) ($n->title ?? ''),
                'message' => (string) ($n->message ?? ''),
                'data' => $n->data,
                'action_url' => $n->action_url ? (string) $n->action_url : null,
                'read_at' => $n->read_at?->toIso8601String(),
                'created_at' => $n->created_at->toIso8601String(),
            ])->values();
            $notifications->setCollection($items);
            return $this->paginatedResponse($notifications, 'Notifications retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve notifications: ' . $e->getMessage());
        }
    }

    public function markAsRead(string $id)
    {
        try {
            $n = Notification::where('user_id', Auth::id())->findOrFail($id);
            $n->markAsRead();
            return $this->successResponse(null, 'Notification marked as read');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Notification not found');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update notification: ' . $e->getMessage());
        }
    }
}
