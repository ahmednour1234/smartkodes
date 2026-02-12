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
      id: json['id']?.toString() ?? '',
      type: json['type'] as String?,
      title: json['title'] as String?,
      message: json['message'] as String?,
      data: json['data'] as Map<String, dynamic>?,
      actionUrl: json['action_url'] as String?,
      readAt: json['read_at'] as String?,
      createdAt: json['created_at'] as String? ?? '',
    );
  }
}
