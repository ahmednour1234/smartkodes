import 'package:flutter/material.dart';

import '../theme/app_theme.dart';

bool isConnectionError(Object? error) {
  if (error == null) return false;
  final s = error.toString().toLowerCase();
  return s.contains('socketexception') ||
      s.contains('failed host lookup') ||
      s.contains('connection error') ||
      s.contains('connection refused') ||
      s.contains('connection reset') ||
      s.contains('no address associated');
}

class NoConnectionWidget extends StatelessWidget {
  const NoConnectionWidget({
    super.key,
    this.onRetry,
    this.compact = false,
  });

  final VoidCallback? onRetry;
  final bool compact;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    if (compact) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(
                Icons.wifi_off_rounded,
                size: 48,
                color: theme.colorScheme.outline,
              ),
              const SizedBox(height: 12),
              Text(
                'No connection',
                style: theme.textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.w600,
                ),
                textAlign: TextAlign.center,
              ),
              if (onRetry != null) ...[
                const SizedBox(height: 16),
                FilledButton.icon(
                  onPressed: onRetry,
                  icon: const Icon(Icons.refresh_rounded, size: 20),
                  label: const Text('Retry'),
                ),
              ],
            ],
          ),
        ),
      );
    }
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                color: AppPalette.surfacePale.withOpacity(0.5),
                shape: BoxShape.circle,
              ),
              child: Icon(
                Icons.wifi_off_rounded,
                size: 64,
                color: theme.colorScheme.primary.withOpacity(0.7),
              ),
            ),
            const SizedBox(height: 24),
            Text(
              'No Connection',
              style: theme.textTheme.headlineSmall?.copyWith(
                fontWeight: FontWeight.w700,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),
            Text(
              'Please check your internet connection and try again.',
              style: theme.textTheme.bodyMedium?.copyWith(
                color: theme.colorScheme.onSurfaceVariant,
              ),
              textAlign: TextAlign.center,
            ),
            if (onRetry != null) ...[
              const SizedBox(height: 24),
              FilledButton.icon(
                onPressed: onRetry,
                icon: const Icon(Icons.refresh_rounded, size: 22),
                label: const Text('Try again'),
                style: FilledButton.styleFrom(
                  padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 14),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
