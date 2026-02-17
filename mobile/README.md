# SmartSite Mobile App

Flutter app for work orders, forms, and notifications. Uses Laravel API at `/api/v1`.

## Setup

1. Install [Flutter](https://docs.flutter.dev/get-started/install).
2. From repo root: `cd mobile`
3. Generate platform folders (if missing): `flutter create --org com.smartsite .`
4. Install dependencies: `flutter pub get`
5. Run: `flutter run` (or `flutter run -d chrome` for web).
   For Android/iOS, add `firebase_core` and `firebase_messaging` to pubspec for FCM.

## Env

Set API base URL (e.g. in `lib/core/config/env.dart` or via `--dart-define=API_BASE_URL=https://...`).
