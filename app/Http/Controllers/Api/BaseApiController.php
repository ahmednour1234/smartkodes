<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use Illuminate\Http\JsonResponse;

abstract class BaseApiController extends Controller
{
    protected ApiResponseService $response;

    public function __construct(ApiResponseService $response)
    {
        $this->response = $response;
    }

    /**
     * Return a successful JSON response
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    protected function successResponse($data = null, string $message = 'Operation successful', int $code = 200): JsonResponse
    {
        return $this->response->success($data, $message, $code);
    }

    /**
     * Return a created JSON response (201)
     *
     * @param mixed $data
     * @param string $message
     * @return JsonResponse
     */
    protected function createdResponse($data = null, string $message = 'Resource created successfully'): JsonResponse
    {
        return $this->response->created($data, $message);
    }

    /**
     * Return a paginated JSON response
     *
     * @param mixed $paginator
     * @param string $message
     * @return JsonResponse
     */
    protected function paginatedResponse($paginator, string $message = 'Data retrieved successfully'): JsonResponse
    {
        return $this->response->paginated($paginator, $message);
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
    protected function errorResponse(string $message, int $code = 400, array $errors = [], ?string $errorCode = null): JsonResponse
    {
        return $this->response->error($message, $code, $errors, $errorCode);
    }

    /**
     * Return a not found JSON response (404)
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return $this->response->notFound($message);
    }

    /**
     * Return an unauthorized JSON response (401)
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->response->unauthorized($message);
    }

    /**
     * Return a forbidden JSON response (403)
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function forbiddenResponse(string $message = 'Forbidden'): JsonResponse
    {
        return $this->response->forbidden($message);
    }

    /**
     * Return a validation error JSON response (422)
     *
     * @param array $errors
     * @param string $message
     * @return JsonResponse
     */
    protected function validationErrorResponse(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->response->validationError($errors, $message);
    }

    /**
     * Return a server error JSON response (500)
     *
     * @param string $message
     * @param string|null $errorCode
     * @return JsonResponse
     */
    protected function serverErrorResponse(string $message = 'Internal server error', ?string $errorCode = 'SERVER_ERROR'): JsonResponse
    {
        return $this->response->serverError($message, $errorCode);
    }
}

