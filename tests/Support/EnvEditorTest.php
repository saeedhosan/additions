<?php

use Additions\Support\EnvEditor;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $path = EnvEditor::envPath();
    if (File::exists($path)) {
        File::delete($path);
    }
    File::put($path, '');
});

afterEach(function () {
    $path = EnvEditor::envPath();
    if (File::exists($path)) {
        File::delete($path);
    }
});

describe('addKey', function () {

    test('adds a new key to the env file', function () {
        EnvEditor::addKey('APP_NAME', 'My App');

        $content = File::get(EnvEditor::envPath());

        expect($content)->toContain('APP_NAME="My App"');
    });

    test('properly escapes quotes in values', function () {
        EnvEditor::addKey('APP_KEY', 'value with "quotes"');

        $content = File::get(EnvEditor::envPath());

        expect($content)->toContain('APP_KEY="value with \"quotes\""');
    });

    test('returns true and adds new key', function () {
        File::put(EnvEditor::envPath(), 'EXISTING=value');

        $result = EnvEditor::addKey('NEW_KEY', 'new_value');

        expect($result)->toBeTrue();
        expect(File::get(EnvEditor::envPath()))->toContain('NEW_KEY="new_value"');
    });

    test('creates env file if it does not exist', function () {
        $path = EnvEditor::envPath();
        if (File::exists($path)) {
            File::delete($path);
        }

        expect(File::exists($path))->toBeFalse();

        $result = EnvEditor::addKey('NEW_KEY', 'value');

        expect($result)->toBeTrue();
        expect(File::exists($path))->toBeTrue();
    });
});

describe('editKey', function () {

    test('edits an existing key', function () {
        File::put(EnvEditor::envPath(), "APP_NAME=\"Old Name\"\n");

        EnvEditor::editKey('APP_NAME', 'New Name');

        expect(File::get(EnvEditor::envPath()))->toBe("APP_NAME=\"New Name\"\n");
    });

    test('only affects targeted key', function () {
        File::put(EnvEditor::envPath(), "APP_NAME=Old\nDB_HOST=localhost\nDB_PORT=3306");

        EnvEditor::editKey('DB_HOST', 'new_host');

        $content = File::get(EnvEditor::envPath());
        expect($content)->toContain('APP_NAME=Old');
        expect($content)->toContain('DB_HOST="new_host"');
        expect($content)->toContain('DB_PORT=3306');
    });

    test('returns true and updates existing key', function () {
        File::put(EnvEditor::envPath(), "APP_NAME=Test\nDB_HOST=localhost");

        $result = EnvEditor::editKey('APP_NAME', 'Updated');

        expect($result)->toBeTrue();
        expect(File::get(EnvEditor::envPath()))->toContain('APP_NAME="Updated"');
    });

    test('returns false when key does not exist', function () {
        File::put(EnvEditor::envPath(), 'APP_NAME=Test');

        $result = EnvEditor::editKey('NON_EXISTENT', 'value');

        expect($result)->toBeFalse();
    });

    test('returns false when env file does not exist', function () {
        $path = EnvEditor::envPath();
        if (File::exists($path)) {
            File::delete($path);
        }

        $result = EnvEditor::editKey('ANY_KEY', 'value');

        expect($result)->toBeFalse();
    });

    test('properly escapes quotes in values', function () {
        File::put(EnvEditor::envPath(), 'APP_KEY=test');

        EnvEditor::editKey('APP_KEY', 'value with "quotes"');

        expect(File::get(EnvEditor::envPath()))->toContain('APP_KEY="value with \"quotes\""');
    });
});

describe('setKey', function () {
    test('adds key if missing', function () {
        EnvEditor::setKey('APP_URL', 'https://example.com');

        $content = File::get(EnvEditor::envPath());

        expect($content)->toContain('APP_URL="https://example.com"');
    });

    test('edits key if existing', function () {
        File::put(EnvEditor::envPath(), "APP_URL=\"old\"\n");

        EnvEditor::setKey('APP_URL', 'new');

        $content = File::get(EnvEditor::envPath());

        expect($content)->toBe("APP_URL=\"new\"\n");
    });

    test('works when env file is empty', function () {
        File::put(EnvEditor::envPath(), '');

        $result = EnvEditor::setKey('NEW_KEY', 'value');

        expect($result)->toBeTrue();
        expect(File::get(EnvEditor::envPath()))->toContain('NEW_KEY="value"');
    });
});

describe('put', function () {
    test('adds or edits correctly', function () {
        EnvEditor::put('DB_NAME', 'local db');
        expect(File::get(EnvEditor::envPath()))->toContain('DB_NAME="local db"');

        EnvEditor::put('DB_NAME', 'changed');
        expect(File::get(EnvEditor::envPath()))->toContain('DB_NAME="changed"');
    });

    test('adds new key when it does not exist', function () {
        File::put(EnvEditor::envPath(), 'EXISTING=value');

        EnvEditor::put('NEW_KEY', 'new_value');

        expect(File::get(EnvEditor::envPath()))->toContain('NEW_KEY="new_value"');
    });

    test('edits existing key', function () {
        File::put(EnvEditor::envPath(), 'APP_NAME=Old');

        EnvEditor::put('APP_NAME', 'New');

        expect(File::get(EnvEditor::envPath()))->toContain('APP_NAME="New"');
    });

    test('preserves other keys when editing', function () {
        File::put(EnvEditor::envPath(), "APP_NAME=Test\nDB_HOST=localhost");

        EnvEditor::put('APP_NAME', 'Updated');

        $content = File::get(EnvEditor::envPath());
        expect($content)->toContain('APP_NAME="Updated"');
        expect($content)->toContain('DB_HOST=localhost');
    });

    test('handles value with quotes', function () {
        EnvEditor::put('QUOTED_KEY', 'value with "quotes"');

        expect(File::get(EnvEditor::envPath()))->toContain('QUOTED_KEY="value with \"quotes\""');
    });
});

describe('keyExists', function () {
    test('returns true when key exists', function () {
        File::put(EnvEditor::envPath(), "APP_NAME=Test\nDB_HOST=localhost");

        expect(EnvEditor::keyExists('APP_NAME'))->toBeTrue();
        expect(EnvEditor::keyExists('DB_HOST'))->toBeTrue();
    });

    test('returns false when key does not exist', function () {
        File::put(EnvEditor::envPath(), "APP_NAME=Test\nDB_HOST=localhost");

        expect(EnvEditor::keyExists('NON_EXISTENT'))->toBeFalse();
    });

    test('returns false when env file does not exist', function () {
        $path = EnvEditor::envPath();
        if (File::exists($path)) {
            File::delete($path);
        }

        expect(EnvEditor::keyExists('ANY_KEY'))->toBeFalse();
    });

    test('is case sensitive', function () {
        File::put(EnvEditor::envPath(), 'APP_NAME=Test');

        expect(EnvEditor::keyExists('app_name'))->toBeFalse();
        expect(EnvEditor::keyExists('APP_NAME'))->toBeTrue();
    });
});

test('reloadConfig - executes without error', function () {
    config(['app.name' => 'Test App']);

    expect(config('app.name'))->toBe('Test App');

    EnvEditor::reloadConfig();
});
