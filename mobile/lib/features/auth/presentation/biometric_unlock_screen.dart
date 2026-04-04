import 'package:flutter/material.dart';
import 'package:local_auth/local_auth.dart';

class BiometricUnlockScreen extends StatefulWidget {
  const BiometricUnlockScreen({
    super.key,
    required this.onSuccess,
    required this.onUsePasswordFallback,
  });

  final VoidCallback onSuccess;
  final VoidCallback onUsePasswordFallback;

  @override
  State<BiometricUnlockScreen> createState() => _BiometricUnlockScreenState();
}

class _BiometricUnlockScreenState extends State<BiometricUnlockScreen>
    with WidgetsBindingObserver {
  final LocalAuthentication _localAuth = LocalAuthentication();

  bool _checkingAvailability = true;
  bool _biometricAvailable = false;
  bool _unlocking = false;
  bool _attemptedAutoUnlock = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    _checkBiometricAvailability();
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed &&
        _biometricAvailable &&
        !_unlocking &&
        !_attemptedAutoUnlock) {
      _unlockWithBiometric();
    }
  }

  Future<void> _checkBiometricAvailability() async {
    try {
      final canCheck = await _localAuth.canCheckBiometrics;
      final isSupported = await _localAuth.isDeviceSupported();
      final availableList = await _localAuth.getAvailableBiometrics();
      final available = (canCheck || isSupported) && availableList.isNotEmpty;
      if (!mounted) return;
      setState(() {
        _biometricAvailable = available;
        _checkingAvailability = false;
      });
      if (available && !_attemptedAutoUnlock) {
        _attemptedAutoUnlock = true;
        WidgetsBinding.instance.addPostFrameCallback((_) {
          if (mounted) {
            _unlockWithBiometric();
          }
        });
      }
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _biometricAvailable = false;
        _checkingAvailability = false;
        _error = 'Biometric authentication is unavailable on this device.';
      });
    }
  }

  Future<void> _unlockWithBiometric() async {
    if (_unlocking) return;
    setState(() {
      _unlocking = true;
      _error = null;
    });

    try {
      final didAuthenticate = await _localAuth.authenticate(
        localizedReason: 'Authenticate to unlock SmartSite',
        options: const AuthenticationOptions(
          biometricOnly: true,
          stickyAuth: true,
          useErrorDialogs: true,
        ),
      );
      if (!mounted) return;
      if (didAuthenticate) {
        widget.onSuccess();
      } else {
        setState(() {
          _error = 'Biometric check was canceled. You can retry or log in with email.';
        });
      }
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _error = 'Biometric authentication failed. Log in with email and password.';
      });
    } finally {
      if (mounted) {
        setState(() {
          _unlocking = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Spacer(),
              Icon(
                Icons.fingerprint,
                size: 64,
                color: theme.colorScheme.primary,
              ),
              const SizedBox(height: 20),
              Text(
                'Unlock SmartSite',
                textAlign: TextAlign.center,
                style: theme.textTheme.headlineSmall,
              ),
              const SizedBox(height: 8),
              Text(
                'Use biometrics to continue. If biometrics are unavailable, log in with your email and password.',
                textAlign: TextAlign.center,
                style: theme.textTheme.bodyMedium?.copyWith(
                      color: theme.colorScheme.onSurfaceVariant,
                    ),
              ),
              const SizedBox(height: 24),
              if (_error != null) ...[
                Text(
                  _error!,
                  textAlign: TextAlign.center,
                  style: theme.textTheme.bodyMedium?.copyWith(
                        color: theme.colorScheme.error,
                      ),
                ),
                const SizedBox(height: 12),
              ],
              FilledButton.icon(
                onPressed: (_unlocking || _checkingAvailability || !_biometricAvailable)
                    ? null
                    : _unlockWithBiometric,
                icon: _unlocking
                    ? const SizedBox(
                        width: 18,
                        height: 18,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      )
                    : const Icon(Icons.fingerprint),
                label: Text(
                  _unlocking ? 'Checking...' : 'Unlock with biometrics',
                ),
              ),
              const SizedBox(height: 12),
              OutlinedButton(
                onPressed: widget.onUsePasswordFallback,
                child: const Text('Log in with email and password'),
              ),
              const Spacer(),
            ],
          ),
        ),
      ),
    );
  }
}
