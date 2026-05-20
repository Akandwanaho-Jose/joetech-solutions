<?php

function smtp_last_error(?string $message = null): string {
    static $last_error = '';

    if ($message !== null) {
        $last_error = $message;
    }

    return $last_error;
}

function smtp_read_response($socket): array {
    $response = '';

    while (($line = fgets($socket, 515)) !== false) {
        $response .= $line;
        if (isset($line[3]) && $line[3] === ' ') {
            break;
        }
    }

    $code = (int) substr($response, 0, 3);
    return [$code, $response];
}

function smtp_command($socket, string $command, array $expected): bool {
    fwrite($socket, $command . "\r\n");
    [$code, $response] = smtp_read_response($socket);

    if (!in_array($code, $expected, true)) {
        smtp_last_error(trim($response));
        return false;
    }

    return true;
}

function mail_header_value(string $value): string {
    return str_replace(["\r", "\n"], '', $value);
}

function send_app_mail(string $to, string $subject, string $body, ?string $reply_to = null): bool {
    smtp_last_error('');

    if (SMTP_HOST === '' || SMTP_USER === '' || SMTP_PASS === '') {
        smtp_last_error('SMTP is not configured.');
        return false;
    }

    $host = SMTP_HOST;
    $port = SMTP_PORT > 0 ? SMTP_PORT : 587;
    $socket = @stream_socket_client(
        'tcp://' . $host . ':' . $port,
        $errno,
        $errstr,
        20,
        STREAM_CLIENT_CONNECT
    );

    if (!$socket) {
        smtp_last_error($errstr !== '' ? $errstr : 'Could not connect to SMTP server.');
        return false;
    }

    stream_set_timeout($socket, 20);

    [$code, $response] = smtp_read_response($socket);
    if ($code !== 220) {
        fclose($socket);
        smtp_last_error(trim($response));
        return false;
    }

    $server_name = parse_url(SITE_URL, PHP_URL_HOST) ?: 'localhost';

    if (!smtp_command($socket, 'EHLO ' . $server_name, [250])) {
        fclose($socket);
        return false;
    }

    if (SMTP_ENCRYPTION === 'tls') {
        if (!smtp_command($socket, 'STARTTLS', [220])) {
            fclose($socket);
            return false;
        }

        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($socket);
            smtp_last_error('Could not start TLS encryption.');
            return false;
        }

        if (!smtp_command($socket, 'EHLO ' . $server_name, [250])) {
            fclose($socket);
            return false;
        }
    }

    if (!smtp_command($socket, 'AUTH LOGIN', [334])
        || !smtp_command($socket, base64_encode(SMTP_USER), [334])
        || !smtp_command($socket, base64_encode(SMTP_PASS), [235])) {
        fclose($socket);
        return false;
    }

    $from_email = mail_header_value((string) SMTP_FROM);
    $from_name = mail_header_value((string) SMTP_FROM_NAME);
    $to = mail_header_value($to);
    $subject = mail_header_value($subject);
    $reply_to = mail_header_value($reply_to ?: $from_email);

    if (!smtp_command($socket, 'MAIL FROM:<' . $from_email . '>', [250])
        || !smtp_command($socket, 'RCPT TO:<' . $to . '>', [250, 251])
        || !smtp_command($socket, 'DATA', [354])) {
        fclose($socket);
        return false;
    }

    $headers = [
        'From: ' . $from_name . ' <' . $from_email . '>',
        'Reply-To: ' . $reply_to,
        'To: <' . $to . '>',
        'Subject: ' . $subject,
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
    ];

    $data = implode("\r\n", $headers) . "\r\n\r\n" . str_replace("\n.", "\n..", $body) . "\r\n.";
    fwrite($socket, $data . "\r\n");
    [$code, $response] = smtp_read_response($socket);

    smtp_command($socket, 'QUIT', [221]);
    fclose($socket);

    if ($code !== 250) {
        smtp_last_error(trim($response));
        return false;
    }

    return true;
}
