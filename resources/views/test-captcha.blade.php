<!DOCTYPE html>
<html>
<head>
    <title>Captcha Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"] { width: 100%; padding: 10px; font-size: 16px; }
        button { padding: 10px 20px; font-size: 16px; cursor: pointer; }
        .error { color: red; margin-top: 5px; }
        .success { color: green; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; }
        .captcha-container { margin: 20px 0; }
    </style>
</head>
<body>
    <h1>Captcha Test Page</h1>

    @if(session('success'))
        <div class="success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('test.captcha.validate') }}">
        @csrf

        <div class="form-group">
            <label>Captcha Image:</label>
            <div class="captcha-container">
                {!! captcha_img('default') !!}
            </div>
            <button type="button" onclick="location.reload()">Refresh Captcha</button>
        </div>

        <div class="form-group">
            <label for="captcha">Enter Captcha (case insensitive):</label>
            <input type="text" id="captcha" name="captcha" value="{{ old('captcha') }}" autocomplete="off">
            @error('captcha')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit">Validate Captcha</button>
    </form>

    <hr style="margin: 40px 0;">

    <h3>Debug Information:</h3>
    <pre>
Config: {{ json_encode(config('captcha.default'), JSON_PRETTY_PRINT) }}

Session ID: {{ session()->getId() }}

Captcha in session: {{ session()->has('captcha') ? 'Yes' : 'No' }}
    </pre>
</body>
</html>
