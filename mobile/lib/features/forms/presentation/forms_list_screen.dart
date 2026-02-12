import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/api/api_response.dart';
import '../../../domain/models/form_model.dart';
import '../../auth/presentation/auth_providers.dart';
import '../../notifications/presentation/notifications_list_screen.dart';
import '../../work_orders/presentation/work_orders_list_screen.dart';
import '../data/forms_repository.dart';
import 'forms_providers.dart';
import 'form_detail_screen.dart';

class FormsListScreen extends ConsumerStatefulWidget {
  const FormsListScreen({super.key});

  @override
  ConsumerState<FormsListScreen> createState() => _FormsListScreenState();
}

class _FormsListScreenState extends ConsumerState<FormsListScreen> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Forms'),
        actions: [
          IconButton(
            icon: const Icon(Icons.home_outlined),
            onPressed: () => Navigator.of(context).popUntil((r) => r.isFirst),
            tooltip: 'Home',
          ),
        ],
      ),
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
              onTap: () => Navigator.pop(context),
            ),
            ListTile(
              leading: const Icon(Icons.notifications),
              title: const Text('Notifications'),
              onTap: () {
                Navigator.pop(context);
                Navigator.of(context).pushReplacement(
                  MaterialPageRoute(builder: (_) => const NotificationsListScreen()),
                );
              },
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
      ),
      body: FutureBuilder<PaginatedResponse<FormModel>>(
        future: ref.read(formsRepositoryProvider).list(),
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }
          if (snapshot.hasError) {
            return Center(child: Text('Error: ${snapshot.error}'));
          }
          final res = snapshot.data;
          if (res == null || res.data.isEmpty) {
            return const Center(child: Text('No forms'));
          }
          return ListView.builder(
            itemCount: res.data.length,
            itemBuilder: (context, i) {
              final form = res.data[i];
              return ListTile(
                title: Text(form.name),
                subtitle: Text(
                  'Version ${form.version ?? '—'} • ${form.fieldsCount ?? 0} fields',
                ),
                onTap: () => Navigator.of(context).push(
                  MaterialPageRoute(
                    builder: (_) => FormDetailScreen(formId: form.id),
                  ),
                ),
              );
            },
          );
        },
      ),
    );
  }
}
