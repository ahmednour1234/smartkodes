import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'auth_providers.dart';

class SetPasscodeScreen extends ConsumerStatefulWidget {
  const SetPasscodeScreen({
    super.key,
    required this.onDone,
    required this.onSkip,
  });

  final VoidCallback onDone;
  final VoidCallback onSkip;

  @override
  ConsumerState<SetPasscodeScreen> createState() => _SetPasscodeScreenState();
}

class _SetPasscodeScreenState extends ConsumerState<SetPasscodeScreen> {
  final _controller = TextEditingController();
  final _confirmController = TextEditingController();
  bool _loading = false;
  String? _error;
  bool _obscure = true;

  @override
  void dispose() {
    _controller.dispose();
    _confirmController.dispose();
    super.dispose();
  }

  static final _digitsOnly = RegExp(r'^\d{6}$');

  Future<void> _submit() async {
    final passcode = _controller.text.trim();
    final confirm = _confirmController.text.trim();
    if (!_digitsOnly.hasMatch(passcode)) {
      setState(() => _error = 'Enter 6 digits');
      return;
    }
    if (passcode != confirm) {
      setState(() => _error = 'Passcodes do not match');
      return;
    }
    setState(() => _loading = true);
    final ok = await ref.read(authStateProvider.notifier).setPasscode(passcode);
    setState(() => _loading = false);
    if (ok && mounted) widget.onDone();
    else setState(() => _error = 'Failed to set passcode');
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
                'Set passcode',
                style: Theme.of(context).textTheme.headlineSmall,
              ),
              const SizedBox(height: 8),
              Text(
                'Optional: unlock the app with a PIN after login.',
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
              const SizedBox(height: 16),
              TextField(
                controller: _confirmController,
                obscureText: _obscure,
                keyboardType: TextInputType.number,
                maxLength: 6,
                decoration: const InputDecoration(
                  labelText: 'Confirm passcode (6 digits)',
                  border: OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 24),
              FilledButton(
                onPressed: _loading ? null : _submit,
                child: _loading
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      )
                    : const Text('Set passcode'),
              ),
              const SizedBox(height: 12),
              TextButton(
                onPressed: widget.onSkip,
                child: const Text('Skip'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
