class Env {
  Env._();
  static const String apiBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'https://a35b-197-43-74-119.ngrok-free.app/api/v1/',
  );
}
