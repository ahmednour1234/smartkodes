import 'dart:typed_data';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:mobile_scanner/mobile_scanner.dart';

class BarcodeScannerField extends StatefulWidget {
  const BarcodeScannerField({
    super.key,
    required this.label,
    this.errorText,
    this.currentValue,
    this.currentPhotoBytes,
    this.currentPhotoUrl,
    required this.onValueChanged,
    this.onPhotoChanged,
    this.readOnly = false,
  });

  final String label;
  final String? errorText;
  final String? currentValue;
  final Uint8List? currentPhotoBytes;
  final String? currentPhotoUrl;
  final ValueChanged<String?> onValueChanged;
  final void Function(Uint8List bytes, String filename)? onPhotoChanged;
  final bool readOnly;

  @override
  State<BarcodeScannerField> createState() => _BarcodeScannerFieldState();
}

class _BarcodeScannerFieldState extends State<BarcodeScannerField> {
  Future<void> _scanBarcode() async {
    if (widget.readOnly) return;

    final result = await showModalBottomSheet<String>(
      context: context,
      isScrollControlled: true,
      builder: (context) {
        var found = false;
        return SizedBox(
          height: MediaQuery.of(context).size.height * 0.7,
          child: Stack(
            children: [
              MobileScanner(
                onDetect: (capture) {
                  if (found) return;
                  final value = capture.barcodes.firstOrNull?.rawValue;
                  if (value == null || value.isEmpty) return;
                  found = true;
                  Navigator.of(context).pop(value);
                },
              ),
              Positioned(
                top: 8,
                right: 8,
                child: IconButton(
                  onPressed: () => Navigator.of(context).pop(),
                  icon: const Icon(Icons.close, color: Colors.white),
                ),
              ),
              const Positioned(
                left: 16,
                right: 16,
                bottom: 24,
                child: Card(
                  color: Colors.black87,
                  child: Padding(
                    padding: EdgeInsets.all(12),
                    child: Text(
                      'Point the camera at a barcode',
                      style: TextStyle(color: Colors.white),
                      textAlign: TextAlign.center,
                    ),
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );

    if (!mounted || result == null) return;
    widget.onValueChanged(result);
  }

  Future<void> _captureBarcodePhoto() async {
    if (widget.readOnly) return;
    final picker = ImagePicker();
    final x = await picker.pickImage(source: ImageSource.camera, imageQuality: 85);
    if (x == null) return;
    final bytes = await x.readAsBytes();
    if (bytes.isEmpty) return;
    final filename = x.name.isNotEmpty ? x.name : 'barcode.jpg';
    widget.onPhotoChanged?.call(bytes, filename);
  }

  @override
  Widget build(BuildContext context) {
    final code = widget.currentValue;
    final hasCode = code != null && code.trim().isNotEmpty;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(widget.label),
        if (widget.errorText != null)
          Padding(
            padding: const EdgeInsets.only(top: 4),
            child: Text(
              widget.errorText!,
              style: const TextStyle(color: Colors.red, fontSize: 12),
            ),
          ),
        const SizedBox(height: 8),
        InputDecorator(
          decoration: const InputDecoration(border: OutlineInputBorder()),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                hasCode ? code! : 'No barcode scanned yet',
                style: Theme.of(context).textTheme.bodyMedium,
              ),
              if (widget.currentPhotoBytes != null || (widget.currentPhotoUrl != null && widget.currentPhotoUrl!.isNotEmpty)) ...[
                const SizedBox(height: 10),
                ClipRRect(
                  borderRadius: BorderRadius.circular(8),
                  child: widget.currentPhotoBytes != null
                      ? Image.memory(
                          widget.currentPhotoBytes!,
                          height: 140,
                          width: double.infinity,
                          fit: BoxFit.cover,
                        )
                      : Image.network(
                          widget.currentPhotoUrl!,
                          height: 140,
                          width: double.infinity,
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) => const SizedBox.shrink(),
                        ),
                ),
              ],
              if (!widget.readOnly) ...[
                const SizedBox(height: 10),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: [
                    OutlinedButton.icon(
                      onPressed: _scanBarcode,
                      icon: const Icon(Icons.qr_code_scanner),
                      label: const Text('Scan barcode'),
                    ),
                    OutlinedButton.icon(
                      onPressed: _captureBarcodePhoto,
                      icon: const Icon(Icons.camera_alt_outlined),
                      label: const Text('Capture photo'),
                    ),
                    if (hasCode)
                      TextButton(
                        onPressed: () => widget.onValueChanged(null),
                        child: const Text('Clear code'),
                      ),
                  ],
                ),
              ],
            ],
          ),
        ),
      ],
    );
  }
}
