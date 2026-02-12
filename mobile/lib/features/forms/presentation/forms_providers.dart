import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/api/api_client_provider.dart';
import '../data/forms_repository.dart';

final formsRepositoryProvider = Provider<FormsRepository>((ref) {
  return FormsRepository(ref.watch(apiClientProvider));
});
