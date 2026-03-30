<?php

declare(strict_types=1);

namespace App\Services;

final class EmailService
{
    private string $smtpHost;
    private int    $smtpPort;

    public function __construct()
    {
        $this->smtpHost = $_ENV['MAIL_HOST'] ?? 'mailhog';
        $this->smtpPort = (int) ($_ENV['MAIL_PORT'] ?? 1025);
    }

    /**
     * Sends a plain-text email via SMTP (no external library dependency).
     * For production, swap this implementation with a proper mailer (e.g. PHPMailer/Symfony Mailer).
     */
    public function sendUrgentAlarmNotification(string $alarmDescription, string $equipmentName): bool
    {
        $to      = 'abcd@abc.com.br';
        $from    = 'noreply@alarmsystem.local';
        $subject = "[URGENTE] Alarme ativado: {$alarmDescription}";

        $body = implode("\r\n", [
            "Alarme Urgente Ativado",
            "======================",
            "",
            "Descrição : {$alarmDescription}",
            "Equipamento: {$equipmentName}",
            "Data/hora  : " . date('d/m/Y H:i:s'),
            "",
            "Este é um aviso automático do Sistema de Alarmes.",
        ]);

        return $this->sendViaSMTP($to, $from, $subject, $body);
    }

    private function sendViaSMTP(
        string $to,
        string $from,
        string $subject,
        string $body
    ): bool {
        $socket = @fsockopen($this->smtpHost, $this->smtpPort, $errno, $errstr, 5);

        if (!$socket) {
            error_log("EmailService: cannot connect to SMTP ({$this->smtpHost}:{$this->smtpPort}) - {$errstr}");
            return false;
        }

        $commands = [
            null,                                              // read banner
            "EHLO alarmsystem.local\r\n",
            "MAIL FROM:<{$from}>\r\n",
            "RCPT TO:<{$to}>\r\n",
            "DATA\r\n",
            "From: Sistema de Alarmes <{$from}>\r\nTo: {$to}\r\nSubject: {$subject}\r\nMIME-Version: 1.0\r\nContent-Type: text/plain; charset=utf-8\r\n\r\n{$body}\r\n.\r\n",
            "QUIT\r\n",
        ];

        foreach ($commands as $command) {
            if ($command !== null) {
                fwrite($socket, $command);
            }
            fgets($socket, 512);
        }

        fclose($socket);
        return true;
    }
}
