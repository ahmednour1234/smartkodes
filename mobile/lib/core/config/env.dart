class Env {
  Env._();
  static const String apiBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'https://2a7b-197-43-130-86.ngrok-free.app/api/v1/',
  );
}
