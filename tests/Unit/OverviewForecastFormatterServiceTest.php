<?php

namespace Tests\Unit;

use App\Services\OverviewForecastFormatterService;
use Tests\TestCase;

class OverviewForecastFormatterServiceTest extends TestCase
{
    protected OverviewForecastFormatterService $formatter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formatter = new OverviewForecastFormatterService();
    }

    public function test_parses_room_list_from_various_input_formats(): void
    {
        // Comma separated with leading hashes
        $rooms1 = $this->formatter->parseRoomList('#2310, #2321, 3311');
        $this->assertEquals(['2310', '2321', '3311'], $rooms1);

        // Mixed with ampersand and extra whitespace
        $rooms2 = $this->formatter->parseRoomList(' 2311 & #4103 ');
        $this->assertEquals(['2311', '4103'], $rooms2);

        // Newline separated
        $rooms3 = $this->formatter->parseRoomList("101\n102\n103");
        $this->assertEquals(['101', '102', '103'], $rooms3);

        // Empty or null
        $this->assertEquals([], $this->formatter->parseRoomList(null));
        $this->assertEquals([], $this->formatter->parseRoomList(''));
    }

    public function test_formats_room_list_with_correct_separators(): void
    {
        // 0 rooms
        $this->assertEquals('', $this->formatter->formatRoomList([]));

        // 1 room
        $this->assertEquals('#2310', $this->formatter->formatRoomList(['2310']));

        // 2 rooms (joined with &)
        $this->assertEquals('#2311 & #4103', $this->formatter->formatRoomList(['2311', '4103']));

        // 3+ rooms (commas + & before last)
        $this->assertEquals('#2310, #2321 & #3311', $this->formatter->formatRoomList(['2310', '2321', '3311']));
        $this->assertEquals('#1, #2, #3 & #4', $this->formatter->formatRoomList(['1', '2', '3', '4']));
    }

    public function test_formats_todays_overview_matches_specification_example(): void
    {
        $input = [
            'occupancy' => 20.00,
            'rooms_occupied' => 15,
            'ooo_rooms' => '2310, 2321, 3311',
            'ooo_reason' => 'Preventive Maintenance Program',
            'guests_adults' => 31,
            'guests_children' => 2,
            'check_ins' => 7,
            'check_outs' => 7,
            'property_tours' => '2311, 4103',
            'manager_on_duty' => 'Ariawan, Agung Janardana, Laksmi',
        ];

        $lines = $this->formatter->formatTodaysOverview($input);

        $expected = [
            'Occupancy: 20.00%',
            'Rooms Occupied: 15 Rooms',
            'Out of Order: 3 Rooms — #2310, #2321 & #3311 (Preventive Maintenance Program)',
            'Number of Guests: 33 Pax (31 Adults + 2 Children)',
            'Check-ins: 7 Rooms',
            'Check-outs: 7 Rooms',
            'Property Tours: Room #2311 & #4103',
            'Manager on Duty: Ariawan, Agung Janardana, Laksmi',
        ];

        $this->assertEquals($expected, $lines);
    }

    public function test_handles_singular_units_and_omits_empty_optional_fields(): void
    {
        $input = [
            'occupancy' => 5,
            'rooms_occupied' => 1,
            'ooo_rooms' => '101',
            'ooo_reason' => null,
            'guests_adults' => 1,
            'guests_children' => 1,
            'property_tours' => '105',
        ];

        $lines = $this->formatter->formatTodaysOverview($input);

        $this->assertContains('Occupancy: 5.00%', $lines);
        $this->assertContains('Rooms Occupied: 1 Rooms', $lines);
        $this->assertContains('Out of Order: 1 Room — #101', $lines);
        $this->assertContains('Number of Guests: 2 Pax (1 Adult + 1 Child)', $lines);
        $this->assertContains('Property Tours: Room #105', $lines);

        // Check-ins, Check-outs, Manager on Duty omitted because empty
        $this->assertStringNotContainsString('Check-ins', implode("\n", $lines));
        $this->assertStringNotContainsString('Check-outs', implode("\n", $lines));
        $this->assertStringNotContainsString('Manager on Duty', implode("\n", $lines));
    }

    public function test_omits_out_of_order_line_when_no_rooms_entered(): void
    {
        $input = [
            'occupancy' => 20,
            'ooo_rooms' => '',
            'ooo_reason' => 'Some Reason',
        ];

        $lines = $this->formatter->formatTodaysOverview($input);

        // Must NEVER output "Out of Order: 0 Rooms"
        $this->assertStringNotContainsString('Out of Order', implode("\n", $lines));
    }

    public function test_formats_forecast_for_tomorrow_matches_specification_example(): void
    {
        $input = [
            'projected_occupancy' => 16.00,
            'rooms_projected' => 12,
            'ooo_rooms' => '2310, 2321, 3311',
            'ooo_reason' => 'Preventive Maintenance Program',
        ];

        $lines = $this->formatter->formatForecastTomorrow($input);

        $expected = [
            'Projected Occupancy: 16.00%',
            'Rooms Projected: 12 Rooms',
            'Out of Order: 3 Rooms — #2310, #2321 & #3311 (Preventive Maintenance Program)',
        ];

        $this->assertEquals($expected, $lines);
    }
}
