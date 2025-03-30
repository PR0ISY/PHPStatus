<?php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class UptimeRobotExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('monitor_status', [$this, 'getMonitorStatus']),
            new TwigFilter('logs_status', [$this, 'getLogsStatus']),
            new TwigFilter('monitor_type', [$this, 'getMonitorType']),
            new TwigFilter('log_type', [$this, 'getLogType']),
            new TwigFilter('monitor_sub_type', [$this, 'getMonitorSubType']),
            new TwigFilter('global_status', [$this, 'getGlobalStatus']),
            new TwigFilter('format_duration', [$this, 'formatDuration'])
        ];
    }

    public function getMonitorStatus(int $status): array
    {
        return match ($status) {
            0 => ['label' => 'Paused', 'class' => 'text-slate-400', 'indicator' => 'status-offline'],
            1 => ['label' => 'Not_Checked_Yet', 'class' => 'text-slate-400', 'indicator' => 'status-offline'],
            2 => ['label' => 'Operational', 'class' => 'text-success-400', 'indicator' => 'status-operational'],
            8 => ['label' => 'Degraded', 'class' => 'text-warning-400', 'indicator' => 'status-degraded'],
            9 => ['label' => 'Offline', 'class' => 'text-error-400', 'indicator' => 'status-offline'],
            default => ['label' => 'Unknown', 'class' => 'text-dark-300', 'indicator' => 'status-unknown'],
        };
    }

    public function getLogsStatus($status): array
    {
        return match ($status) {
            "success" => ['bg' => 'bg-success-500/10', 'text' => 'text-success-400', 'indicator' => 'status-operational'],
            "danger" => ['bg' => 'bg-error-500/10', 'text' => 'text-error-400', 'indicator' => 'status-offline'],
            default => ['label' => 'Unknown', 'class' => 'text-dark-300', 'indicator' => 'status-unknown'],
        };
    }

    public function getMonitorType(int $type): string
    {
        return match ($type) {
            1 => 'HTTP(S)',
            2 => 'KEYWORD',
            3 => 'PING',
            4 => 'PORT',
            5 => 'HEARTBEAT',
            default => 'UNKNOWN',
        };
    }

    public function getLogType(int $type): string
    {
        return match ($type) {
            1 => 'DOWN',
            2 => 'UP',
            99 => 'PAUSED',
            98 => 'STARTED',
            default => 'UNKNOWN',
        };
    }

    public function getMonitorSubType(int $subType): string
    {
        return match ($subType) {
            1 => 'HTTP (80)',
            2 => 'HTTPS (443)',
            3 => 'FTP (21)',
            4 => 'SMTP (25)',
            5 => 'POP3 (110)',
            6 => 'IMAP (143)',
            99 => 'CUSTOM PORT',
            default => 'UNKNOWN',
        };
    }

    public function getGlobalStatus(array $monitors): array
    {
        $totalMonitors = count($monitors);
        $offlineCount = 0;
        $degradedCount = 0;
        $totalUptime = 0;

        foreach ($monitors as $monitor) {
            $status = (int) $monitor['status'];
            $uptime = (float) $monitor['all_time_uptime_ratio'];

            if ($status == 9) {
                $offlineCount++;
            } elseif ($uptime < 99) {
                $degradedCount++;
            }

            $totalUptime += $uptime;
        }

        $averageUptime = $totalUptime / max($totalMonitors, 1);
        if ($offlineCount > 0) {
            return ['status' => 'offline', 'color' => 'text-error-500', 'message' => 'Some systems are down!'];
        } elseif ($degradedCount > 0 || $averageUptime < 99) {
            return ['status' => 'degraded', 'color' => 'text-warning-500', 'message' => 'Some systems are degraded'];
        } else {
            return ['status' => 'operational', 'color' => 'text-success-400', 'message' => 'All Systems Operational'];
        }
    }

    public function formatDuration(int $seconds): string
    {
        $year = intdiv($seconds, 31536000);
        $seconds %= 31536000;

        $month = intdiv($seconds, 2592000);
        $seconds %= 2592000;

        $day = intdiv($seconds, 86400);
        $seconds %= 86400;

        $hour = intdiv($seconds, 3600);
        $seconds %= 3600;

        $minute = intdiv($seconds, 60);
        $seconds %= 60;

        $result = [];
        if ($year > 0) {
            $result[] = $year . ($year > 1 ? ' years' : ' year');
        }
        if ($month > 0) {
            $result[] = $month . ($month > 1 ? ' months' : ' month');
        }
        if ($day > 0) {
            $result[] = $day . ($day > 1 ? ' days' : ' day');
        }
        if ($hour > 0) {
            $result[] = $hour . 'h';
        }
        if ($minute > 0) {
            $result[] = $minute . 'min';
        }
        if ($seconds > 0) {
            $result[] = $seconds . 's';
        }
        if ($seconds <= 0) {
            return 'N/A';
        }

        return implode(' ', $result);
    }

}
