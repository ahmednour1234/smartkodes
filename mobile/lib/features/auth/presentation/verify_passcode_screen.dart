import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'auth_providers.dart';

class VerifyPasscodeScreen extends ConsumerStatefulWidget {
  const VerifyPasscodeScreen({
    super.key,
    required this.onSuccess,
  });

  final VoidCallback onSuccess;

  @override
  ConsumerState<VerifyPasscodeScreen> createState() => _VerifyPasscodeScreenState();
}

class _VerifyPasscodeScreenState extends ConsumerState<VerifyPasscodeScreen> {
  final _controller = TextEditingController();
  bool _loading = false;
  String? _error;
  bool _obscure = true;

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  static final _digitsOnly = RegExp(r'^\d{6}$');

  Future<void> _verify() async {
    final passcode = _controller.text.trim();
    if (!_digitsOnly.hasMatch(passcode)) {
      setState(() => _error = 'Enter 6 digits');
      return;
    }
    setState(() => _loading = true);
    final ok = await ref.read(authStateProvider.notifier).verifyPasscode(passcode);
    setState(() => _loading = false);
    if (ok && mounted) widget.onSuccess();
    else setState(() => _error = 'Wrong passcode');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const SizedBox(height: 32),
              Text(
                'Enter passcode',
                style: Theme.of(context).textTheme.headlineSmall,
              ),
              const SizedBox(height: 8),
              Text(
                'Enter your PIN to open the app.',
                style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                      color: Theme.of(context).colorScheme.onSurfaceVariant,
                    ),
              ),
              const SizedBox(height: 32),
              TextField(
                controller: _controller,
                obscureText: _obscure,
                keyboardType: TextInputType.number,
                maxLength: 6,
                autofocus: true,
                onSubmitted: (_) => _verify(),
                decoration: InputDecoration(
                  labelText: 'Passcode (6 digits)',
                  errorText: _error,
                  border: const OutlineInputBorder(),
                  suffixIcon: IconButton(
                    icon: Icon(_obscure ? Icons.visibility : Icons.visibility_off),
                    onPressed: () => setState(() => _obscure = !_obscure),
                  ),
                ),
              ),
              const SizedBox(height: 24),
              FilledButton(
                onPressed: _loading ? null : _verify,
                child: _loading
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      )
                    : const Text('Unlock'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
