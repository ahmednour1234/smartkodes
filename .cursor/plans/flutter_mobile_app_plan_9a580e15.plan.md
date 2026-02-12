---
name: Flutter Mobile App Plan
overview: Create a new Flutter mobile app in the smartkodes repo using MVVM, state management, and FCM, consuming the existing Laravel API for auth, work orders, forms, and notifications, with offline form filling and sync.
todos: []
isProject: false
---

# Flutter SmartKodes Mobile App – Implementation Plan

## Context

- **Backend**: Laravel API at `{{base_url}}/api/v1` (see [routes/api.php](routes/api.php)). JWT auth; response envelope: `{ success, message, data?, meta }`; paginated: `data` array + `meta.pagination` (current_page, per_page, total, last_page, from, to) and `links` (first, last, prev, next).
- **Flutter app**: New project; assume created inside this repo (e.g. `mobile/` or `smartkodes_app/`).

---

## 1. Project setup and dependencies

- Create Flutter project (latest stable) in a dedicated folder (e.g. `mobile/`).
- **pubspec.yaml** (main packages):
  - **State management**: `flutter_riverpod` (or `provider` / `bloc` per team choice; Riverpod fits MVVM and testability).
  - **Networking**: `dio` (base URL, JWT interceptors, multipart for form submit).
  - **Local storage / offline**: `drift` (SQLite) or `hive` + `path_provider` for pending submissions and cached work orders/forms.
  - **Auth storage**: `flutter_secure_storage` for token and passcode hash/pin.
  - **Maps**: `google_maps_flutter` + `geolocator` (list by distance, show destination, ETA).
  - **Links**: `url_launcher` to open Google Maps app (directions URL from API).
  - **Push**: `firebase_core`, `firebase_messaging` (FCM); store FCM token and send to backend when backend supports it.
  - **PIN/Passcode**: `local_auth` (optional) and a simple PIN UI; passcode set/verify via API (`/users/set-passcode`, `/users/verify-passcode`).
- **Configuration**: Environment-based `base_url` (e.g. `.env` or build flavors) for API.

---

## 2. Architecture (MVVM)

- **Model**: Plain Dart classes matching API responses (e.g. `User`, `WorkOrder`, `Form`, `FormField`, `Notification`, pagination wrapper). Use `fromJson`/`toJson` and keep API envelope parsing in a single place (e.g. `ApiResponse<T>` with `data`, `message`, `meta`).
- **View**: Flutter screens/widgets only; no business logic.
- **ViewModel**: Riverpod providers (or ChangeNotifier/Bloc) that:
  - Call repository/service layer.
  - Expose state (e.g. `AsyncValue<List<WorkOrder>>`) and actions (login, load work orders, submit form, sync pending).
- **Repository layer**: One per domain (auth, work orders, forms, notifications). Repositories use:
  - **Remote**: Dio client with interceptors (attach JWT, refresh token on 401, base URL).
  - **Local**: Drift/Hive for offline queue and cached data.
- **Services**: ApiClient (Dio wrapper + envelope parsing), AuthService (login, logout, refresh, passcode set/verify), SyncService (upload pending form submissions when online; prompt for network on file upload).

---

## 3. API client and auth

- **Dio**: Base options `baseUrl`, `connectTimeout`, `headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' }`.
- **Interceptors**:
  - Request: add `Authorization: Bearer <token>` from secure storage (skip for `/login`, `/forgot-password`, `/reset-password`, `/refresh`).
  - Response: on 401, call `/refresh` and retry once; on failure, clear token and redirect to login.
- **Response parsing**: Map all success responses to a generic `ApiResponse<T>` (and `PaginatedResponse<T>`) so ViewModels consume typed `data` and pagination (current_page, last_page, total, etc.).
- **Auth flow**: Login screen → POST `/login` with email/password → store token + user in secure storage and app state → optional “Set passcode” screen (POST `/users/set-passcode`); on next launch, optional passcode/PIN screen → verify via POST `/users/verify-passcode` (or local PIN only if backend passcode is not required for app open).

---

## 4. Screens and features (mapped to API)


| Feature               | API / behaviour                                                                                                                                                                                                                                                                                                                                                                                                                                             |
| --------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Header**            | Logo (asset); Notifications icon → notifications list; optional unread badge from notifications count.                                                                                                                                                                                                                                                                                                                                                      |
| **Login**             | POST `/login` (email, password). On success → home or passcode setup. Forgot password: POST `/forgot-password`; reset: POST `/reset-password`.                                                                                                                                                                                                                                                                                                              |
| **Passcode / PIN**    | Set: POST `/users/set-passcode` { passcode }. Verify: POST `/users/verify-passcode` { passcode }. Optional local PIN screen after app open.                                                                                                                                                                                                                                                                                                                 |
| **Work Orders list**  | GET `/work-orders` with query: `priority`, `sort_by` (e.g. distance), `sort_order`, `latitude`, `longitude`, `radius`, `per_page`. Display: Work order ID, Form name, Priority, Distance. Filters: distance, priority, “nearby” (send device lat/long + radius).                                                                                                                                                                                            |
| **Work order detail** | GET `/work-orders/:id`. Show destination; GET `/work-orders/:id/map` and GET `/work-orders/:id/directions` for map URL and directions URL. In-app: show map (Google Map) with destination and optional ETA (compute client-side or from API if available). “Start direction” → `url_launcher` to open directions URL in Google Maps app.                                                                                                                    |
| **Form (work order)** | GET `/work-orders/:workOrderId/forms/:formId` for schema/fields. Display all fields (text, number, select, file, etc.). Fill locally; submit: POST `/work-orders/:workOrderId/submit-form` (multipart: `form_id`, `work_order_id`, plus dynamic `field_<id>` for values; files as multipart file parts). If offline: save to local queue; sync when online. If submission includes files and device is offline: show “Connect to internet to upload files”. |
| **Forms (manage)**    | GET `/forms?per_page=15`. List: Title, Version, Fields total. Update missing data: PUT `/forms/:formId/records/:recordId` with JSON body. “Download”: implement as fetch + cache form definition (GET `/forms/:id`) for offline use; if backend adds an export endpoint later, add a download action.                                                                                                                                                       |
| **Notifications**     | GET `/notifications?per_page=15&unread_only=0`. List items (id, type, title, message, data, action_url, read_at, created_at). Mark read: POST `/notifications/:id/read`. Types can drive UI (e.g. “Assigned to work order”, “Work order updated”, “General”).                                                                                                                                                                                               |


---

## 5. Offline and sync

- **When online**: Normal API calls; work order list and form definitions can be cached (e.g. Drift tables or Hive) for later offline use.
- **When offline**: Allow filling forms; store submission payload (and field values + file paths) in local DB as “pending”. Disable or hide “Submit” that requires file upload until online, or show a clear message: “Connect to internet to upload files.”
- **When back online**: Sync service (e.g. triggered by connectivity listener or on app resume) processes pending submissions: for each pending item, POST submit-form (with multipart for files). On success remove from queue; on 4xx keep for user to fix; on 5xx/network error retry later.
- **Conflict**: If backend returns validation errors, surface them and allow user to update missing data (align with “Manage Forms” → update form record).

---

## 6. Push notifications (FCM)

- Add `google-services.json` (Android) and `GoogleService-Info.plist` (iOS); configure `firebase_core` and `firebase_messaging`.
- Request notification permission; get FCM token and store it. If backend has an endpoint to register device token, call it after login; otherwise document the need for it (e.g. `POST /devices` or `PATCH /me` with `fcm_token`).
- Handle foreground/background messages: update notifications list (e.g. invalidate Riverpod provider or refetch) and optionally navigate to work order or notification detail when user taps the notification (using `data` payload).

---

## 7. Navigation and app structure

- **Logged out**: Login screen; optional Forgot/Reset password screens.
- **Logged in**: Bottom nav or drawer with: Work Orders, Forms, Notifications; persistent header with logo and notification icon.
- **Work order flow**: List → Detail (map + “Open in Google Maps”) → Form(s) → Fill → Submit (or queue for offline).
- **Forms**: List → Form detail (view/cache) → “Update record” for existing records with missing data (navigate to form record by id and call PUT `/forms/:formId/records/:recordId`).

---

## 8. Suggested folder structure (Flutter)

```
lib/
  main.dart
  app.dart                    # MaterialApp, routes, connectivity listener
  core/
    api/
      api_client.dart         # Dio, interceptors, base URL
      api_response.dart      # ApiResponse<T>, PaginatedResponse<T>
    config/env.dart
    constants/
  data/
    local/                    # Drift DB or Hive (pending submissions, cache)
    remote/                   # Repository implementations (API calls)
  domain/
    models/                   # User, WorkOrder, Form, Notification, etc.
    repositories/             # Abstract repo interfaces if desired
  features/
    auth/                     # login view + view_model, passcode view + view_model
    work_orders/              # list, detail, map, form fill + view_models
    forms/                    # list, detail, update record + view_models
    notifications/            # list + view_model
  shared/                     # common widgets (header, loading, error)
```

---

## 9. Implementation order (high level)

1. Flutter project + pubspec (dio, riverpod, secure_storage, drift or hive).
2. Core: env, API client, envelope parsing, auth interceptors.
3. Auth: login screen + ViewModel, token storage, refresh logic; optional passcode set/verify screens.
4. Work orders: list screen + filters (distance, priority, nearby), detail screen, map (in-app) + “Open in Google Maps”, form load + dynamic form UI + submit (multipart).
5. Offline: local DB for pending submissions, connectivity detection, sync on resume/online.
6. Forms: list (title, version, fields count), form detail, update record (PUT); “Download” = fetch + cache.
7. Notifications: list, mark as read, optional badge in header.
8. FCM: setup, token registration (when backend supports it), handle messages and tap.
9. Polish: header logo, error messages, loading states, “Connect to internet for files” prompt.

---

## 10. Backend gap (optional)

- **FCM token**: Backend may need a `devices` or `user` endpoint to store FCM token for push (e.g. `PATCH /me` with `fcm_token`). Confirm with existing API; add to plan when implementing FCM.

---

## Summary

- New Flutter app in repo; MVVM with Riverpod; Dio + JWT + refresh; Drift/Hive for offline queue and cache.
- All listed API endpoints from the Postman collection are covered; form submit uses multipart; pagination and response envelope are defined.
- Offline: fill forms and queue submissions; sync when online; require network for file uploads.
- FCM integrated; device token registration depends on backend support.

