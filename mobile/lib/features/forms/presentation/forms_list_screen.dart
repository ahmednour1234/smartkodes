import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:path_provider/path_provider.dart';

import '../../../core/api/api_response.dart';
import '../../../core/widgets/app_drawer.dart';
import '../../../domain/models/record_model.dart';
import '../../../domain/models/work_order.dart';
import '../../auth/presentation/auth_providers.dart';
import '../../work_orders/presentation/work_order_providers.dart';
import '../data/forms_repository.dart';
import 'forms_providers.dart';
import 'form_update_record_screen.dart';

class FormsListScreen extends ConsumerStatefulWidget {
  const FormsListScreen({super.key});

  @override
  ConsumerState<FormsListScreen> createState() => _FormsListScreenState();
}

class _FormsListScreenState extends ConsumerState<FormsListScreen> {
  List<WorkOrder>? _workOrders;
  String? _selectedWorkOrderId;
  String? _selectedProjectId;

  @override
  void initState() {
    super.initState();
    ref.read(workOrderRepositoryProvider).list(perPage: 100).then((res) {
      if (mounted) setState(() => _workOrders = res.data);
    });
  }

  List<WorkOrderProject> get _distinctProjects {
    if (_workOrders == null) return [];
    final seen = <String>{};
    final list = <WorkOrderProject>[];
    for (final wo in _workOrders!) {
      if (wo.project != null && !seen.contains(wo.project!.id)) {
        seen.add(wo.project!.id);
        list.add(wo.project!);
      }
    }
    return list;
  }

  Future<void> _openRecordForEdit(RecordModel record) async {
    final form = await ref.read(formsRepositoryProvider).get(record.form!.id);
    if (!mounted || form == null) return;
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => FormUpdateRecordScreen(
          formId: record.form!.id,
          recordId: record.id,
          form: form,
          initialFields: record.fields,
        ),
      ),
    ).then((_) => setState(() {}));
  }

  Future<void> _downloadPdf(RecordModel record) async {
    final bytes = await ref.read(formsRepositoryProvider).getRecordPdfBytes(record.id);
    if (!mounted || bytes == null) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Could not download PDF')),
        );
      }
      return;
    }
    final dir = await getApplicationDocumentsDirectory();
    final date = DateTime.now().toIso8601String().substring(0, 10);
    final file = File('${dir.path}/form_${record.id}_$date.pdf');
    await file.writeAsBytes(bytes);
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('PDF saved to ${file.path}')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(
        title: const Text('Manage Forms'),
        actions: [
          IconButton(
            icon: const Icon(Icons.home_outlined),
            onPressed: () => Navigator.of(context).popUntil((r) => r.isFirst),
            tooltip: 'Home',
          ),
        ],
      ),
      drawer: const AppDrawer(),
      body: FutureBuilder<PaginatedResponse<RecordModel>>(
        future: ref.read(formsRepositoryProvider).listMyRecords(
          workOrderId: _selectedWorkOrderId,
          projectId: _selectedProjectId,
        ),
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }
          if (snapshot.hasError) {
            return Center(child: Text('Error: ${snapshot.error}'));
          }
          final records = snapshot.data?.data ?? [];

          return RefreshIndicator(
            onRefresh: () async => setState(() {}),
            child: ListView(
              padding: const EdgeInsets.all(20),
              children: [
                Row(
                  children: [
                    Icon(Icons.assignment_turned_in_outlined, size: 28, color: theme.colorScheme.primary),
                    const SizedBox(width: 12),
                    Text(
                      'My submitted forms',
                      style: theme.textTheme.titleLarge?.copyWith(
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                Card(
                  elevation: 0,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                    side: BorderSide(color: theme.colorScheme.outlineVariant),
                  ),
                  child: Padding(
                    padding: const EdgeInsets.all(12),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Icon(Icons.filter_list, size: 20, color: theme.colorScheme.primary),
                            const SizedBox(width: 8),
                            Text(
                              'Filters',
                              style: theme.textTheme.titleSmall?.copyWith(
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 12),
                        DropdownButtonFormField<String>(
                          value: _selectedWorkOrderId,
                          decoration: InputDecoration(
                            labelText: 'Work Order',
                            border: const OutlineInputBorder(),
                            contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                          ),
                          items: [
                            const DropdownMenuItem(value: null, child: Text('All work orders')),
                            if (_workOrders != null)
                              ..._workOrders!.map((wo) => DropdownMenuItem(
                                    value: wo.id,
                                    child: Text(
                                      wo.title?.isNotEmpty == true ? wo.title! : wo.id,
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  )),
                          ],
                          onChanged: (v) => setState(() => _selectedWorkOrderId = v),
                        ),
                        const SizedBox(height: 12),
                        DropdownButtonFormField<String>(
                          value: _selectedProjectId,
                          decoration: InputDecoration(
                            labelText: 'Project',
                            border: const OutlineInputBorder(),
                            contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                          ),
                          items: [
                            const DropdownMenuItem(value: null, child: Text('All projects')),
                            ..._distinctProjects.map((p) => DropdownMenuItem(
                                  value: p.id,
                                  child: Text(p.name, overflow: TextOverflow.ellipsis),
                                )),
                          ],
                          onChanged: (v) => setState(() => _selectedProjectId = v),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 20),
                if (records.isEmpty)
                  Card(
                    elevation: 0,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(16),
                      side: BorderSide(color: theme.colorScheme.outlineVariant),
                    ),
                    child: Padding(
                      padding: const EdgeInsets.symmetric(vertical: 32, horizontal: 20),
                      child: Column(
                        children: [
                          Icon(Icons.inbox_outlined, size: 56, color: theme.colorScheme.outline),
                          const SizedBox(height: 16),
                          Text(
                            'No submissions yet',
                            style: theme.textTheme.titleMedium?.copyWith(
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            'Submit forms from a work order to see them here.',
                            textAlign: TextAlign.center,
                            style: theme.textTheme.bodyMedium?.copyWith(
                              color: theme.colorScheme.onSurfaceVariant,
                            ),
                          ),
                        ],
                      ),
                    ),
                  )
                else
                  ...records.map((record) {
                    final formName = record.form?.name ?? '—';
                    final woId = record.workOrder?.id;
                    final submitted = record.submittedAt != null
                        ? (record.submittedAt!.length > 16
                            ? '${record.submittedAt!.substring(0, 10)} ${record.submittedAt!.substring(11, 16)}'
                            : record.submittedAt)
                        : '—';
                    return Padding(
                      padding: const EdgeInsets.only(bottom: 12),
                      child: Card(
                        elevation: 1,
                        shadowColor: theme.colorScheme.shadow.withValues(alpha: 0.08),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(16),
                        ),
                        child: InkWell(
                          onTap: () => _openRecordForEdit(record),
                          borderRadius: BorderRadius.circular(16),
                          child: Padding(
                            padding: const EdgeInsets.all(16),
                            child: Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.all(12),
                                  decoration: BoxDecoration(
                                    color: theme.colorScheme.primaryContainer.withValues(alpha: 0.6),
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: Icon(
                                    Icons.edit_document,
                                    color: theme.colorScheme.onPrimaryContainer,
                                    size: 28,
                                  ),
                                ),
                                const SizedBox(width: 16),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        formName,
                                        style: theme.textTheme.titleMedium?.copyWith(
                                          fontWeight: FontWeight.w600,
                                        ),
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                      const SizedBox(height: 6),
                                      Row(
                                        children: [
                                          if (woId != null) ...[
                                            Icon(Icons.folder_outlined, size: 16, color: theme.colorScheme.outline),
                                            const SizedBox(width: 4),
                                            Flexible(
                                              child: Text(
                                                'WO: $woId',
                                                style: theme.textTheme.bodySmall?.copyWith(
                                                  color: theme.colorScheme.onSurfaceVariant,
                                                ),
                                                overflow: TextOverflow.ellipsis,
                                              ),
                                            ),
                                            const SizedBox(width: 12),
                                          ],
                                          Icon(Icons.calendar_today_outlined, size: 16, color: theme.colorScheme.outline),
                                          const SizedBox(width: 4),
                                          Flexible(
                                            child: Text(
                                              submitted ?? '—',
                                              style: theme.textTheme.bodySmall?.copyWith(
                                                color: theme.colorScheme.onSurfaceVariant,
                                              ),
                                              overflow: TextOverflow.ellipsis,
                                            ),
                                          ),
                                        ],
                                      ),
                                    ],
                                  ),
                                ),
                                IconButton(
                                  icon: const Icon(Icons.picture_as_pdf_outlined),
                                  onPressed: () => _downloadPdf(record),
                                  tooltip: 'Download PDF',
                                  style: IconButton.styleFrom(
                                    backgroundColor: theme.colorScheme.surfaceContainerHighest,
                                  ),
                                ),
                                const SizedBox(width: 4),
                                Icon(Icons.chevron_right, color: theme.colorScheme.outline),
                              ],
                            ),
                          ),
                        ),
                      ),
                    );
                  }),
              ],
            ),
          );
        },
      ),
    );
  }
}
