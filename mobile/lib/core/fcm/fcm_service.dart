import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';

Future<void> _onBackgroundMessage(RemoteMessage message) async {}

class FcmService {
  static Future<void> init() async {
    try {
      await Firebase.initializeApp();
    } catch (_) {
      return;
    }
    FirebaseMessaging.onBackgroundMessage(_onBackgroundMessage);
    await FirebaseMessaging.instance.requestPermission(
      alert: true,
      badge: true,
      sound: true,
    );
    final token = await FirebaseMessaging.instance.getToken();
    if (token != null) {
      // TODO: send token to backend when endpoint exists (e.g. PATCH /me with fcm_token)
    }
    FirebaseMessaging.onMessage.listen((RemoteMessage message) {});
    FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {});
  }
}
