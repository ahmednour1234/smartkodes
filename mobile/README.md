# SmartKodes Mobile App

Flutter app for work orders, forms, and notifications. Uses Laravel API at `/api/v1`.

## Setup

1. Install [Flutter](https://docs.flutter.dev/get-started/install).
2. From repo root: `cd mobile`
3. Generate platform folders (if missing): `flutter create --org com.smartkodes .`
4. Install dependencies: `flutter pub get`
5. Run: `flutter run`

## Env

Set API base URL (e.g. in `lib/core/config/env.dart` or via `--dart-define=API_BASE_URL=https://...`).
