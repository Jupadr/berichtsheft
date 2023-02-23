<?php

namespace App\Berichtsheft;

use DateTime;
use DateTimeZone;
use stdClass;

class Heatmap
{
    
    /**
     * @param  \App\Entity\Entry[]  $entries
     *
     * @return stdClass[]
     */
    public static function handleAzubi(DateTime $start, DateTime $end, array $entries): array
    {
        $currentDate = new DateTime('now');
        
        $pointerDay = $start;
        
        $targetTimestamp = $currentDate < $end ? $currentDate->getTimestamp() : $end->getTimestamp();
        
        $resultArray = [];
        while ($pointerDay->getTimestamp() < $targetTimestamp) {
            $currentEntries = array_filter($entries, static function ($entry) use (&$pointerDay) {
                return $entry->getDate()?->format('Y-m-d') === $pointerDay->format('Y-m-d');
            });
            
            if (count($currentEntries) === 0 && (int)$pointerDay->format('w') > 1) {
                $resultArray[] = (object)[
                    $pointerDay->setTimezone(new DateTimeZone('Europe/London')),
                    'date'  => $pointerDay->format('Y-m-d'),
                    'value' => HeatmapColor::FEHLEND->value,
                ];
                $pointerDay->modify("+1 day");
                continue;
            }
            
            if (count($currentEntries) === 0) {
                $pointerDay->modify('+1 day');
                continue;
            }
            
            $color         = HeatmapColor::FEHLEND;
            $uniqueEntries = array_unique(
                array_map(static function ($entry) {
                    $ret = match ($entry->getStatus()) {
                        0 => HeatmapColor::ABGEARBEITET,
                        1 => HeatmapColor::ABGESEGNET,
                        2 => HeatmapColor::ABGELEHNT,
                        default => HeatmapColor::FEHLEND,
                    };
                    return $ret->value;
                }, $currentEntries)
            );
            
            if (in_array(HeatmapColor::ABGELEHNT->value, $uniqueEntries, true)) {
                $color = HeatmapColor::ABGELEHNT->value;
            } elseif (in_array(HeatmapColor::ABGEARBEITET->value, $uniqueEntries, true)) {
                $color = HeatmapColor::ABGEARBEITET->value;
            } elseif (in_array(HeatmapColor::ABGESEGNET->value, $uniqueEntries, true)) {
                $color = HeatmapColor::ABGESEGNET->value;
            }
            
            $resultArray[] = (object)[
                'date'  => $pointerDay->format('Y-m-d'),
                'value' => $color,
            ];
            
            $pointerDay->modify("+1 day");
        }
        
        return $resultArray;
    }
    
}