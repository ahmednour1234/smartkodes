import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'app_logo.dart';
import '../../features/auth/presentation/auth_providers.dart';
import '../../features/forms/presentation/forms_list_screen.dart';
import '../../features/home/presentation/collected_data_screen.dart';
import '../../features/home/presentation/field_worker_home_screen.dart';
import '../../features/notifications/presentation/notifications_list_screen.dart';
import '../../features/work_orders/presentation/work_orders_list_screen.dart';

class AppDrawer extends ConsumerWidget {
  const AppDrawer({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final theme = Theme.of(context);
    return Drawer(
      child: ListView(
        children: [
          Container(
            height: 224,
            decoration: BoxDecoration(color: theme.colorScheme.primaryContainer),
            padding: const EdgeInsets.fromLTRB(16, 24, 16, 24),
            alignment: Alignment.centerLeft,
            child: AppLogo(
              bigLogo: true,
              color: theme.colorScheme.onPrimaryContainer,
            ),
          ),
          ListTile(
            leading: const Icon(Icons.home_outlined),
            title: const Text('Home'),
            onTap: () => _nav(context, const FieldWorkerHomeScreen()),
          ),
          ListTile(
            leading: const Icon(Icons.assignment_outlined),
            title: const Text('Work Orders'),
            onTap: () => _nav(context, const WorkOrdersListScreen()),
          ),
          ListTile(
            leading: const Icon(Icons.folder_outlined),
            title: const Text('Collected Data'),
            onTap: () => _nav(context, const CollectedDataScreen()),
          ),
          ListTile(
            leading: const Icon(Icons.description_outlined),
            title: const Text('My Forms'),
            onTap: () => _nav(context, const FormsListScreen()),
          ),
          ListTile(
            leading: const Icon(Icons.notifications_outlined),
            title: const Text('Notifications'),
            onTap: () => _nav(context, const NotificationsListScreen()),
          ),
          const Divider(),
          ListTile(
            leading: const Icon(Icons.logout),
            title: const Text('Log out'),
            onTap: () {
              Navigator.pop(context);
              ref.read(authStateProvider.notifier).logout();
            },
          ),
        ],
      ),
    );
  }

  void _nav(BuildContext context, Widget screen) {
    Navigator.pop(context);
    Navigator.of(context).pushReplacement(
      MaterialPageRoute(builder: (_) => screen),
    );
  }
}
