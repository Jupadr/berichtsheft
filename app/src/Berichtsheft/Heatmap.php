<?php

namespace App\Berichtsheft;

use DateTime;
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
            
            if (count($currentEntries) === 0) {
                $resultArray[] = (object)[
                    'date'  => $pointerDay->format('Y-m-d'),
                    'value' => HeatmapColor::FEHLEND->value,
                ];
                $pointerDay->modify("+1 day");
                continue;
            }
            
            $color         = HeatmapColor::FEHLEND;
            $uniqueEntries = array_unique(
                array_map(static function ($entry) {
                    return match ($entry->getStatus()) {
                        0 => HeatmapColor::ABGEARBEITET,
                        1 => HeatmapColor::ABGESEGNET,
                        2 => HeatmapColor::ABGELEHNT,
                        default => HeatmapColor::FEHLEND,
                    };
                }, $entries)
            );
            
            if (in_array(HeatmapColor::ABGELEHNT, $uniqueEntries, true)) {
                $color = HeatmapColor::ABGELEHNT->value;
            } elseif (in_array(HeatmapColor::ABGEARBEITET, $uniqueEntries, true)) {
                $color = HeatmapColor::ABGEARBEITET->value;
            } elseif (in_array(HeatmapColor::ABGESEGNET, $uniqueEntries, true)) {
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