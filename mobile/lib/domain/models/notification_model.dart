class NotificationModel {
  final String id;
  final String? type;
  final String? title;
  final String? message;
  final Map<String, dynamic>? data;
  final String? actionUrl;
  final String? readAt;
  final String createdAt;

  const NotificationModel({
    required this.id,
    this.type,
    this.title,
    this.message,
    this.data,
    this.actionUrl,
    this.readAt,
    required this.createdAt,
  });

  bool get isRead => readAt != null && readAt!.isNotEmpty;

  factory NotificationModel.fromJson(Map<String, dynamic> json) {
    return NotificationModel(
      id: _str(json['id']) ?? '',
      type: _str(json['type']),
      title: _str(json['title']),
      message: _str(json['message']),
      data: json['data'] as Map<String, dynamic>?,
      actionUrl: _str(json['action_url']),
      readAt: _str(json['read_at']),
      createdAt: _str(json['created_at']) ?? '',
    );
  }
}

String? _str(dynamic v) => v == null ? null : v.toString();
