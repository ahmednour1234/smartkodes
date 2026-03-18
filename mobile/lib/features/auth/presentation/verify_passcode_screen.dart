import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:local_auth/local_auth.dart';

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

class _VerifyPasscodeScreenState extends ConsumerState<VerifyPasscodeScreen>
  with WidgetsBindingObserver {
  final _controller = TextEditingController();
  final LocalAuthentication _localAuth = LocalAuthentication();
  bool _loading = false;
  String? _error;
  bool _obscure = true;
  bool _biometricAvailable = false;
  bool _biometricLoading = false;
  bool _biometricChecked = false;
  bool _attemptedAutoBiometric = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    _checkBiometricAvailability();
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _controller.dispose();
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed && _biometricAvailable && !_biometricLoading) {
      _unlockWithBiometric();
    }
  }

  static final _digitsOnly = RegExp(r'^\d{6}$');

  Future<void> _checkBiometricAvailability() async {
    try {
      final canCheck = await _localAuth.canCheckBiometrics;
      final isSupported = await _localAuth.isDeviceSupported();
      await _localAuth.getAvailableBiometrics();
      final available = canCheck || isSupported;
      if (!mounted) return;
      setState(() {
        _biometricAvailable = available;
        _biometricChecked = true;
      });
      if (available && !_attemptedAutoBiometric) {
        _attemptedAutoBiometric = true;
        WidgetsBinding.instance.addPostFrameCallback((_) {
          if (mounted) {
            _unlockWithBiometric();
          }
        });
      }
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _biometricAvailable = false;
        _biometricChecked = true;
      });
    }
  }

  Future<void> _unlockWithBiometric() async {
    if (_biometricLoading) return;
    setState(() {
      _biometricLoading = true;
      _error = null;
    });

    try {
      final didAuthenticate = await _localAuth.authenticate(
        localizedReason: 'Authenticate to unlock SmartSite',
        options: const AuthenticationOptions(
          biometricOnly: false,
          stickyAuth: true,
          useErrorDialogs: true,
        ),
      );

      if (!mounted) return;
      if (didAuthenticate) {
        widget.onSuccess();
      }
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _error = 'Biometric authentication failed. Use passcode.';
      });
    } finally {
      if (mounted) {
        setState(() {
          _biometricLoading = false;
        });
      }
    }
  }

  Future<void> _verify() async {
    final passcode = _controller.text.trim();
    if (!_digitsOnly.hasMatch(passcode)) {
      setState(() => _error = 'Enter 6 digits');
      return;
    }
    setState(() => _loading = true);
    final ok = await ref.read(authStateProvider.notifier).verifyPasscode(passcode);
    setState(() => _loading = false);
    if (ok && mounted) {
      widget.onSuccess();
    } else {
      setState(() => _error = 'Wrong passcode');
    }
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
              if (_biometricChecked) ...[
                const SizedBox(height: 12),
                OutlinedButton.icon(
                  onPressed: (_biometricLoading || !_biometricAvailable)
                      ? null
                      : _unlockWithBiometric,
                  icon: _biometricLoading
                      ? const SizedBox(
                          height: 16,
                          width: 16,
                          child: CircularProgressIndicator(strokeWidth: 2),
                        )
                      : const Icon(Icons.fingerprint),
                  label: Text(
                    _biometricLoading
                        ? 'Checking...'
                        : (_biometricAvailable
                            ? 'Use fingerprint / face'
                            : 'Biometric not available'),
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}
