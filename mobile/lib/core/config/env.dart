class Env {
  Env._();
  static const String apiBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://smartkodes.syscomdemos.com/api/v1/',
  );
}
