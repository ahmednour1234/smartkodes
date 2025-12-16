<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiResponseService
{
    /**
     * Return a successful JSON response
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    public function success($data = null, string $message = 'Operation successful', int $code = Response::HTTP_OK): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        $response['meta'] = [
            'timestamp' => now()->toIso8601String(),
        ];

        return response()->json($response, $code);
    }

    /**
     * Return a created JSON response (201)
     *
     * @param mixed $data
     * @param string $message
     * @return JsonResponse
     */
    public function created($data = null, string $message = 'Resource created successfully'): JsonResponse
    {
        return $this->success($data, $message, Response::HTTP_CREATED);
    }

    /**
     * Return a paginated JSON response
     *
     * @param LengthAwarePaginator|ResourceCollection $paginator
     * @param string $message
     * @return JsonResponse
     */
    public function paginated($paginator, string $message = 'Data retrieved successfully'): JsonResponse
    {
        // Handle ResourceCollection pagination
        if ($paginator instanceof ResourceCollection) {
            $resource = $paginator->resource;
            if ($resource instanceof LengthAwarePaginator) {
                $paginator = $resource;
            }
        }

        // Ensure we have a LengthAwarePaginator
        if (!$paginator instanceof LengthAwarePaginator) {
            return $this->success($paginator, $message);
        }

        $data = $paginator->items();

        // If items are resources, resolve them
        if (!empty($data) && is_object($data[0]) && method_exists($data[0], 'resolve')) {
            $data = collect($data)->map(function ($item) {
                return $item->resolve();
            })->toArray();
        }

        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => [
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
                'timestamp' => now()->toIso8601String(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ];

        return response()->json($response, Response::HTTP_OK);
    }

    /**
     * Return an error JSON response
     *
     * @param string $message
     * @param int $code
     * @param array $errors
     * @param string|null $errorCode
     * @return JsonResponse
     */
    public function error(string $message, int $code = Response::HTTP_BAD_REQUEST, array $errors = [], ?string $errorCode = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        $response['meta'] = [
            'timestamp' => now()->toIso8601String(),
        ];

        if ($errorCode) {
            $response['meta']['error_code'] = $errorCode;
        }

        return response()->json($response, $code);
    }

    /**
     * Return a not found JSON response (404)
     *
     * @param string $message
     * @return JsonResponse
     */
    public function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->error($message, Response::HTTP_NOT_FOUND, [], 'NOT_FOUND');
    }

    /**
     * Return an unauthorized JSON response (401)
     *
     * @param string $message
     * @return JsonResponse
     */
    public function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->error($message, Response::HTTP_UNAUTHORIZED, [], 'UNAUTHORIZED');
    }

    /**
     * Return a forbidden JSON response (403)
     *
     * @param string $message
     * @return JsonResponse
     */
    public function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, Response::HTTP_FORBIDDEN, [], 'FORBIDDEN');
    }

    /**
     * Return a validation error JSON response (422)
     *
     * @param array $errors
     * @param string $message
     * @return JsonResponse
     */
    public function validationError(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->error($message, Response::HTTP_UNPROCESSABLE_ENTITY, $errors, 'VALIDATION_ERROR');
    }

    /**
     * Return a server error JSON response (500)
     *
     * @param string $message
     * @param string|null $errorCode
     * @return JsonResponse
     */
    public function serverError(string $message = 'Internal server error', ?string $errorCode = 'SERVER_ERROR'): JsonResponse
    {
        return $this->error($message, Response::HTTP_INTERNAL_SERVER_ERROR, [], $errorCode);
    }

    /**
     * Return a no content response (204)
     *
     * @return JsonResponse
     */
    public function noContent(): JsonResponse
    {
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}

