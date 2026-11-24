<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $filename }}</title>
    <style>
        @page {
            margin: 20mm 20mm 20mm 20mm;
            size: a4 portrait;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10.5pt;
            line-height: 1.15;
            color: #1f2937;
            margin: 0;
            padding: 0;
        }

        /* Header banner */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #111827;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }

        .hotel-title {
            font-size: 15pt;
            font-weight: bold;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 2px 0;
        }

        .report-subtitle {
            font-size: 11pt;
            font-weight: bold;
            color: #0f766e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 4px 0;
        }

        .report-date {
            font-size: 9.5pt;
            color: #4b5563;
            margin: 0;
        }

        .greeting {
            font-size: 12pt;
            font-weight: bold;
            color: #000000;
            margin: 14px 0 6px 0;
        }

        .intro {
            font-size: 11pt;
            font-style: italic;
            color: #1f2937;
            margin: 0 0 16px 0;
            line-height: 1.25;
        }

        /* Section structure */
        .section-block {
            margin-bottom: 12px;
            page-break-inside: auto;
        }

        .main-heading {
            font-size: 12pt;
            font-weight: bold;
            color: #0f766e;
            text-transform: uppercase;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 3px;
            margin: 14px 0 6px 0;
            page-break-after: avoid;
        }

        .sub-heading {
            font-size: 11pt;
            font-weight: bold;
            color: #111827;
            margin: 10px 0 4px 0;
            page-break-after: avoid;
        }

        /* Lists */
        ul.bullet-list {
            margin: 4px 0 8px 0;
            padding-left: 20px;
            list-style-type: disc;
        }

        ul.bullet-list li {
            margin-bottom: 3.5px;
            font-size: 10.5pt;
            line-height: 1.25;
            color: #1f2937;
        }

        /* Paragraphs */
        .paragraph-text {
            font-size: 10.5pt;
            line-height: 1.35;
            color: #1f2937;
            margin: 4px 0 8px 0;
            white-space: pre-line;
        }

        .empty-placeholder {
            font-size: 10pt;
            font-style: italic;
            color: #6b7280;
            margin: 3px 0 6px 0;
        }
    </style>
</head>
<body>

    <!-- 5 Standard Sections (Report Body Begins Directly) -->
    @foreach ($data['tabs'] as $tIndex => $tab)
        <div class="section-block">
            <div class="main-heading">{{ $tIndex + 1 }}. {{ strtoupper($tab['title']) }}</div>

            @foreach ($tab['sections'] as $sec)
                @if (count($tab['sections']) > 1 || $tab['key'] === 'departmental_activities')
                    <div class="sub-heading">{{ $sec['title'] }}</div>
                @endif

                @if ($sec['field_type'] === 'text')
                    @if (!$sec['is_empty'] && !empty($sec['content']))
                        <div class="paragraph-text">{{ $sec['content'] }}</div>
                    @else
                        <div class="empty-placeholder">(No guest experience or concern reported)</div>
                    @endif
                @else
                    @if (!$sec['is_empty'] && !empty($sec['items']))
                        <ul class="bullet-list">
                            @foreach ($sec['items'] as $item)
                                @php
                                    $cleanText = preg_replace('/^[•●○◦▪▫‣⁃\-\*]\s*/u', '', trim($item));
                                @endphp
                                <li>{{ $cleanText }}</li>
                            @endforeach
                        </ul>
                    @else
                        <div class="empty-placeholder">(No items reported)</div>
                    @endif
                @endif
            @endforeach
        </div>
    @endforeach

</body>
</html>
