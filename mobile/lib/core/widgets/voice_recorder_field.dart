import 'dart:async';
import 'dart:io';
import 'dart:typed_data';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:just_audio/just_audio.dart';
import 'package:path_provider/path_provider.dart';
import 'package:record/record.dart';

class VoiceRecorderField extends StatefulWidget {
  const VoiceRecorderField({
    super.key,
    required this.label,
    required this.maxFileBytes,
    this.errorText,
    this.currentFileName,
    this.currentAudioBytes,
    this.currentAudioUrl,
    this.onRecorded,
    this.onClear,
    this.readOnly = false,
  });

  final String label;
  final int maxFileBytes;
  final String? errorText;
  final String? currentFileName;
  final Uint8List? currentAudioBytes;
  final String? currentAudioUrl;
  final void Function(Uint8List bytes, String filename)? onRecorded;
  final VoidCallback? onClear;
  final bool readOnly;

  @override
  State<VoiceRecorderField> createState() => _VoiceRecorderFieldState();
}

class _VoiceRecorderFieldState extends State<VoiceRecorderField> {
  AudioRecorder? _recorder;
  final AudioPlayer _player = AudioPlayer();
  Timer? _timer;
  bool _isRecording = false;
  bool _isPlaying = false;
  int _elapsedSeconds = 0;
  String? _currentPath;
  String? _playPath;

  @override
  void initState() {
    super.initState();
    _player.playerStateStream.listen((state) {
      if (!mounted) return;
      setState(() {
        _isPlaying = state.playing;
      });
    });
  }

  Future<AudioRecorder?> _ensureRecorder() async {
    if (_recorder != null) return _recorder;
    try {
      _recorder = AudioRecorder();
      return _recorder;
    } on MissingPluginException {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Recorder plugin not loaded. Stop app and run again.')),
        );
      }
      return null;
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Unable to initialize recorder.')),
        );
      }
      return null;
    }
  }

  @override
  void dispose() {
    _timer?.cancel();
    _player.dispose();
    final recorder = _recorder;
    if (recorder != null) {
      recorder.dispose().catchError((_) {});
    }
    super.dispose();
  }

  Future<void> _startRecording() async {
    final recorder = await _ensureRecorder();
    if (recorder == null) return;

    try {
      final hasPermission = await recorder.hasPermission();
      if (!hasPermission) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Microphone permission is required.')),
          );
        }
        return;
      }

      final dir = await getTemporaryDirectory();
      final filename = 'voice-message-${DateTime.now().millisecondsSinceEpoch}.wav';
      final path = '${dir.path}/$filename';
      _currentPath = path;

      await recorder.start(
        const RecordConfig(
          encoder: AudioEncoder.wav,
          sampleRate: 16000,
        ),
        path: path,
      );

      _timer?.cancel();
      setState(() {
        _isRecording = true;
        _elapsedSeconds = 0;
      });

      _timer = Timer.periodic(const Duration(seconds: 1), (_) {
        if (!mounted) return;
        setState(() => _elapsedSeconds++);
      });
    } on MissingPluginException {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Recorder plugin not loaded. Please reinstall and run again.')),
        );
      }
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Unable to start recording.')),
        );
      }
    }
  }

  Future<void> _stopRecording() async {
    final recorder = _recorder;
    if (recorder == null) return;

    try {
      final path = await recorder.stop();
      _timer?.cancel();
      setState(() => _isRecording = false);

      final finalPath = path ?? _currentPath;
      if (finalPath == null) return;

      final file = File(finalPath);
      if (!await file.exists()) return;

      final bytes = await file.readAsBytes();
      if (bytes.isEmpty) return;

      if (bytes.length > widget.maxFileBytes) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Voice message must be 5MB or less.')),
          );
        }
        return;
      }

      final filename = finalPath.split('/').last;
      _playPath = finalPath;
      widget.onRecorded?.call(bytes, filename);
    } on MissingPluginException {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Recorder plugin not loaded. Please restart app.')),
        );
      }
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Unable to save recording.')),
        );
      }
    }
  }

  String _formatElapsed(int totalSeconds) {
    final minutes = (totalSeconds ~/ 60).toString().padLeft(2, '0');
    final seconds = (totalSeconds % 60).toString().padLeft(2, '0');
    return '$minutes:$seconds';
  }

  Future<String?> _resolvePlayablePath() async {
    if (_playPath != null && await File(_playPath!).exists()) {
      return _playPath;
    }
    if (_currentPath != null && await File(_currentPath!).exists()) {
      _playPath = _currentPath;
      return _playPath;
    }
    final bytes = widget.currentAudioBytes;
    if (bytes != null && bytes.isNotEmpty) {
      final dir = await getTemporaryDirectory();
      final ext = (widget.currentFileName != null && widget.currentFileName!.contains('.'))
          ? widget.currentFileName!.split('.').last
          : 'wav';
      final path = '${dir.path}/voice-preview-${DateTime.now().millisecondsSinceEpoch}.$ext';
      final file = File(path);
      await file.writeAsBytes(bytes, flush: true);
      _playPath = path;
      return _playPath;
    }
    return null;
  }

  Future<void> _togglePlayback() async {
    if (_isRecording) return;
    try {
      if (_isPlaying) {
        await _player.stop();
        return;
      }
      final localPath = await _resolvePlayablePath();
      if (localPath != null) {
        await _player.setFilePath(localPath);
        await _player.play();
        return;
      }
      final url = widget.currentAudioUrl;
      if (url != null && url.isNotEmpty) {
        await _player.setUrl(url);
        await _player.play();
        return;
      }
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('No recording available to play.')),
        );
      }
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Unable to play this recording.')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
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
        if (!widget.readOnly)
          Row(
            children: [
              ElevatedButton.icon(
                onPressed: _isRecording ? _stopRecording : _startRecording,
                icon: Icon(_isRecording ? Icons.stop : Icons.mic),
                label: Text(_isRecording ? 'Stop' : 'Record'),
              ),
              const SizedBox(width: 12),
              Text(
                _isRecording ? _formatElapsed(_elapsedSeconds) : 'Tap record to start',
                style: Theme.of(context).textTheme.bodySmall,
              ),
            ],
          ),
        if (widget.currentFileName != null && widget.currentFileName!.isNotEmpty) ...[
          const SizedBox(height: 8),
          Row(
            children: [
              const Icon(Icons.audiotrack, size: 18),
              const SizedBox(width: 6),
              Expanded(
                child: Text(
                  widget.currentFileName!,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
              if (widget.onClear != null && !widget.readOnly)
                TextButton(
                  onPressed: () async {
                    if (_isPlaying) {
                      await _player.stop();
                    }
                    _playPath = null;
                    widget.onClear?.call();
                  },
                  child: const Text('Clear'),
                ),
              IconButton(
                onPressed: _togglePlayback,
                icon: Icon(_isPlaying ? Icons.stop_circle_outlined : Icons.play_circle_outline),
                tooltip: _isPlaying ? 'Stop playback' : 'Play recording',
              ),
            ],
          ),
        ],
      ],
    );
  }
}
