import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/api/api_response.dart';
import '../../../domain/models/notification_model.dart';
import '../../forms/presentation/forms_list_screen.dart';
import '../../work_orders/presentation/work_orders_list_screen.dart';
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
      drawer: Drawer(
        child: ListView(
          children: [
            const DrawerHeader(
              child: Text('SmartKodes', style: TextStyle(color: Colors.white, fontSize: 20)),
            ),
            ListTile(
              leading: const Icon(Icons.assignment),
              title: const Text('Work Orders'),
              onTap: () {
                Navigator.pop(context);
                Navigator.of(context).pushReplacement(
                  MaterialPageRoute(builder: (_) => const WorkOrdersListScreen()),
                );
              },
            ),
            ListTile(
              leading: const Icon(Icons.description),
              title: const Text('Forms'),
              onTap: () {
                Navigator.pop(context);
                Navigator.of(context).pushReplacement(
                  MaterialPageRoute(builder: (_) => const FormsListScreen()),
                );
              },
            ),
            ListTile(
              leading: const Icon(Icons.notifications),
              title: const Text('Notifications'),
              onTap: () => Navigator.pop(context),
            ),
          ],
        ),
      ),
      body: FutureBuilder<PaginatedResponse<NotificationModel>>(
        future: ref.read(notificationsRepositoryProvider).list(),
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }
          if (snapshot.hasError) {
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
