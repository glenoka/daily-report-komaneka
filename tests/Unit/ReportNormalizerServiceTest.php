<?php

namespace Tests\Unit;

use App\Services\Normalizer\ReportNormalizerService;
use PHPUnit\Framework\TestCase;

class ReportNormalizerServiceTest extends TestCase
{
    protected ReportNormalizerService $normalizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->normalizer = new ReportNormalizerService();
    }

    public function test_normalizes_standard_bullet_symbols(): void
    {
        $input = <<<TEXT
• AC checked
● Pump checked
○ Pool filter replaced
◦ Garden lamp repaired
▪ Generator tested
▫ Water tank refilled
‣ Wi-Fi router rebooted
⁃ Keycard dispenser cleaned
TEXT;

        $expected = [
            'AC checked',
            'Pump checked',
            'Pool filter replaced',
            'Garden lamp repaired',
            'Generator tested',
            'Water tank refilled',
            'Wi-Fi router rebooted',
            'Keycard dispenser cleaned',
        ];

        $this->assertEquals($expected, $this->normalizer->normalizeToArray($input));
    }

    public function test_normalizes_dashes_and_asterisks_with_whitespace(): void
    {
        $input = <<<TEXT
- Daily briefing conducted
– Lobby AC temperature adjusted
— Chiller inspection finished
* Backup database verified
TEXT;

        $expected = [
            'Daily briefing conducted',
            'Lobby AC temperature adjusted',
            'Chiller inspection finished',
            'Backup database verified',
        ];

        $this->assertEquals($expected, $this->normalizer->normalizeToArray($input));
    }

    public function test_normalizes_numeric_and_alphabetic_markers(): void
    {
        $input = <<<TEXT
1. Villa 105 water heater inspected
2) Villa 106 TV signal fixed
10. Spa sound system configured
1- Kitchen hood filter sanitized
a. Morning patrol clear
b) Afternoon patrol clear
A. Night shift handover complete
TEXT;

        $expected = [
            'Villa 105 water heater inspected',
            'Villa 106 TV signal fixed',
            'Spa sound system configured',
            'Kitchen hood filter sanitized',
            'Morning patrol clear',
            'Afternoon patrol clear',
            'Night shift handover complete',
        ];

        $this->assertEquals($expected, $this->normalizer->normalizeToArray($input));
    }

    public function test_normalizes_checkboxes_and_checkmarks(): void
    {
        $input = <<<TEXT
[ ] Fire extinguisher check scheduled
[x] First aid kit restocked
[X] CCTV cameras 1-16 reviewed
☐ Deep cleaning pool side
☑ Kitchen gas line leak test passed
☒ Chemical storage locked
✓ Emergency exit lights operational
✔ Kitchen refrigerator temp 4°C
TEXT;

        $expected = [
            'Fire extinguisher check scheduled',
            'First aid kit restocked',
            'CCTV cameras 1-16 reviewed',
            'Deep cleaning pool side',
            'Kitchen gas line leak test passed',
            'Chemical storage locked',
            'Emergency exit lights operational',
            'Kitchen refrigerator temp 4°C',
        ];

        $this->assertEquals($expected, $this->normalizer->normalizeToArray($input));
    }

    public function test_normalizes_emoji_list_markers(): void
    {
        $input = <<<TEXT
📌 Staff attendance 100%
🔹 Breakfast buffet turnover high
✅ Sound system tested
👉 VIP arrival greeting prepared
✔️ Room keys ready
TEXT;

        $expected = [
            'Staff attendance 100%',
            'Breakfast buffet turnover high',
            'Sound system tested',
            'VIP arrival greeting prepared',
            'Room keys ready',
        ];

        $this->assertEquals($expected, $this->normalizer->normalizeToArray($input));
    }

    public function test_normalizes_combined_and_nested_markers(): void
    {
        $input = <<<TEXT
• - 1. AC unit serviced in Suite 1
* - Daily report drafted
✓ 1. Routine water testing
• • Double bullet cleaned
TEXT;

        $expected = [
            'AC unit serviced in Suite 1',
            'Daily report drafted',
            'Routine water testing',
            'Double bullet cleaned',
        ];

        $this->assertEquals($expected, $this->normalizer->normalizeToArray($input));
    }

    public function test_preserves_embedded_numbers_and_technical_content(): void
    {
        $input = <<<TEXT
Room 101 has 2 AC units installed
F&B 2nd floor operation starts at 06:00
Villa 202 requested 3 extra pillows and 1 baby cot
Cost estimate is $150.50 (approved by GM)
-5 degrees Celsius in walk-in freezer
TEXT;

        $expected = [
            'Room 101 has 2 AC units installed',
            'F&B 2nd floor operation starts at 06:00',
            'Villa 202 requested 3 extra pillows and 1 baby cot',
            'Cost estimate is $150.50 (approved by GM)',
            '-5 degrees Celsius in walk-in freezer',
        ];

        $this->assertEquals($expected, $this->normalizer->normalizeToArray($input));
    }

    public function test_handles_empty_input_and_blank_lines(): void
    {
        $this->assertSame([], $this->normalizer->normalizeToArray(null));
        $this->assertSame([], $this->normalizer->normalizeToArray(''));
        $this->assertSame([], $this->normalizer->normalizeToArray("   \n\n   \r\n   "));

        $input = "\n\n• Line 1\n\n\n• Line 2\n\n";
        $this->assertEquals(['Line 1', 'Line 2'], $this->normalizer->normalizeToArray($input));
    }

    public function test_normalize_to_clean_text_returns_newline_delimited_string(): void
    {
        $input = "1. Item one\n2. Item two\n• Item three";
        $expected = "Item one\nItem two\nItem three";

        $this->assertEquals($expected, $this->normalizer->normalizeToCleanText($input));
    }
}
