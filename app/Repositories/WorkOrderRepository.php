<?php

namespace App\Repositories;

use App\Models\Record;
use App\Models\WorkOrder;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WorkOrderRepository extends BaseRepository
{
    protected function model(): string
    {
        return WorkOrder::class;
    }

    /**
     * Get work orders assigned to user with filters
     *
     * @param string $userId
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator|Collection
     */
    public function getAssignedToUser(string $userId, array $filters = [], int $perPage = 15)
    {
        $query = $this->model->newQuery();
        $query = $this->applyTenantScope($query);
        $query->where('assigned_to', $userId);

        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by priority
        if (isset($filters['priority'])) {
            $query->where('priority_value', $filters['priority']);
        }

        // Filter by nearby location (requires current latitude and longitude)
        if (isset($filters['latitude']) && isset($filters['longitude']) && isset($filters['radius'])) {
            $latitude = $filters['latitude'];
            $longitude = $filters['longitude'];
            $radius = $filters['radius'] ?? 10; // Default 10 km

            // Calculate distance using Haversine formula
            $query->selectRaw(
                "*, (
                    6371 * acos(
                        cos(radians(?))
                        * cos(radians(latitude))
                        * cos(radians(longitude) - radians(?))
                        + sin(radians(?))
                        * sin(radians(latitude))
                    )
                ) AS distance",
                [$latitude, $longitude, $latitude]
            )
            ->havingRaw('distance <= ?', [$radius])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('distance', 'asc');
        } else {
            // Default ordering
            if (isset($filters['sort_by'])) {
                $sortOrder = $filters['sort_order'] ?? 'asc';
                switch ($filters['sort_by']) {
                    case 'priority':
                        $query->orderBy('priority_value', $sortOrder);
                        break;
                    case 'due_date':
                        $query->orderBy('due_date', $sortOrder);
                        break;
                    case 'distance':
                        // Requires latitude/longitude
                        if (isset($filters['latitude']) && isset($filters['longitude'])) {
                            $latitude = $filters['latitude'];
                            $longitude = $filters['longitude'];
                            $query->selectRaw(
                                "*, (
                                    6371 * acos(
                                        cos(radians(?))
                                        * cos(radians(latitude))
                                        * cos(radians(longitude) - radians(?))
                                        + sin(radians(?))
                                        * sin(radians(latitude))
                                    )
                                ) AS distance",
                                [$latitude, $longitude, $latitude]
                            )
                            ->whereNotNull('latitude')
                            ->whereNotNull('longitude')
                            ->orderBy('distance', $sortOrder);
                        }
                        break;
                    default:
                        $query->orderBy('created_at', 'desc');
                }
            } else {
                $query->orderBy('created_at', 'desc');
            }
        }

        $query->addSelect([
            'records_count' => Record::query()
                ->selectRaw('count(distinct form_id)')
                ->whereColumn('records.work_order_id', 'work_orders.id')
                ->where('records.submitted_by', $userId),
        ]);
        $query->with(['project', 'assignedUser', 'forms.formFields']);

        if ($perPage > 0) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }

    /**
     * Get work order with map information
     *
     * @param string $id
     * @return WorkOrder
     */
    public function getWithMapInfo(string $id): WorkOrder
    {
        $workOrder = $this->with(['project', 'assignedUser', 'forms.formFields'])->find($id);
        return $workOrder;
    }

    /**
     * Calculate distance between two coordinates (in km)
     *
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return float
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }

    /**
     * Get estimated travel time (simple calculation)
     * In production, use Google Maps Distance Matrix API
     *
     * @param float $distanceKm
     * @param float $averageSpeedKmh
     * @return array
     */
    public function getEstimatedTime(float $distanceKm, float $averageSpeedKmh = 50): array
    {
        $hours = $distanceKm / $averageSpeedKmh;
        $minutes = round($hours * 60);

        return [
            'hours' => floor($hours),
            'minutes' => $minutes % 60,
            'total_minutes' => $minutes,
            'formatted' => $this->formatDuration($minutes),
        ];
    }

    /**
     * Format duration in minutes to human readable
     *
     * @param int $minutes
     * @return string
     */
    private function formatDuration(int $minutes): string
    {
        if ($minutes < 60) {
            return $minutes . ' min';
        }

        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($mins == 0) {
            return $hours . ' hour' . ($hours > 1 ? 's' : '');
        }

        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ' . $mins . ' min';
    }
}

