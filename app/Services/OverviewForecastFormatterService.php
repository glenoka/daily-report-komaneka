<?php

namespace App\Services;

class OverviewForecastFormatterService
{
    /**
     * Parse raw room input into a clean array of room identifier strings (without '#').
     *
     * @param string|array|null $input
     * @return array<int, string>
     */
    public function parseRoomList(string|array|null $input): array
    {
        if (empty($input)) {
            return [];
        }

        if (is_array($input)) {
            $rawItems = $input;
        } else {
            // Replace ampersands and newlines with commas
            $normalized = str_replace(['&', "\r\n", "\r", "\n"], ',', $input);
            $rawItems = explode(',', $normalized);
        }

        $cleaned = [];
        foreach ($rawItems as $item) {
            $trimmed = trim((string) $item);
            if ($trimmed === '') {
                continue;
            }

            // Strip leading '#' and whitespace
            $roomNo = ltrim($trimmed, "# \t\n\r\0\x0B");
            if ($roomNo !== '') {
                $cleaned[] = $roomNo;
            }
        }

        return array_values(array_unique($cleaned));
    }

    /**
     * Format a list of room numbers with '#' prefix and proper commas / '&' separators.
     * Examples:
     * - ['2310'] => '#2310'
     * - ['2311', '4103'] => '#2311 & #4103'
     * - ['2310', '2321', '3311'] => '#2310, #2321 & #3311'
     *
     * @param array<int, string>|string|null $rooms
     * @return string
     */
    public function formatRoomList(array|string|null $rooms): string
    {
        $parsed = is_array($rooms) ? $rooms : $this->parseRoomList($rooms);
        if (empty($parsed)) {
            return '';
        }

        $prefixed = array_map(fn($r) => '#' . ltrim($r, '#'), $parsed);
        $count = count($prefixed);

        if ($count === 1) {
            return $prefixed[0];
        }

        if ($count === 2) {
            return $prefixed[0] . ' & ' . $prefixed[1];
        }

        $last = array_pop($prefixed);
        return implode(', ', $prefixed) . ' & ' . $last;
    }

    /**
     * Format structured Today's Overview data into an array of clean report lines.
     *
     * @param array<string, mixed> $data
     * @return array<int, string>
     */
    public function formatTodaysOverview(array $data): array
    {
        $lines = [];

        // 1. Occupancy: 20.00%
        if (isset($data['occupancy']) && $data['occupancy'] !== '' && $data['occupancy'] !== null) {
            $val = number_format((float) $data['occupancy'], 2, '.', '');
            $lines[] = "Occupancy: {$val}%";
        }

        // 2. Rooms Occupied: 15 Rooms
        if (isset($data['rooms_occupied']) && $data['rooms_occupied'] !== '' && $data['rooms_occupied'] !== null) {
            $val = (int) $data['rooms_occupied'];
            $lines[] = "Rooms Occupied: {$val} Rooms";
        }

        // 3. Out of Order: 3 Rooms — #2310, #2321 & #3311 (Preventive Maintenance Program)
        $oooRooms = $this->parseRoomList($data['ooo_rooms'] ?? null);
        if (!empty($oooRooms)) {
            $count = count($oooRooms);
            $roomLabel = $count === 1 ? '1 Room' : "{$count} Rooms";
            $formattedRooms = $this->formatRoomList($oooRooms);
            $reason = !empty($data['ooo_reason']) ? ' (' . trim($data['ooo_reason']) . ')' : '';
            $lines[] = "Out of Order: {$roomLabel} — {$formattedRooms}{$reason}";
        }

        // 4. Number of Guests: 33 Pax (31 Adults + 2 Children)
        $hasAdults = isset($data['guests_adults']) && $data['guests_adults'] !== '' && $data['guests_adults'] !== null;
        $hasChildren = isset($data['guests_children']) && $data['guests_children'] !== '' && $data['guests_children'] !== null;

        if ($hasAdults || $hasChildren) {
            $adults = $hasAdults ? (int) $data['guests_adults'] : 0;
            $children = $hasChildren ? (int) $data['guests_children'] : 0;
            $total = $adults + $children;

            $adultText = $adults === 1 ? '1 Adult' : "{$adults} Adults";
            $childText = $children === 1 ? '1 Child' : "{$children} Children";

            $lines[] = "Number of Guests: {$total} Pax ({$adultText} + {$childText})";
        }

        // 5. Check-ins: 7 Rooms
        if (isset($data['check_ins']) && $data['check_ins'] !== '' && $data['check_ins'] !== null) {
            $val = (int) $data['check_ins'];
            $lines[] = "Check-ins: {$val} Rooms";
        }

        // 6. Check-outs: 7 Rooms
        if (isset($data['check_outs']) && $data['check_outs'] !== '' && $data['check_outs'] !== null) {
            $val = (int) $data['check_outs'];
            $lines[] = "Check-outs: {$val} Rooms";
        }

        // 7. Property Tours: Room #2311 & #4103
        $tourRooms = $this->parseRoomList($data['property_tours'] ?? null);
        if (!empty($tourRooms)) {
            $formattedTours = $this->formatRoomList($tourRooms);
            $lines[] = "Property Tours: Room {$formattedTours}";
        }

        // 8. Manager on Duty: Ariawan, Agung Janardana, Laksmi
        if (!empty($data['manager_on_duty'])) {
            $modRaw = trim((string) $data['manager_on_duty']);
            $names = array_filter(array_map('trim', explode(',', $modRaw)));
            if (!empty($names)) {
                $lines[] = 'Manager on Duty: ' . implode(', ', $names);
            }
        }

        return $lines;
    }

    /**
     * Format structured Forecast for Tomorrow data into an array of clean report lines.
     *
     * @param array<string, mixed> $data
     * @return array<int, string>
     */
    public function formatForecastTomorrow(array $data): array
    {
        $lines = [];

        // 1. Projected Occupancy: 16.00%
        if (isset($data['projected_occupancy']) && $data['projected_occupancy'] !== '' && $data['projected_occupancy'] !== null) {
            $val = number_format((float) $data['projected_occupancy'], 2, '.', '');
            $lines[] = "Projected Occupancy: {$val}%";
        }

        // 2. Rooms Projected: 12 Rooms
        if (isset($data['rooms_projected']) && $data['rooms_projected'] !== '' && $data['rooms_projected'] !== null) {
            $val = (int) $data['rooms_projected'];
            $lines[] = "Rooms Projected: {$val} Rooms";
        }

        // 3. Out of Order: 3 Rooms — #2310, #2321 & #3311 (Preventive Maintenance Program)
        $oooRooms = $this->parseRoomList($data['ooo_rooms'] ?? null);
        if (!empty($oooRooms)) {
            $count = count($oooRooms);
            $roomLabel = $count === 1 ? '1 Room' : "{$count} Rooms";
            $formattedRooms = $this->formatRoomList($oooRooms);
            $reason = !empty($data['ooo_reason']) ? ' (' . trim($data['ooo_reason']) . ')' : '';
            $lines[] = "Out of Order: {$roomLabel} — {$formattedRooms}{$reason}";
        }

        // 4. Optional Check-ins / Expected Arrivals
        if (isset($data['expected_check_ins']) && $data['expected_check_ins'] !== '' && $data['expected_check_ins'] !== null) {
            $val = (int) $data['expected_check_ins'];
            $lines[] = "Check-ins: {$val} Rooms";
        }

        // 5. Optional Check-outs / Expected Departures
        if (isset($data['expected_check_outs']) && $data['expected_check_outs'] !== '' && $data['expected_check_outs'] !== null) {
            $val = (int) $data['expected_check_outs'];
            $lines[] = "Check-outs: {$val} Rooms";
        }

        return $lines;
    }

    /**
     * Return formatted Today's Overview as a newline-delimited string.
     */
    public function formatTodaysOverviewText(array $data): string
    {
        return implode("\n", $this->formatTodaysOverview($data));
    }

    /**
     * Return formatted Forecast for Tomorrow as a newline-delimited string.
     */
    public function formatForecastTomorrowText(array $data): string
    {
        return implode("\n", $this->formatForecastTomorrow($data));
    }
}
