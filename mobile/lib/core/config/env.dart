class Env {
  Env._();
  static const String apiBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'https://5bfe-156-203-155-96.ngrok-free.app/api/v1/',
  );
}
