# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer {YOUR_AUTH_TOKEN}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

This API uses Laravel Sanctum for authentication. You can retrieve your token by logging in via the `/api/v1/login` endpoint. Include the token in the Authorization header as: `Bearer {your-token}`.
