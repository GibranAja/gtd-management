<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    /**
     * Format tanggal ke format Indonesia WIB
     */
    public static function formatIndonesian($date, $format = 'full'): string
    {
        if (!$date) return '-';
        
        $carbon = Carbon::parse($date)->setTimezone('Asia/Jakarta');
        
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        $days = [
            0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu',
            4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'
        ];
        
        switch ($format) {
            case 'date_only':
                return $carbon->format('d') . ' ' . $months[$carbon->month] . ' ' . $carbon->format('Y');
                
            case 'time_only':
                return $carbon->format('H:i') . ' WIB';
                
            case 'datetime':
                return $carbon->format('d') . ' ' . $months[$carbon->month] . ' ' . $carbon->format('Y') . ' ' . $carbon->format('H:i') . ' WIB';
                
            case 'full':
                return $days[$carbon->dayOfWeek] . ', ' . $carbon->format('d') . ' ' . $months[$carbon->month] . ' ' . $carbon->format('Y') . ' ' . $carbon->format('H:i') . ' WIB';
                
            case 'short':
                return $carbon->format('d/m/Y H:i') . ' WIB';
                
            case 'relative':
                return $carbon->diffForHumans();
                
            default:
                return $carbon->format('d') . ' ' . $months[$carbon->month] . ' ' . $carbon->format('Y') . ' ' . $carbon->format('H:i') . ' WIB';
        }
    }
    
    /**
     * Format untuk API response
     */
    public static function formatForApi($date): array
    {
        if (!$date) return ['formatted' => '-', 'iso' => null, 'timestamp' => null];
        
        $carbon = Carbon::parse($date)->setTimezone('Asia/Jakarta');
        
        return [
            'formatted' => self::formatIndonesian($date, 'datetime'),
            'iso' => $carbon->toISOString(),
            'timestamp' => $carbon->timestamp,
            'relative' => $carbon->diffForHumans()
        ];
    }
}