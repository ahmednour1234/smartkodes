import 'dart:async';
import 'dart:convert';
import 'dart:typed_data';

import 'package:file_picker/file_picker.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:image_picker/image_picker.dart';
import 'package:just_audio/just_audio.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/config/env.dart';
import '../../../core/widgets/barcode_scanner_field.dart';
import '../../../core/widgets/signature_field.dart';
import '../../../core/widgets/voice_recorder_field.dart';
import '../../../core/widgets/gps_map_field.dart';
import '../../../data/local/pending_record_updates_store.dart';
import '../../../domain/models/form_model.dart';
import '../../work_orders/presentation/work_order_providers.dart';
import '../data/forms_repository.dart';
import 'forms_providers.dart';

const int _maxFileBytes = 5 * 1024 * 1024; // 5MB

class FormUpdateRecordScreen extends ConsumerStatefulWidget {
  const FormUpdateRecordScreen({
    super.key,
    required this.formId,
    required this.form,
    this.recordId,
    this.initialFields,
    this.readOnly = false,
  });

  final String formId;
  final FormModel form;
  final String? recordId;
  final Map<String, dynamic>? initialFields;
  final bool readOnly;

  @override
  ConsumerState<FormUpdateRecordScreen> createState() => _FormUpdateRecordScreenState();
}

class _FormUpdateRecordScreenState extends ConsumerState<FormUpdateRecordScreen>
    with WidgetsBindingObserver {
  final _recordIdController = TextEditingController();
  final _values = <String, dynamic>{};
  final _fileData = <String, dynamic>{};
  final _barcodePhotos = <String, ({Uint8List bytes, String filename})>{};
  final _signatureData = <String, ({Uint8List bytes, String filename})>{};
  final _fieldErrors = <String, String>{};
  bool _submitting = false;
  String? _error;
  Timer? _draftTimer;
  final _initialValues = <String, dynamic>{};
  bool _draftLoaded = false;
  bool _hasDraft = false;
  bool _draftWasCleared = false;
  bool _hasUserEdited = false;
  final AudioPlayer _submissionAudioPlayer = AudioPlayer();
  String? _activeSubmissionAudioUrl;
  bool _submissionAudioPlaying = false;

  bool _isSignatureField(FormFieldModel f) {
    final type = f.type.toLowerCase().trim();
    final name = f.name.toLowerCase().trim();
    return type == 'signature' || type.contains('signature') || name.contains('signature');
  }

  bool _isSignatureLikeValue(dynamic value) {
    if (value == null) return false;

    // Handle list/array format (value might be wrapped in a list)
    if (value is List && value.isNotEmpty) {
      value = value.first;
    }

    if (value == null) return false;
    String str = value.toString().trim();

    // Check if value looks like base64 data URL or signature file path
    bool isBase64 = str.startsWith('data:image/png;base64,');
    bool isFilePath = (str.contains('/tenants/') && str.endsWith('.png')) ||
                      (str.contains('records') && str.endsWith('signature.png')) ||
                      (str.contains('storage/') && str.endsWith('.png'));

    return isBase64 || isFilePath;
  }


  Uint8List? _decodeDataUrl(String value) {
    if (!value.startsWith('data:')) return null;
    final comma = value.indexOf(',');
    if (comma < 0 || comma + 1 >= value.length) return null;
    try {
      return base64Decode(value.substring(comma + 1));
    } catch (_) {
      return null;
    }
  }

  bool _looksLikeImagePath(String value) {
    final v = value.toLowerCase();
    return v.endsWith('.png') || v.endsWith('.jpg') || v.endsWith('.jpeg') || v.endsWith('.webp') || v.endsWith('.gif');
  }

  bool _looksLikeAudioPath(String value) {
    final v = value.toLowerCase();
    return v.endsWith('.mp3') || v.endsWith('.wav') || v.endsWith('.m4a') || v.endsWith('.aac') || v.endsWith('.ogg');
  }

  bool _looksLikeVideoPath(String value) {
    final v = value.toLowerCase();
    return v.endsWith('.mp4') || v.endsWith('.mov') || v.endsWith('.mkv') || v.endsWith('.webm') || v.endsWith('.3gp') || v.endsWith('.m4v');
  }

  String _toAbsoluteFileUrl(String value) {
    if (value.startsWith('http://') || value.startsWith('https://')) return value;
    final base = Env.apiBaseUrl.replaceAll(RegExp(r'/api/v1/?$'), '');
    return '$base/storage/$value'.replaceFirst(RegExp(r'//+'), '//');
  }

  Future<void> _toggleSubmissionAudio(String rawValue) async {
    final url = _toAbsoluteFileUrl(rawValue);
    try {
      if (_activeSubmissionAudioUrl == url && _submissionAudioPlaying) {
        await _submissionAudioPlayer.stop();
        if (!mounted) return;
        setState(() {
          _submissionAudioPlaying = false;
        });
        return;
      }

      await _submissionAudioPlayer.setUrl(url);
      await _submissionAudioPlayer.play();
      if (!mounted) return;
      setState(() {
        _activeSubmissionAudioUrl = url;
        _submissionAudioPlaying = true;
      });
    } catch (_) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Unable to play this audio file.')),
      );
    }
  }

  Future<void> _openSubmissionVideo(String rawValue) async {
    final url = _toAbsoluteFileUrl(rawValue);
    final uri = Uri.tryParse(url);
    if (uri == null) return;

    final ok = await launchUrl(uri, mode: LaunchMode.externalApplication);
    if (!ok && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Unable to open this video.')),
      );
    }
  }

  Widget _buildSubmissionValue(dynamic value) {
    if (value == null) {
      return const Text('-', style: TextStyle(color: Colors.black54));
    }

    if (value is List) {
      if (value.isEmpty) {
        return const Text('-', style: TextStyle(color: Colors.black54));
      }
      return Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: value
            .map(
              (item) => Padding(
                padding: const EdgeInsets.only(bottom: 8),
                child: _buildSubmissionValue(item),
              ),
            )
            .toList(),
      );
    }

    if (value is Map) {
      return Text(jsonEncode(value));
    }

    final text = value.toString().trim();
    if (text.isEmpty) {
      return const Text('-', style: TextStyle(color: Colors.black54));
    }

    final dataBytes = _decodeDataUrl(text);
    if (dataBytes != null) {
      return ClipRRect(
        borderRadius: BorderRadius.circular(8),
        child: Image.memory(dataBytes, height: 180, fit: BoxFit.contain),
      );
    }

    if (_looksLikeImagePath(text)) {
      final imageUrl = _toAbsoluteFileUrl(text);
      return ClipRRect(
        borderRadius: BorderRadius.circular(8),
        child: Image.network(
          imageUrl,
          height: 180,
          fit: BoxFit.contain,
          headers: const {'ngrok-skip-browser-warning': 'true'},
          errorBuilder: (_, __, ___) => Text(text),
        ),
      );
    }

    if (_looksLikeAudioPath(text)) {
      final url = _toAbsoluteFileUrl(text);
      final isCurrent = _activeSubmissionAudioUrl == url;
      final isPlaying = isCurrent && _submissionAudioPlaying;
      return Row(
        children: [
          ElevatedButton.icon(
            onPressed: () => _toggleSubmissionAudio(text),
            icon: Icon(isPlaying ? Icons.stop : Icons.play_arrow),
            label: Text(isPlaying ? 'Stop audio' : 'Play audio'),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              text.split('/').last,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(color: Colors.black54),
            ),
          ),
        ],
      );
    }

    if (_looksLikeVideoPath(text)) {
      return Row(
        children: [
          ElevatedButton.icon(
            onPressed: () => _openSubmissionVideo(text),
            icon: const Icon(Icons.play_circle_fill),
            label: const Text('Play video'),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              text.split('/').last,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(color: Colors.black54),
            ),
          ),
        ],
      );
    }

    return SelectableText(text);
  }

  Widget _buildSimpleSubmissionBody() {
    final fields = widget.form.fields ?? [];
    fields.sort((a, b) => (a.order ?? 0).compareTo(b.order ?? 0));

    if (!_draftLoaded) {
      return const Center(child: CircularProgressIndicator());
    }

    final fieldNames = fields.map((f) => f.name).toSet();
    final extraKeys = _values.keys.where((k) => !fieldNames.contains(k)).toList()..sort();

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        if (_error != null)
          Padding(
            padding: const EdgeInsets.only(bottom: 12),
            child: Text(_error!, style: const TextStyle(color: Colors.red)),
          ),
        ...fields.map((f) {
          final label = f.label ?? f.name;
          final value = _values[f.name];
          return Card(
            margin: const EdgeInsets.only(bottom: 12),
            child: Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(label, style: Theme.of(context).textTheme.titleSmall?.copyWith(fontWeight: FontWeight.w600)),
                  const SizedBox(height: 8),
                  _buildSubmissionValue(value),
                ],
              ),
            ),
          );
        }),
        ...extraKeys.map((k) {
          final value = _values[k];
          return Card(
            margin: const EdgeInsets.only(bottom: 12),
            child: Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(k, style: Theme.of(context).textTheme.titleSmall?.copyWith(fontWeight: FontWeight.w600)),
                  const SizedBox(height: 8),
                  _buildSubmissionValue(value),
                ],
              ),
            ),
          );
        }),
      ],
    );
  }

  String get _draftKey => 'update_${widget.formId}_${widget.recordId ?? (_recordIdController.text.trim().isEmpty ? "new" : _recordIdController.text.trim())}';

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    _submissionAudioPlayer.playerStateStream.listen((state) {
      if (!mounted) return;
      setState(() {
        _submissionAudioPlaying = state.playing;
      });
    });
    if (widget.recordId != null) {
      _recordIdController.text = widget.recordId!;
    }
    for (final f in widget.form.fields ?? []) {
      if (f.defaultValue != null) _values[f.name] = f.defaultValue;
    }
    if (widget.initialFields != null) {
      for (final e in widget.initialFields!.entries) {
        _values[e.key] = e.value;
      }
    }
    for (final e in _values.entries) {
      _initialValues[e.key] = _toJsonSafeValue(e.value);
    }
    WidgetsBinding.instance.addPostFrameCallback((_) async {
      if (widget.recordId != null && mounted) {
        final repo = ref.read(formsRepositoryProvider);
        final record = await repo.getRecord(widget.recordId!);
        if (mounted && record != null) {
          final recordFields = record.fields;
          final hasRecordData = recordFields != null && recordFields.isNotEmpty;
          if (hasRecordData || widget.initialFields != null) {
            setState(() {
              if (hasRecordData) {
                for (final e in recordFields!.entries) {
                  _values[e.key] = e.value;
                }
              }
              if (widget.initialFields != null) {
                for (final e in widget.initialFields!.entries) {
                  if (!_values.containsKey(e.key)) _values[e.key] = e.value;
                }
              }
              for (final f in widget.form.fields ?? []) {
                if (!_values.containsKey(f.name) && f.defaultValue != null) {
                  _values[f.name] = f.defaultValue;
                }
              }
              _initialValues.clear();
              for (final e in _values.entries) {
                _initialValues[e.key] = _toJsonSafeValue(e.value);
              }
            });
          }
        }
      }
      if (!mounted) return;
      await _loadDraft();
    });
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _draftTimer?.cancel();
    _submissionAudioPlayer.dispose();
    _recordIdController.dispose();
    if (_hasUserEdited) _saveDraft();
    super.dispose();
  }

  void _scheduleDraftSave() {
    _hasUserEdited = true;
    _draftTimer?.cancel();
    _draftTimer = Timer(const Duration(milliseconds: 1500), () {
      _saveDraft();
    });
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if ((state == AppLifecycleState.paused || state == AppLifecycleState.inactive) && _hasUserEdited) {
      _saveDraft();
    }
  }

  Future<void> _saveDraft() async {
    if (!_hasUserEdited) return;
    final store = ref.read(formDraftStoreProvider);
    final values = <String, dynamic>{};
    for (final e in _values.entries) {
      values[e.key] = _toJsonSafeValue(e.value);
    }
    Map<String, ({Uint8List bytes, String filename})>? flatFiles;
    if (_fileData.isNotEmpty) {
      flatFiles = {};
      for (final e in _fileData.entries) {
        final v = e.value;
        if (v is List && v.isNotEmpty) {
          for (var i = 0; i < v.length; i++) {
            flatFiles['${e.key}__$i'] = v[i] as ({Uint8List bytes, String filename});
          }
        } else if (v is ({Uint8List bytes, String filename})) {
          flatFiles[e.key] = v;
        }
      }
      if (flatFiles!.isEmpty) flatFiles = null;
    }
    await store.saveDraft(_draftKey, values, flatFiles);
  }

  Future<void> _loadDraft() async {
    final key = _draftKey;
    final store = ref.read(formDraftStoreProvider);
    final draft = await store.loadDraftWithFiles(key);
    if (!mounted) return;
    setState(() {
      if (draft != null) {
        _hasDraft = true;
        for (final e in draft.values.entries) {
          _values[e.key] = e.value;
        }
        if (draft.fileData != null) {
          _fileData.addAll(draft.fileData!);
          final grouped = <String, List<(int, ({Uint8List bytes, String filename}))>>{};
          for (final k in _fileData.keys.toList()) {
            final m = RegExp(r'^(.+)__(\d+)$').firstMatch(k);
            if (m != null) {
              final base = m.group(1)!;
              final idx = int.parse(m.group(2)!);
              final file = _fileData.remove(k) as ({Uint8List bytes, String filename});
              grouped.putIfAbsent(base, () => []).add((idx, file));
            }
          }
          for (final e in grouped.entries) {
            e.value.sort((a, b) => a.$1.compareTo(b.$1));
            _fileData[e.key] = e.value.map((x) => x.$2).toList();
          }
        }
      }
      _draftLoaded = true;
    });
  }

  Future<void> _clearDraft() async {
    final store = ref.read(formDraftStoreProvider);
    await store.removeDraft(_draftKey);
    ref.invalidate(draftKeysProvider);
    ref.read(draftKeysRefreshTriggerProvider.notifier).update((s) => s + 1);
    if (!mounted) return;
    setState(() {
      _hasDraft = false;
      _draftWasCleared = true;
      _fileData.clear();
      _barcodePhotos.clear();
      _signatureData.clear();
      _values.clear();
      for (final e in _initialValues.entries) {
        _values[e.key] = e.value;
      }
    });
  }

  static dynamic _toJsonSafeValue(dynamic v) {
    if (v == null) return null;
    if (v is String || v is num || v is bool) return v;
    if (v is List) return v.map((e) => _toJsonSafeValue(e)).toList();
    if (v is Map) return v.map((k, v2) => MapEntry(k.toString(), _toJsonSafeValue(v2)));
    return v.toString();
  }

  String? _existingFileLabel(dynamic value) {
    if (value == null) return null;
    if (value is String && value.trim().isNotEmpty) {
      final s = value.trim();
      if (s.startsWith('{')) return null;
      return s.contains('/') ? s.split('/').last : s;
    }
    if (value is Map && value.isNotEmpty) {
      final name = value['filename'] ?? value['name'] ?? value['path'];
      if (name != null) return name.toString().split('/').last;
    }
    return null;
  }

  String? _existingImageUrl(dynamic value) {
    final list = _existingImageUrls(value);
    return list != null && list.isNotEmpty ? list.first : null;
  }

  String? _existingAudioUrl(dynamic value) {
    if (value == null) return null;
    final base = Env.apiBaseUrl.replaceAll(RegExp(r'/api/v1/?$'), '');
    String? pathToUrl(String s) {
      if (s.startsWith('http')) return s;
      if (s.contains('/')) return '$base/storage/$s'.replaceFirst(RegExp(r'//+'), '//');
      return null;
    }
    if (value is String && value.trim().isNotEmpty) return pathToUrl(value.trim());
    if (value is Map) {
      final path = value['path'] ?? value['url'];
      if (path != null) return pathToUrl(path.toString().trim());
    }
    return null;
  }

  String? _existingBarcodePhotoUrl(String fieldName) {
    final value = _values['${fieldName}_photo'];
    if (value == null) return null;
    String? path;
    if (value is String && value.trim().isNotEmpty) {
      path = value.trim();
    } else if (value is List && value.isNotEmpty) {
      final first = value.first;
      if (first is String && first.trim().isNotEmpty) path = first.trim();
    }
    if (path == null || path.isEmpty) return null;
    if (path.startsWith('http')) return path;
    final base = Env.apiBaseUrl.replaceAll(RegExp(r'/api/v1/?$'), '');
    return '$base/storage/$path'.replaceFirst(RegExp(r'//+'), '//');
  }

  String? _existingSignatureUrl(String fieldName) {
    final value = _values[fieldName];
    if (value == null) return null;
    String? path;
    if (value is String && value.trim().isNotEmpty) {
      path = value.trim();
      if (path.startsWith('data:')) return path;
    } else if (value is List && value.isNotEmpty) {
      final first = value.first;
      if (first is String && first.trim().isNotEmpty) path = first.trim();
    }
    if (path == null || path.isEmpty) return null;
    if (path.startsWith('http')) return path;
    final base = Env.apiBaseUrl.replaceAll(RegExp(r'/api/v1/?$'), '');
    return '$base/storage/$path'.replaceFirst(RegExp(r'//+'), '//');
  }

  List<String>? _existingImageUrls(dynamic value) {
    if (value == null) return null;
    final base = Env.apiBaseUrl.replaceAll(RegExp(r'/api/v1/?$'), '');
    String? pathToUrl(String s) {
      if (s.startsWith('http')) return s;
      if (s.contains('/')) return '$base/storage/$s'.replaceFirst(RegExp(r'//+'), '//');
      return null;
    }
    if (value is String && value.trim().isNotEmpty) {
      final u = pathToUrl(value.trim());
      return u != null ? [u] : null;
    }
    if (value is List) {
      final urls = <String>[];
      for (final e in value) {
        if (e is String && e.trim().isNotEmpty) {
          final u = pathToUrl(e.trim());
          if (u != null) urls.add(u);
        } else if (e is Map) {
          final u = e['url'] ?? e['path'];
          if (u != null) {
            final s = u.toString().trim();
            if (s.startsWith('http')) urls.add(s);
            else if (s.contains('/')) urls.add('$base/storage/$s'.replaceFirst(RegExp(r'//+'), '//'));
          }
        }
      }
      return urls.isEmpty ? null : urls;
    }
    if (value is Map) {
      final u = value['url'] ?? value['path'];
      if (u != null) {
        final s = u.toString().trim();
        if (s.startsWith('http')) return [s];
        if (s.contains('/')) return ['$base/storage/$s'.replaceFirst(RegExp(r'//+'), '//')];
      }
    }
    return null;
  }

  Widget _filePlaceholder(BuildContext context, String? label) {
    return Container(
      height: 180,
      color: Theme.of(context).colorScheme.surfaceContainerHighest.withOpacity(0.5),
      alignment: Alignment.center,
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.insert_drive_file_outlined, size: 48, color: Theme.of(context).colorScheme.onSurfaceVariant),
          if (label != null && label.isNotEmpty) ...[
            const SizedBox(height: 8),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Text(label, style: Theme.of(context).textTheme.bodySmall, textAlign: TextAlign.center, maxLines: 2, overflow: TextOverflow.ellipsis),
            ),
          ],
        ],
      ),
    );
  }

  List<({Uint8List bytes, String filename})> _getFileList(String fieldName) {
    final v = _fileData[fieldName];
    if (v == null) return [];
    if (v is List) return List<({Uint8List bytes, String filename})>.from(v);
    return [v as ({Uint8List bytes, String filename})];
  }

  Future<void> _pickFile(FormFieldModel field) async {
    if (field.type == 'file') {
      final result = await FilePicker.platform.pickFiles(
        type: FileType.custom,
        allowedExtensions: ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'mp4', 'mov', 'avi', 'mkv', 'webm', 'mp3', 'wav', 'ogg', 'm4a'],
        withData: true,
      );
      if (result == null || result.files.isEmpty) return;
      final f = result.files.single;
      final bytes = f.bytes;
      if (bytes == null || bytes.isEmpty) return;
      if (bytes.length > _maxFileBytes) {
        if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('File must be 5MB or less')));
        return;
      }
      final filename = f.name.isNotEmpty ? f.name : 'file';
      setState(() => _fileData[field.name] = (bytes: bytes, filename: filename));
      _scheduleDraftSave();
      return;
    }
    if (field.type == 'photo' || field.type == 'image') {
      final source = await showModalBottomSheet<ImageSource>(
        context: context,
        builder: (ctx) => SafeArea(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              ListTile(leading: const Icon(Icons.camera_alt), title: const Text('Camera'), onTap: () => Navigator.pop(ctx, ImageSource.camera)),
              ListTile(leading: const Icon(Icons.photo_library), title: const Text('Gallery'), onTap: () => Navigator.pop(ctx, ImageSource.gallery)),
            ],
          ),
        ),
      );
      if (source == null) return;
      final picker = ImagePicker();
      final x = await picker.pickImage(source: source);
      if (x == null) return;
      final bytes = await x.readAsBytes();
      final list = _getFileList(field.name);
      final totalBytes = list.fold<int>(0, (s, f) => s + f.bytes.length) + bytes.length;
      if (totalBytes > _maxFileBytes) {
        if (mounted) {
          final totalMB = (totalBytes / (1024 * 1024)).toStringAsFixed(2);
          ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Total would be ${totalMB}MB. Max 5MB for all photos in this field.')));
        }
        return;
      }
      final filename = x.name.isNotEmpty ? x.name : 'image.jpg';
      setState(() {
        list.add((bytes: bytes, filename: filename));
        _fileData[field.name] = list;
      });
      _scheduleDraftSave();
      return;
    }

    final picker = ImagePicker();
    final XFile? x;
    if (field.type == 'video') {
      final source = await showModalBottomSheet<ImageSource>(
        context: context,
        builder: (ctx) => SafeArea(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              ListTile(leading: const Icon(Icons.videocam), title: const Text('Camera'), onTap: () => Navigator.pop(ctx, ImageSource.camera)),
              ListTile(leading: const Icon(Icons.photo_library), title: const Text('Gallery'), onTap: () => Navigator.pop(ctx, ImageSource.gallery)),
            ],
          ),
        ),
      );
      if (source == null) return;
      x = await picker.pickVideo(source: source);
    } else {
      x = await picker.pickImage(source: ImageSource.gallery);
    }
    if (x != null) {
      final bytes = await x.readAsBytes();
      if (bytes.length > _maxFileBytes) {
        if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('File must be 5MB or less')));
        return;
      }
      final defaultName = field.type == 'video' ? 'video.mp4' : 'image.jpg';
      final filename = x.name.isNotEmpty ? x.name : defaultName;
      setState(() => _fileData[field.name] = (bytes: bytes, filename: filename));
      _scheduleDraftSave();
    }
  }

  String? _validateField(FormFieldModel f, dynamic value) {
    final empty = value == null ||
        (value is List && value.isEmpty) ||
        (value is! bool && value is! List && value.toString().trim().isEmpty);
    if (f.required && (empty || (f.type == 'checkbox' && value != true))) {
      return '${f.label ?? f.name} is required';
    }
    if (empty && f.type != 'checkbox') return null;
    if (value == null || value.toString().trim().isEmpty) return null;
    final s = value.toString().trim();
    switch (f.type) {
      case 'date':
      case 'datetime':
        final d = DateTime.tryParse(s);
        if (d == null) return '${f.label ?? f.name} must be a valid date';
        break;
      case 'time':
        if (!RegExp(r'^\d{1,2}:\d{2}(:\d{2})?$').hasMatch(s)) {
          return '${f.label ?? f.name} must be a valid time (HH:mm)';
        }
        break;
      case 'number':
      case 'currency':
        if (num.tryParse(s) == null) return '${f.label ?? f.name} must be a number';
        break;
      case 'email':
        if (!RegExp(r'^[\w.-]+@[\w.-]+\.\w+$').hasMatch(s)) {
          return '${f.label ?? f.name} must be a valid email';
        }
        break;
    }
    return null;
  }

  Future<void> _submit() async {
    final recordId = widget.recordId ?? _recordIdController.text.trim();
    if (recordId.isEmpty) {
      setState(() => _error = 'Enter record ID');
      return;
    }
    final fields = widget.form.fields ?? [];
    final errs = <String, String>{};
    for (final f in fields) {
      final v = _values[f.name];
      final isFileType = ['file', 'photo', 'video', 'audio', 'voice_message', 'image'].contains(f.type);
      final hasFileValue = isFileType && (
        (f.type == 'photo' || f.type == 'image') ? _getFileList(f.name).isNotEmpty
            : _fileData[f.name] != null
            || (v != null && v.toString().trim().isNotEmpty)
      );
      if (isFileType && f.required && !hasFileValue) {
        errs[f.name] = '${f.label ?? f.name} is required';
      }
      if (isFileType && hasFileValue) continue;
      final msg = _validateField(f, v);
      if (msg != null) errs[f.name] = msg;
    }
    if (errs.isNotEmpty) {
      setState(() {
        _fieldErrors.clear();
        _fieldErrors.addAll(errs);
        _error = 'Please fix the errors below.';
      });
      return;
    }
    setState(() {
      _submitting = true;
      _error = null;
      _fieldErrors.clear();
    });
    final fileTypes = ['file', 'photo', 'video', 'audio', 'voice_message', 'image', 'signature'];
    final fileFieldNames = <String>{};
    final photoFieldNames = <String>{};
    for (final f in fields) {
      if (fileTypes.contains(f.type) || _isSignatureField(f)) {
        fileFieldNames.add(f.name);
        if (f.type == 'photo' || f.type == 'image') photoFieldNames.add(f.name);
      }
    }
    final fieldsToSend = <String, dynamic>{};
    for (final entry in _values.entries) {
      if (fileFieldNames.contains(entry.key)) continue;
      fieldsToSend[entry.key] = entry.value;
    }
    final syncService = ref.read(syncServiceProvider);
    final isOnline = await syncService.isOnline;
    if (!isOnline) {
      final offlineFields = <String, dynamic>{};
      for (final entry in fieldsToSend.entries) {
        final hasFile = fileFieldNames.contains(entry.key) && (
          _getFileList(entry.key).isNotEmpty || _fileData[entry.key] != null
        );
        if (hasFile) continue;
        offlineFields[entry.key] = _toJsonSafeValue(entry.value);
      }
      final createdAt = DateTime.now();
      final u = PendingRecordUpdate(
        formId: widget.formId,
        recordId: recordId,
        fields: offlineFields,
        createdAt: createdAt,
      );
      final store = ref.read(pendingRecordUpdatesStoreProvider);
      Map<String, ({Uint8List bytes, String filename})>? filesForPending;
      if (_fileData.isNotEmpty) {
        filesForPending = {};
        for (final e in _fileData.entries) {
          final v = e.value;
          if (v is List && v.isNotEmpty) {
            filesForPending![e.key] = v.first as ({Uint8List bytes, String filename});
          } else if (v is ({Uint8List bytes, String filename})) {
            filesForPending![e.key] = v;
          }
        }
        if (filesForPending!.isEmpty) filesForPending = null;
      }
      if (filesForPending != null) {
        await store.addWithFiles(u, filesForPending);
      } else {
        await store.add(u);
      }
      await ref.read(formDraftStoreProvider).removeDraft(_draftKey);
      setState(() => _submitting = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Saved offline. Will sync when connected.')),
        );
        Navigator.of(context).pop();
      }
      return;
    }
    final repo = ref.read(formsRepositoryProvider);
    try {
      final filesToSend = <String, dynamic>{};
      filesToSend.addAll(_fileData);
      for (final e in _barcodePhotos.entries) {
        filesToSend['${e.key}_photo'] = (bytes: e.value.bytes, filename: e.value.filename);
      }
      for (final e in _signatureData.entries) {
        filesToSend[e.key] = (bytes: e.value.bytes, filename: e.value.filename);
      }

      final ok = await repo.updateRecord(
        widget.formId,
        recordId,
        fieldsToSend,
        fileFields: filesToSend.isEmpty ? null : filesToSend,
      );
      setState(() => _submitting = false);
      if (ok && mounted) {
        await ref.read(formDraftStoreProvider).removeDraft(_draftKey);
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Record updated')),
        );
        Navigator.of(context).pop();
      } else {
        setState(() => _error = 'Update failed');
      }
    } catch (e) {
      setState(() {
        _submitting = false;
        if (e is RecordUpdateValidationException) {
          _fieldErrors.clear();
          _fieldErrors.addAll(e.fieldErrors);
          _error = e.message;
        } else {
          _error = e.toString();
        }
      });
    }
  }

  bool _isUpdated(FormFieldModel f) {
    if (['file', 'photo', 'video', 'audio', 'voice_message', 'image'].contains(f.type)) {
      if (f.type == 'photo' || f.type == 'image') return _getFileList(f.name).isNotEmpty;
      return _fileData[f.name] != null;
    }
    final a = _initialValues[f.name];
    final b = _toJsonSafeValue(_values[f.name]);
    if (a == b) return false;
    return jsonEncode(a) != jsonEncode(b);
  }

  InputDecoration _decoration(FormFieldModel f) {
    final updated = _isUpdated(f);
    return InputDecoration(
      labelText: '${f.label ?? f.name}${f.required ? ' *' : ''}',
      border: OutlineInputBorder(borderSide: BorderSide(color: updated ? Colors.orange : const Color(0xFFE0E0E0), width: updated ? 2 : 1)),
      enabledBorder: OutlineInputBorder(borderSide: BorderSide(color: updated ? Colors.orange : const Color(0xFFE0E0E0), width: updated ? 2 : 1)),
      focusedBorder: OutlineInputBorder(borderSide: BorderSide(color: updated ? Colors.orange : Theme.of(context).colorScheme.primary, width: updated ? 2 : 1)),
      errorText: _fieldErrors[f.name],
      errorBorder: const OutlineInputBorder(borderSide: BorderSide(color: Colors.red)),
    );
  }

  Widget _wrapIfUpdated(Widget child, FormFieldModel f) {
    if (!_isUpdated(f)) return child;
    return Container(
      decoration: BoxDecoration(
        border: Border.all(color: Colors.orange, width: 2),
        borderRadius: BorderRadius.circular(8),
      ),
      padding: const EdgeInsets.all(8),
      child: child,
    );
  }

  Future<void> _pickDate(FormFieldModel f) async {
    final initial = _values[f.name] != null
        ? DateTime.tryParse(_values[f.name].toString())
        : null;
    final d = await showDatePicker(
      context: context,
      initialDate: initial ?? DateTime.now(),
      firstDate: DateTime(1900),
      lastDate: DateTime(2100),
    );
    if (d != null) {
      setState(() => _values[f.name] = '${d.year}-${d.month.toString().padLeft(2, '0')}-${d.day.toString().padLeft(2, '0')}');
      _scheduleDraftSave();
    }
  }

  Future<void> _pickTime(FormFieldModel f) async {
    final s = _values[f.name]?.toString() ?? '';
    TimeOfDay initial = TimeOfDay.now();
    if (s.isNotEmpty) {
      final parts = s.split(':');
      if (parts.length >= 2) {
        final h = int.tryParse(parts[0]);
        final m = int.tryParse(parts[1]);
        if (h != null && m != null) initial = TimeOfDay(hour: h, minute: m);
      }
    }
    final t = await showTimePicker(context: context, initialTime: initial);
    if (t != null) {
      setState(() => _values[f.name] = '${t.hour.toString().padLeft(2, '0')}:${t.minute.toString().padLeft(2, '0')}');
      _scheduleDraftSave();
    }
  }

  Future<void> _pickDateTime(FormFieldModel f) async {
    final initial = _values[f.name] != null
        ? DateTime.tryParse(_values[f.name].toString())
        : null;
    final d = await showDatePicker(
      context: context,
      initialDate: initial ?? DateTime.now(),
      firstDate: DateTime(1900),
      lastDate: DateTime(2100),
    );
    if (d == null) return;
    final t = await showTimePicker(
      context: context,
      initialTime: initial != null
          ? TimeOfDay(hour: initial.hour, minute: initial.minute)
          : TimeOfDay.now(),
    );
    if (t != null) {
      final dt = DateTime(d.year, d.month, d.day, t.hour, t.minute);
      setState(() => _values[f.name] = dt.toIso8601String().replaceFirst('T', ' ').substring(0, 19));
      _scheduleDraftSave();
    }
  }

  Widget _buildReadOnlyField(FormFieldModel f) {
    final value = _values[f.name];
    final label = '${f.label ?? f.name}${f.required ? ' *' : ''}';

    if (f.type == 'audio' || f.type == 'voice_message') {
      final current = _fileData[f.name] as ({Uint8List bytes, String filename})?;
      final existingLabel = _existingFileLabel(value);
      return Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: VoiceRecorderField(
          label: label,
          maxFileBytes: _maxFileBytes,
          currentFileName: current?.filename ?? existingLabel,
          currentAudioBytes: current?.bytes,
          currentAudioUrl: current == null ? _existingAudioUrl(value) : null,
          readOnly: true,
          onRecorded: (_, __) {},
        ),
      );
    }

    if (f.type == 'photo' || f.type == 'image') {
      final existingUrls = _existingImageUrls(value);
      final fileList = _getFileList(f.name);
      return Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(label, style: Theme.of(context).textTheme.bodyMedium),
            const SizedBox(height: 8),
            if ((existingUrls?.isNotEmpty ?? false) || fileList.isNotEmpty)
              Wrap(
                spacing: 8,
                runSpacing: 8,
                children: [
                  if (existingUrls != null)
                    for (final url in existingUrls)
                      ClipRRect(
                        borderRadius: BorderRadius.circular(8),
                        child: Image.network(url, width: 72, height: 72, fit: BoxFit.cover, headers: const {'ngrok-skip-browser-warning': 'true'}, errorBuilder: (_, __, ___) => _filePlaceholder(context, 'Image')),
                      ),
                  for (final file in fileList)
                    ClipRRect(
                      borderRadius: BorderRadius.circular(8),
                      child: Image.memory(file.bytes, width: 72, height: 72, fit: BoxFit.cover),
                    ),
                ],
              )
            else
              Text('—', style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: Theme.of(context).colorScheme.onSurfaceVariant)),
          ],
        ),
      );
    }

    if (f.type == 'file' || f.type == 'video') {
      final existingLabel = _existingFileLabel(value);
      final newFile = _fileData[f.name] as ({Uint8List bytes, String filename})?;
      final displayName = newFile?.filename ?? existingLabel;
      return Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: InputDecorator(
          decoration: InputDecoration(labelText: label, border: const OutlineInputBorder()),
          child: Row(
            children: [
              Icon(f.type == 'video' ? Icons.videocam_outlined : Icons.insert_drive_file_outlined, size: 18),
              const SizedBox(width: 8),
              Expanded(child: Text(displayName ?? '—', overflow: TextOverflow.ellipsis)),
            ],
          ),
        ),
      );
    }

    if (f.type == 'checkbox') {
      final v = value;
      final checked = v == true || v == 'true' || v == '1' || v == 1;
      return Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: CheckboxListTile(
          title: Text(label),
          value: checked,
          onChanged: null,
          controlAffinity: ListTileControlAffinity.leading,
        ),
      );
    }
    if (f.type == 'barcode' || f.type == 'qrcode') {
      return Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: BarcodeScannerField(
          label: label,
          currentValue: value?.toString(),
          currentPhotoBytes: _barcodePhotos[f.name]?.bytes,
          currentPhotoUrl: _barcodePhotos[f.name] == null ? _existingBarcodePhotoUrl(f.name) : null,
          readOnly: true,
          onValueChanged: (_) {},
        ),
      );
    }

    String displayValue = '—';
    if (value != null) {
      if (value is List) {
        displayValue = value.map((e) => e.toString()).join(', ');
      } else {
        final s = value.toString().trim();
        displayValue = s.isEmpty ? '—' : s;
      }
    }

    return Padding(
      padding: const EdgeInsets.only(bottom: 16),
      child: InputDecorator(
        decoration: InputDecoration(
          labelText: label,
          border: const OutlineInputBorder(),
          filled: true,
          fillColor: Theme.of(context).colorScheme.surfaceContainerHighest.withOpacity(0.3),
        ),
        child: Text(displayValue, style: Theme.of(context).textTheme.bodyMedium),
      ),
    );
  }

  Widget _buildField(FormFieldModel f) {
    if (widget.readOnly) return _buildReadOnlyField(f);

    // PRIORITY CHECK: Any field with signature-like value should render as image
    final sigValue = _values[f.name];
    if (_isSignatureLikeValue(sigValue)) {
      return Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: _wrapIfUpdated(
          SignatureField(
            label: '${f.label ?? f.name}${f.required ? ' *' : ''}',
            errorText: _fieldErrors[f.name],
            currentBytes: _signatureData[f.name]?.bytes,
            currentUrl: _signatureData[f.name] == null ? _existingSignatureUrl(f.name) : null,
            readOnly: widget.readOnly,
            onChanged: (bytes, filename) {
              setState(() {
                _signatureData[f.name] = (bytes: bytes, filename: filename);
                _fieldErrors.remove(f.name);
              });
              _scheduleDraftSave();
            },
            onCleared: () {
              setState(() => _signatureData.remove(f.name));
              _scheduleDraftSave();
            },
          ),
          f,
        ),
      );
    }

    // FALLBACK: Treat field as signature if it contains signature-like values
    if (_isSignatureLikeValue(sigValue) && !['file', 'photo', 'video', 'audio', 'voice_message', 'image'].contains(f.type)) {
      return Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: _wrapIfUpdated(
          SignatureField(
            label: '${f.label ?? f.name}${f.required ? ' *' : ''}',
            errorText: _fieldErrors[f.name],
            currentBytes: _signatureData[f.name]?.bytes,
            currentUrl: _signatureData[f.name] == null ? _existingSignatureUrl(f.name) : null,
            readOnly: widget.readOnly,
            onChanged: (bytes, filename) {
              setState(() {
                _signatureData[f.name] = (bytes: bytes, filename: filename);
                _fieldErrors.remove(f.name);
              });
              _scheduleDraftSave();
            },
            onCleared: () {
              setState(() => _signatureData.remove(f.name));
              _scheduleDraftSave();
            },
          ),
          f,
        ),
      );
    }
    final isFile = ['file', 'photo', 'video', 'audio', 'voice_message', 'image'].contains(f.type);
    if (isFile) {
      if (f.type == 'audio' || f.type == 'voice_message') {
        final current = _fileData[f.name] as ({Uint8List bytes, String filename})?;
        final existingLabel = _existingFileLabel(_values[f.name]);
        return Padding(
          padding: const EdgeInsets.only(bottom: 16),
          child: _wrapIfUpdated(
            VoiceRecorderField(
              label: '${f.label ?? f.name}${f.required ? ' *' : ''}',
              errorText: _fieldErrors[f.name],
              maxFileBytes: _maxFileBytes,
              currentFileName: current?.filename ?? existingLabel,
              currentAudioBytes: current?.bytes,
              currentAudioUrl: current == null ? _existingAudioUrl(_values[f.name]) : null,
              onRecorded: (bytes, filename) {
                setState(() {
                  _fileData[f.name] = (bytes: bytes, filename: filename);
                  _fieldErrors.remove(f.name);
                });
                _scheduleDraftSave();
              },
              onClear: (current == null && existingLabel == null)
                  ? null
                  : () {
                      setState(() {
                        _fileData.remove(f.name);
                        _values.remove(f.name);
                      });
                      _scheduleDraftSave();
                    },
            ),
            f,
          ),
        );
      }

      final existingFile = _values[f.name];
      final existingLabel = _existingFileLabel(existingFile);
      final existingUrl = _existingImageUrl(existingFile);
      final existingUrls = _existingImageUrls(existingFile);
      final isMultiPhoto = f.type == 'photo' || f.type == 'image';
      final fileList = isMultiPhoto ? _getFileList(f.name) : null;
      final hasNewFile = isMultiPhoto ? (fileList?.isNotEmpty ?? false) : _fileData[f.name] != null;
      final hasExisting = (existingUrls?.isNotEmpty ?? false) || existingUrl != null;
      final title = f.label ?? f.name;
      final isImageType = isMultiPhoto;
      final showImagePreview = isImageType && (hasNewFile || hasExisting);
      final pickButtonLabel = f.type == 'video'
          ? (hasNewFile ? 'Change video' : (existingLabel != null ? 'Replace video' : 'Pick video'))
          : (isMultiPhoto ? 'Add photo' : (hasNewFile ? 'Change file' : (existingLabel != null ? 'Replace file' : 'Pick file')));
      Widget preview;
      if (isMultiPhoto && (hasExisting || (fileList?.isNotEmpty ?? false))) {
        preview = Wrap(
          spacing: 8,
          runSpacing: 8,
          children: [
            if (existingUrls != null)
              for (final url in existingUrls)
                ClipRRect(
                  borderRadius: BorderRadius.circular(8),
                  child: Image.network(url, width: 72, height: 72, fit: BoxFit.cover, headers: const {'ngrok-skip-browser-warning': 'true'}, errorBuilder: (_, __, ___) => _filePlaceholder(context, 'Image')),
                ),
            if (fileList != null)
              for (var i = 0; i < fileList.length; i++)
                Stack(
                  children: [
                    ClipRRect(
                      borderRadius: BorderRadius.circular(8),
                      child: Image.memory(fileList[i].bytes, width: 72, height: 72, fit: BoxFit.cover),
                    ),
                    Positioned(
                      top: 4,
                      right: 4,
                      child: GestureDetector(
                        onTap: () {
                          setState(() {
                            fileList.removeAt(i);
                            _fileData[f.name] = fileList.isEmpty ? null : fileList;
                          });
                          _scheduleDraftSave();
                        },
                        child: const CircleAvatar(radius: 12, backgroundColor: Colors.black54, child: Icon(Icons.close, size: 16, color: Colors.white)),
                      ),
                    ),
                  ],
                ),
          ],
        );
      } else if (showImagePreview && hasNewFile)
        preview = Image.memory((_fileData[f.name] as ({Uint8List bytes, String filename})).bytes, height: 180, fit: BoxFit.cover);
      else if (showImagePreview && existingUrl != null)
        preview = Image.network(existingUrl, height: 180, fit: BoxFit.cover, headers: const {'ngrok-skip-browser-warning': 'true'}, errorBuilder: (_, __, ___) => _filePlaceholder(context, existingLabel));
      else if (hasNewFile)
        preview = _filePlaceholder(context, (_fileData[f.name] as ({Uint8List bytes, String filename})).filename);
      else if (existingLabel != null)
        preview = _filePlaceholder(context, existingLabel);
      else
        preview = _filePlaceholder(context, f.type == 'video' ? 'No video' : 'No file');
      return Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: _wrapIfUpdated(
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('$title${f.required ? ' *' : ''}'),
              if (_fieldErrors[f.name] != null)
                Padding(
                  padding: const EdgeInsets.only(bottom: 4),
                  child: Text(_fieldErrors[f.name]!, style: const TextStyle(color: Colors.red, fontSize: 12)),
                ),
              const SizedBox(height: 8),
              Card(
                clipBehavior: Clip.antiAlias,
                elevation: 1,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12), side: BorderSide(color: Theme.of(context).dividerColor)),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Padding(
                      padding: const EdgeInsets.fromLTRB(12, 10, 12, 6),
                      child: Text(title, style: Theme.of(context).textTheme.titleSmall?.copyWith(fontWeight: FontWeight.w600)),
                    ),
                    preview,
                    if (isMultiPhoto && ((existingUrls?.length ?? 0) + (fileList?.length ?? 0) > 0))
                      Padding(
                        padding: const EdgeInsets.fromLTRB(10, 0, 10, 0),
                        child: Text('${(existingUrls?.length ?? 0) + (fileList?.length ?? 0)} photo(s). Total max 5MB.', style: Theme.of(context).textTheme.bodySmall),
                      ),
                    Padding(
                      padding: const EdgeInsets.all(10),
                      child: OutlinedButton(
                        onPressed: () => _pickFile(f),
                        child: Text(pickButtonLabel),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          f,
        ),
      );
    }
    if (f.type == 'select' && (f.options?.isNotEmpty ?? false)) {
      return Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: DropdownButtonFormField<String>(
          value: _values[f.name] as String?,
          decoration: _decoration(f),
          items: f.options!
              .map((o) => DropdownMenuItem(value: o, child: Text(o)))
              .toList(),
          onChanged: (v) {
            setState(() {
              _values[f.name] = v;
              _fieldErrors.remove(f.name);
            });
            _scheduleDraftSave();
          },
        ),
      );
    }
    if (f.type == 'barcode' || f.type == 'qrcode') {
      return Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: _wrapIfUpdated(
          BarcodeScannerField(
            label: '${f.label ?? f.name}${f.required ? ' *' : ''}',
            errorText: _fieldErrors[f.name],
            currentValue: _values[f.name]?.toString(),
            currentPhotoBytes: _barcodePhotos[f.name]?.bytes,
            currentPhotoUrl: _barcodePhotos[f.name] == null ? _existingBarcodePhotoUrl(f.name) : null,
            onValueChanged: (v) {
              setState(() {
                _values[f.name] = (v == null || v.isEmpty) ? null : v;
                _fieldErrors.remove(f.name);
              });
              _scheduleDraftSave();
            },
            onPhotoChanged: (bytes, filename) {
              setState(() {
                _barcodePhotos[f.name] = (bytes: bytes, filename: filename);
              });
            },
            readOnly: widget.readOnly,
          ),
          f,
        ),
      );
    }
    if (_isSignatureField(f)) {
      return Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: _wrapIfUpdated(
          SignatureField(
            label: '${f.label ?? f.name}${f.required ? ' *' : ''}',
            errorText: _fieldErrors[f.name],
            currentBytes: _signatureData[f.name]?.bytes,
            currentUrl: _signatureData[f.name] == null ? _existingSignatureUrl(f.name) : null,
            readOnly: widget.readOnly,
            onChanged: (bytes, filename) {
              setState(() {
                _signatureData[f.name] = (bytes: bytes, filename: filename);
                _fieldErrors.remove(f.name);
              });
              _scheduleDraftSave();
            },
            onCleared: () {
              setState(() => _signatureData.remove(f.name));
              _scheduleDraftSave();
            },
          ),
          f,
        ),
      );
    }
    if (f.type == 'radio' && (f.options?.isNotEmpty ?? false)) {
      final current = _values[f.name]?.toString();
      return Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: _wrapIfUpdated(
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('${f.label ?? f.name}${f.required ? ' *' : ''}'),
              if (_fieldErrors[f.name] != null)
                Padding(
                  padding: const EdgeInsets.only(bottom: 4),
                  child: Text(_fieldErrors[f.name]!, style: const TextStyle(color: Colors.red, fontSize: 12)),
                ),
              ...f.options!.map((opt) => RadioListTile<String>(
                title: Text(opt),
                value: opt,
                groupValue: current,
                onChanged: (v) {
                  setState(() {
                    _values[f.name] = v;
                    _fieldErrors.remove(f.name);
                  });
                  _scheduleDraftSave();
                },
              )),
            ],
          ),
          f,
        ),
      );
    }
    if (f.isMultiSelectType && (f.options?.isNotEmpty ?? false)) {
      final current = _values[f.name];
      List<String> selected = [];
      if (current is List) {
        selected = current.map((e) => e.toString()).toList();
      } else if (current is String && current.isNotEmpty) {
        selected = current.contains(',') ? current.split(',').map((e) => e.trim()).where((e) => e.isNotEmpty).toList() : [current];
      }
      return Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: _wrapIfUpdated(
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('${f.label ?? f.name}${f.required ? ' *' : ''}'),
              if (_fieldErrors[f.name] != null)
                Padding(
                  padding: const EdgeInsets.only(bottom: 4),
                  child: Text(_fieldErrors[f.name]!, style: const TextStyle(color: Colors.red, fontSize: 12)),
                ),
              const SizedBox(height: 8),
              Wrap(
                spacing: 8,
                runSpacing: 8,
                children: f.options!.map((opt) {
                  final isSelected = selected.contains(opt);
                  return FilterChip(
                    label: Text(opt),
                    selected: isSelected,
                    onSelected: (_) {
                      setState(() {
                        final next = isSelected ? selected.where((e) => e != opt).toList() : [...selected, opt];
                        _values[f.name] = next.isEmpty ? null : next;
                        _fieldErrors.remove(f.name);
                      });
                      _scheduleDraftSave();
                    },
                  );
                }).toList(),
              ),
            ],
          ),
          f,
        ),
      );
    }
    if (f.type == 'checkbox') {
      final v = _values[f.name];
      final checked = v == true || v == 'true' || v == '1' || v == 1;
      return Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: _wrapIfUpdated(
          CheckboxListTile(
            title: Text('${f.label ?? f.name}${f.required ? ' *' : ''}'),
            value: checked,
            onChanged: (val) {
              setState(() {
                _values[f.name] = val == true;
                _fieldErrors.remove(f.name);
              });
              _scheduleDraftSave();
            },
            controlAffinity: ListTileControlAffinity.leading,
          ),
          f,
        ),
      );
    }
    if (f.type == 'gps') {
      return Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: _wrapIfUpdated(
          GpsMapField(
        label: '${f.label ?? f.name}${f.required ? ' *' : ''}',
        value: _values[f.name],
        errorText: _fieldErrors[f.name],
        onChanged: (v) => setState(() {
          _values[f.name] = v;
          _fieldErrors.remove(f.name);
        }),
          ),
          f,
        ),
      );
    }
    if (f.type == 'date') {
      return Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: InkWell(
          onTap: () async {
            await _pickDate(f);
            if (mounted) setState(() => _fieldErrors.remove(f.name));
          },
          child: InputDecorator(
            decoration: _decoration(f),
            child: Text(_values[f.name]?.toString() ?? 'Tap to pick date'),
          ),
        ),
      );
    }
    if (f.type == 'time') {
      return Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: InkWell(
          onTap: () async {
            await _pickTime(f);
            if (mounted) setState(() => _fieldErrors.remove(f.name));
          },
          child: InputDecorator(
            decoration: _decoration(f),
            child: Text(_values[f.name]?.toString() ?? 'Tap to pick time'),
          ),
        ),
      );
    }
    if (f.type == 'datetime') {
      return Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: InkWell(
          onTap: () async {
            await _pickDateTime(f);
            if (mounted) setState(() => _fieldErrors.remove(f.name));
          },
          child: InputDecorator(
            decoration: _decoration(f),
            child: Text(_values[f.name]?.toString() ?? 'Tap to pick date & time'),
          ),
        ),
      );
    }
    if (f.type == 'number' || f.type == 'currency') {
      return Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: TextFormField(
          keyboardType: const TextInputType.numberWithOptions(decimal: true),
          decoration: _decoration(f),
          initialValue: _values[f.name]?.toString(),
          onChanged: (v) {
            setState(() {
              _values[f.name] = v.isEmpty ? null : num.tryParse(v);
              _fieldErrors.remove(f.name);
            });
            _scheduleDraftSave();
          },
        ),
      );
    }
    return Padding(
      padding: const EdgeInsets.only(bottom: 16),
      child: TextFormField(
        decoration: _decoration(f),
        initialValue: _values[f.name]?.toString(),
        maxLines: f.type == 'textarea' ? 4 : 1,
        keyboardType: f.type == 'email' ? TextInputType.emailAddress : null,
        onChanged: (v) {
          setState(() {
            _values[f.name] = v.isEmpty ? null : v;
            _fieldErrors.remove(f.name);
          });
          _scheduleDraftSave();
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (widget.readOnly) {
      return Scaffold(
        appBar: AppBar(
          title: Text('${widget.form.name} - Submission'),
        ),
        body: _buildSimpleSubmissionBody(),
      );
    }

    final fields = widget.form.fields ?? [];
    fields.sort((a, b) => (a.order ?? 0).compareTo(b.order ?? 0));

    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (didPop, result) async {
        if (didPop) return;
        _draftTimer?.cancel();
        if (!_draftWasCleared && _hasUserEdited) await _saveDraft();
        if (context.mounted) Navigator.of(context).pop({'draftKey': _draftKey, 'cleared': _draftWasCleared});
      },
      child: Scaffold(
        appBar: AppBar(
          title: Text(widget.readOnly ? widget.form.name : '${widget.form.name} – Update'),
          actions: [
            if (_hasDraft && !widget.readOnly)
              IconButton(
                onPressed: () async { await _clearDraft(); },
                icon: const Icon(Icons.delete_outline, color: Colors.orange),
                tooltip: 'Clear draft',
              ),
          ],
        ),
        body: _draftLoaded
            ? KeyedSubtree(
        key: ValueKey('form_$_hasDraft'),
        child: Column(
        children: [
          Expanded(
            child: ListView(
              padding: const EdgeInsets.all(16),
              children: [
                if (_error != null)
                  Padding(
                    padding: const EdgeInsets.only(bottom: 8),
                    child: Text(_error!, style: const TextStyle(color: Colors.red)),
                  ),
                if (widget.recordId == null)
                  Padding(
                    padding: const EdgeInsets.only(bottom: 16),
                    child: TextFormField(
                      controller: _recordIdController,
                      decoration: const InputDecoration(
                        labelText: 'Record ID',
                        border: OutlineInputBorder(),
                      ),
                    ),
                  ),
                ...fields.map((f) => _buildField(f)),
              ],
            ),
          ),
          if (!widget.readOnly)
          Padding(
            padding: const EdgeInsets.all(16),
            child: FilledButton(
              onPressed: _submitting ? null : _submit,
              style: FilledButton.styleFrom(
                minimumSize: const Size(double.infinity, 48),
              ),
              child: _submitting
                  ? const SizedBox(
                      height: 24,
                      width: 24,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    )
                  : const Text('Update'),
            ),
          ),
        ],
        ),
        )
            : const Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    CircularProgressIndicator(),
                    SizedBox(height: 16),
                    Text('Loading...'),
                  ],
                ),
              ),
      ),
    );
  }
}

