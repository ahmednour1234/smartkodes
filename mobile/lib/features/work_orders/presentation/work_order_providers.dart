import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/api/api_client_provider.dart';
import '../data/work_order_repository.dart';

final workOrderRepositoryProvider = Provider<WorkOrderRepository>((ref) {
  return WorkOrderRepository(ref.watch(apiClientProvider));
});
