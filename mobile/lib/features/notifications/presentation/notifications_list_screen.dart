import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/api/api_response.dart';
import '../../../core/widgets/app_drawer.dart';
import '../../../core/widgets/no_connection_widget.dart';
import '../../../domain/models/notification_model.dart';
import '../data/notifications_repository.dart';
import 'notifications_providers.dart';

class NotificationsListScreen extends ConsumerStatefulWidget {
  const NotificationsListScreen({super.key});

  @override
  ConsumerState<NotificationsListScreen> createState() => _NotificationsListScreenState();
}

class _NotificationsListScreenState extends ConsumerState<NotificationsListScreen> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Notifications')),
      drawer: const AppDrawer(),
      body: FutureBuilder<PaginatedResponse<NotificationModel>>(
        future: ref.read(notificationsRepositoryProvider).list(),
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }
          if (snapshot.hasError) {
            if (isConnectionError(snapshot.error)) {
              return NoConnectionWidget(onRetry: () => setState(() {}));
            }
            return Center(child: Text('Error: ${snapshot.error}'));
          }
          final res = snapshot.data;
          if (res == null || res.data.isEmpty) {
            return const Center(child: Text('No notifications'));
          }
          return RefreshIndicator(
            onRefresh: () async => setState(() {}),
            child: ListView.builder(
              itemCount: res.data.length,
              itemBuilder: (context, i) {
                final n = res.data[i];
                return ListTile(
                  leading: Icon(
                    n.isRead ? Icons.mark_email_read : Icons.mark_email_unread,
                    color: n.isRead ? null : Theme.of(context).colorScheme.primary,
                  ),
                  title: Text(n.title ?? '—'),
                  subtitle: Text(n.message ?? ''),
                  trailing: n.isRead
                      ? null
                      : TextButton(
                          onPressed: () async {
                            await ref
                                .read(notificationsRepositoryProvider)
                                .markAsRead(n.id);
                            setState(() {});
                          },
                          child: const Text('Mark read'),
                        ),
                );
              },
            ),
          );
        },
      ),
    );
  }
}
