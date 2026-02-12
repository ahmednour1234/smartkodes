import '../../../core/api/api_client.dart';
import '../../../core/api/api_response.dart';
import '../../../domain/models/notification_model.dart';

class NotificationsRepository {
  NotificationsRepository(this._client);

  final ApiClient _client;

  Future<PaginatedResponse<NotificationModel>> list({
    int perPage = 15,
    bool unreadOnly = false,
  }) async {
    return _client.requestPaginated<NotificationModel>(
      'notifications',
      queryParameters: {
        'per_page': perPage,
        'unread_only': unreadOnly ? 1 : 0,
      },
      fromJsonT: (d) => NotificationModel.fromJson(d as Map<String, dynamic>),
    );
  }

  Future<bool> markAsRead(String id) async {
    final response = await _client.request<dynamic>(
      'notifications/$id/read',
      method: 'POST',
    );
    return response.success;
  }
}
