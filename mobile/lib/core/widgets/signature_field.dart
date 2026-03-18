import 'dart:typed_data';
import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:signature/signature.dart';

/// A form field widget that lets the user draw a signature with their finger
/// or stylus. Shows the existing signature (from bytes or URL) and a fresh
/// canvas for drawing. In read-only mode it only displays the current signature.
class SignatureField extends StatefulWidget {
  const SignatureField({
    super.key,
    required this.label,
    this.errorText,
    this.currentBytes,
    this.currentUrl,
    this.onChanged,
    this.onCleared,
    this.readOnly = false,
  });

  /// Field label shown above the canvas.
  final String label;

  /// Validation error text shown in red below the label.
  final String? errorText;

  /// In-memory PNG bytes of the signature drawn this session (from previous
  /// export after user drew). If set, shown as a preview above the canvas.
  final Uint8List? currentBytes;

  /// URL of an existing server-side signature image (from a previous
  /// submission). Shown as a preview if [currentBytes] is null.
  final String? currentUrl;

  /// Called with the PNG bytes whenever the user finishes a stroke.
  final void Function(Uint8List bytes, String filename)? onChanged;

  /// Called when the user taps the Clear button.
  final VoidCallback? onCleared;

  /// When true the canvas is replaced by an image display only.
  final bool readOnly;

  @override
  State<SignatureField> createState() => _SignatureFieldState();
}

class _SignatureFieldState extends State<SignatureField> {
  late final SignatureController _controller;

  Uint8List? _decodeDataUrl(String value) {
    if (!value.startsWith('data:')) return null;
    final comma = value.indexOf(',');
    if (comma < 0 || comma + 1 >= value.length) return null;
    final payload = value.substring(comma + 1);
    try {
      return base64Decode(payload);
    } catch (_) {
      return null;
    }
  }

  @override
  void initState() {
    super.initState();
    _controller = SignatureController(
      penStrokeWidth: 2,
      penColor: Colors.black,
      exportBackgroundColor: Colors.white,
      onDrawEnd: () => _exportSignature(),
    );
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  Future<void> _exportSignature() async {
    if (!_controller.isNotEmpty) return;
    final bytes = await _controller.toPngBytes();
    if (bytes != null && bytes.isNotEmpty) {
      widget.onChanged?.call(bytes, 'signature.png');
    }
  }

  void _clear() {
    _controller.clear();
    widget.onCleared?.call();
  }

  @override
  Widget build(BuildContext context) {
    final hasError = widget.errorText != null;
    final border = BorderRadius.circular(8);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(widget.label, style: Theme.of(context).textTheme.titleSmall),
        if (hasError)
          Padding(
            padding: const EdgeInsets.only(top: 4),
            child: Text(
              widget.errorText!,
              style: const TextStyle(color: Colors.red, fontSize: 12),
            ),
          ),
        const SizedBox(height: 8),
        if (widget.readOnly) ...[
          Container(
            height: 140,
            width: double.infinity,
            decoration: BoxDecoration(
              border: Border.all(color: Colors.grey.shade300),
              borderRadius: border,
              color: Colors.grey.shade50,
            ),
            child: _buildPreviewContent(border),
          ),
        ] else ...[
          // Show existing/last-drawn signature as a reference above the canvas
          if (widget.currentBytes != null && widget.currentBytes!.isNotEmpty) ...[
            const Text(
              'Last saved signature:',
              style: TextStyle(fontSize: 11, color: Colors.grey),
            ),
            const SizedBox(height: 4),
            Container(
              height: 90,
              width: double.infinity,
              decoration: BoxDecoration(
                border: Border.all(color: Colors.grey.shade200),
                borderRadius: border,
              ),
              child: ClipRRect(
                borderRadius: border,
                child: Image.memory(widget.currentBytes!, fit: BoxFit.contain),
              ),
            ),
            const SizedBox(height: 8),
          ] else if (widget.currentUrl != null) ...[
            const Text(
              'Current signature:',
              style: TextStyle(fontSize: 11, color: Colors.grey),
            ),
            const SizedBox(height: 4),
            Container(
              height: 90,
              width: double.infinity,
              decoration: BoxDecoration(
                border: Border.all(color: Colors.grey.shade200),
                borderRadius: border,
              ),
              child: ClipRRect(
                borderRadius: border,
                child: (() {
                  final dataBytes = _decodeDataUrl(widget.currentUrl!);
                  if (dataBytes != null) {
                    return Image.memory(dataBytes, fit: BoxFit.contain);
                  }
                  return Image.network(
                    widget.currentUrl!,
                    fit: BoxFit.contain,
                    headers: const {'ngrok-skip-browser-warning': 'true'},
                    errorBuilder: (_, __, ___) =>
                        const Center(child: Text('Could not load', style: TextStyle(fontSize: 11))),
                  );
                })(),
              ),
            ),
            const SizedBox(height: 8),
          ],
          // Drawing canvas
          const Text(
            'Draw new signature below:',
            style: TextStyle(fontSize: 11, color: Colors.grey),
          ),
          const SizedBox(height: 4),
          Container(
            height: 150,
            width: double.infinity,
            decoration: BoxDecoration(
              border: Border.all(
                color: hasError ? Colors.red : Colors.grey.shade400,
              ),
              borderRadius: border,
              color: Colors.white,
            ),
            child: ClipRRect(
              borderRadius: border,
              child: Signature(
                controller: _controller,
                backgroundColor: Colors.white,
              ),
            ),
          ),
          Row(
            children: [
              const Expanded(
                child: Text(
                  'Sign above with your finger or stylus',
                  style: TextStyle(fontSize: 11, color: Colors.grey),
                ),
              ),
              TextButton.icon(
                onPressed: _clear,
                icon: const Icon(Icons.clear, size: 16),
                label: const Text('Clear'),
                style: TextButton.styleFrom(foregroundColor: Colors.red),
              ),
            ],
          ),
        ],
      ],
    );
  }

  Widget _buildPreviewContent(BorderRadius border) {
    if (widget.currentBytes != null && widget.currentBytes!.isNotEmpty) {
      return ClipRRect(
        borderRadius: border,
        child: Image.memory(widget.currentBytes!, fit: BoxFit.contain),
      );
    }
    if (widget.currentUrl != null) {
      final dataBytes = _decodeDataUrl(widget.currentUrl!);
      if (dataBytes != null) {
        return ClipRRect(
          borderRadius: border,
          child: Image.memory(dataBytes, fit: BoxFit.contain),
        );
      }
      return ClipRRect(
        borderRadius: border,
        child: Image.network(
          widget.currentUrl!,
          fit: BoxFit.contain,
          headers: const {'ngrok-skip-browser-warning': 'true'},
          errorBuilder: (_, __, ___) => const Center(
            child: Text(
              'Could not load signature',
              style: TextStyle(color: Colors.grey),
            ),
          ),
        ),
      );
    }
    return const Center(
      child: Text('No signature', style: TextStyle(color: Colors.grey)),
    );
  }
}
