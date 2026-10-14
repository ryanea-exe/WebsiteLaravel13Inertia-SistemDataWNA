<?php

use App\Services\CsvSanitizer;

test('sanitizes formula triggers starting with equal, plus, minus, at, pipe, percent', function () {
    expect(CsvSanitizer::sanitize('=cmd'))->toBe("'=cmd")
        ->and(CsvSanitizer::sanitize('+cmd'))->toBe("'+cmd")
        ->and(CsvSanitizer::sanitize('-cmd'))->toBe("'-cmd")
        ->and(CsvSanitizer::sanitize('@SUM(1+1)'))->toBe("'@SUM(1+1)")
        ->and(CsvSanitizer::sanitize('|cmd'))->toBe("'|cmd")
        ->and(CsvSanitizer::sanitize('%cmd'))->toBe("'%cmd");
});

test('sanitizes formula characters preceded by whitespace', function () {
    expect(CsvSanitizer::sanitize(' =1+1'))->toBe("' =1+1")
        ->and(CsvSanitizer::sanitize('   +cmd'))->toBe("'   +cmd")
        ->and(CsvSanitizer::sanitize('  @SUM(A1:A5)'))->toBe("'  @SUM(A1:A5)")
        ->and(CsvSanitizer::sanitize(' -2+3+calc'))->toBe("' -2+3+calc");
});

test('sanitizes formula characters preceded by tab', function () {
    expect(CsvSanitizer::sanitize("\t=cmd"))->toBe("'\t=cmd")
        ->and(CsvSanitizer::sanitize("\t\t+1+1"))->toBe("'\t\t+1+1")
        ->and(CsvSanitizer::sanitize(" \t=cmd"))->toBe("' \t=cmd");
});

test('sanitizes formula characters preceded by carriage return or line feed', function () {
    expect(CsvSanitizer::sanitize("\r=cmd"))->toBe("'\r=cmd")
        ->and(CsvSanitizer::sanitize("\n=cmd"))->toBe("'\n=cmd")
        ->and(CsvSanitizer::sanitize("\r\n=1+1"))->toBe("'\r\n=1+1")
        ->and(CsvSanitizer::sanitize("\r\n+cmd"))->toBe("'\r\n+cmd")
        ->and(CsvSanitizer::sanitize("\r\n@SUM(1)"))->toBe("'\r\n@SUM(1)");
});

test('sanitizes combination of whitespace, control characters, and formula triggers', function () {
    expect(CsvSanitizer::sanitize("\x00=cmd"))->toBe("'\x00=cmd")
        ->and(CsvSanitizer::sanitize("\x1F@SUM(1)"))->toBe("'\x1F@SUM(1)")
        ->and(CsvSanitizer::sanitize(" \t\r\n\x00 -2+3+calc"))->toBe("' \t\r\n\x00 -2+3+calc")
        ->and(CsvSanitizer::sanitize("\x08+cmd"))->toBe("'\x08+cmd");
});

test('preserves legitimate numeric values and valid negative numbers', function () {
    expect(CsvSanitizer::sanitize('42'))->toBe('42')
        ->and(CsvSanitizer::sanitize(42))->toBe('42')
        ->and(CsvSanitizer::sanitize('0'))->toBe('0')
        ->and(CsvSanitizer::sanitize('-15'))->toBe('-15')
        ->and(CsvSanitizer::sanitize('-0.75'))->toBe('-0.75')
        ->and(CsvSanitizer::sanitize(' -15 '))->toBe(' -15 ')
        ->and(CsvSanitizer::sanitize('-15.50'))->toBe('-15.50')
        ->and(CsvSanitizer::sanitize('+62812345678'))->toBe('+62812345678')
        ->and(CsvSanitizer::sanitize(3.14159))->toBe('3.14159');
});

test('preserves empty values, whitespace, and single dash placeholder', function () {
    expect(CsvSanitizer::sanitize(null))->toBe('')
        ->and(CsvSanitizer::sanitize(''))->toBe('')
        ->and(CsvSanitizer::sanitize('   '))->toBe('   ')
        ->and(CsvSanitizer::sanitize('-'))->toBe('-')
        ->and(CsvSanitizer::sanitize(' - '))->toBe(' - ');
});

test('preserves normal text and unicode characters without unnecessary escaping', function () {
    expect(CsvSanitizer::sanitize('John Doe'))->toBe('John Doe')
        ->and(CsvSanitizer::sanitize('Budi, S.Kom., M.T.'))->toBe('Budi, S.Kom., M.T.')
        ->and(CsvSanitizer::sanitize('佐藤 太郎'))->toBe('佐藤 太郎')
        ->and(CsvSanitizer::sanitize('Nguyễn Văn A'))->toBe('Nguyễn Văn A')
        ->and(CsvSanitizer::sanitize('Formula inside text: a = b + c'))->toBe('Formula inside text: a = b + c');
});

test('sanitizes rows correctly and integrates with fputcsv escaping', function () {
    $row = [
        1,
        'REG-001',
        'John Doe',
        '-15',
        ' =cmd|calc',
        '-',
        "Multi-line\nwith, comma and \"quotes\"",
    ];

    $sanitized = CsvSanitizer::sanitizeRow($row);

    expect($sanitized[0])->toBe('1')
        ->and($sanitized[1])->toBe('REG-001')
        ->and($sanitized[2])->toBe('John Doe')
        ->and($sanitized[3])->toBe('-15')
        ->and($sanitized[4])->toBe("' =cmd|calc")
        ->and($sanitized[5])->toBe('-')
        ->and($sanitized[6])->toBe("Multi-line\nwith, comma and \"quotes\"");

    // Verify fputcsv generates valid RFC 4180 CSV
    $stream = fopen('php://memory', 'r+');
    expect($stream)->not->toBeFalse();

    fputcsv($stream, $sanitized);
    rewind($stream);
    $csvOutput = stream_get_contents($stream);
    fclose($stream);

    expect($csvOutput)->toContain('1,REG-001,"John Doe",-15,"\' =cmd|calc",-,"Multi-line')
        ->and($csvOutput)->toContain('with, comma and ""quotes"""');
});
