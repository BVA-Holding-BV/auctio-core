<?php

namespace AuctioCore;

class Date
{

    /**
     * Convert date to format (in specific language)
     *
     * @param DateTime $dateObject
     * @param string $format
     * @param string $language
     * @return mixed|void
     */
    public function format($dateObject, $format, $language)
    {
        if (empty($dateObject)) return;

        // Convert date-object to format
        $date = $dateObject->format($format);

        // Check date contains language-specific formats
        if (strstr($format, "D")) {
            $date = self::dayNameShort($date, $language);
        }
        if (strstr($format, "l")) {
            $date = self::dayName($date, $language);
        }
        if (strstr($format, "F")) {
            $date = self::monthName($date, $language);
        }
        if (strstr($format, "M")) {
            $date = self::monthNameShort($date, $language);
        }

        return $date;
    }

    private function dayName($date, $language)
    {
        if ($language == "nl") {
            $date = str_replace("Monday", "Maandag", $date);
            $date = str_replace("Tuesday", "Dinsdag", $date);
            $date = str_replace("Wednesday", "Woensdag", $date);
            $date = str_replace("Thursday", "Donderdag", $date);
            $date = str_replace("Friday", "Vrijdag", $date);
            $date = str_replace("Saturday", "Zaterdag", $date);
            $date = str_replace("Sunday", "Zondag", $date);
        }
        return $date;
    }

    private function dayNameShort($date, $language)
    {
        if ($language == "nl") {
            $date = str_replace("Mon", "Ma", $date);
            $date = str_replace("Tue", "Di", $date);
            $date = str_replace("Wed", "Wo", $date);
            $date = str_replace("Thu", "Do", $date);
            $date = str_replace("Fri", "Vr", $date);
            $date = str_replace("Sat", "Za", $date);
            $date = str_replace("Sun", "Zo", $date);
        }
        return $date;
    }

    private function monthName($date, $language)
    {
        if ($language == "nl") {
            $date = str_replace("January", "Januari", $date);
            $date = str_replace("February", "Februari", $date);
            $date = str_replace("March", "Maart", $date);
            $date = str_replace("May", "Mei", $date);
            $date = str_replace("June", "Juni", $date);
            $date = str_replace("July", "Juli", $date);
            $date = str_replace("August", "Augustus", $date);
            $date = str_replace("October", "Oktober", $date);
        }
        return $date;
    }

    private function monthNameShort($date, $language)
    {
        if ($language == "nl") {
            $date = str_replace("Mar", "Mrt", $date);
            $date = str_replace("May", "Mei", $date);
            $date = str_replace("Oct", "Okt", $date);
        }
        return $date;
    }
}