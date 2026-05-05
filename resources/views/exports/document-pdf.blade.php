<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $document->title ?: 'Untitled document' }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #172554;
            margin: 0;
            padding: 36px 42px;
            font-size: 12px;
            line-height: 1.6;
        }

        .meta {
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #dbeafe;
        }

        .eyebrow {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #2563eb;
            margin-bottom: 12px;
        }

        h1 {
            margin: 0 0 8px 0;
            font-size: 28px;
            line-height: 1.1;
            color: #0f172a;
        }

        .owner,
        .updated {
            color: #475569;
            font-size: 11px;
            margin: 0;
        }

        .content {
            color: #0f172a;
        }

        .content h1,
        .content h2,
        .content h3,
        .content h4 {
            color: #0f172a;
            margin-top: 20px;
        }

        .content p {
            margin: 0 0 12px 0;
        }

        .content ul,
        .content ol {
            margin: 0 0 14px 18px;
        }
    </style>
</head>
<body>
    <div class="meta">
        <div class="eyebrow">GoodDocs export</div>
        <h1>{{ $document->title ?: 'Untitled document' }}</h1>
        @if ($ownerName)
            <p class="owner">Owner: {{ $ownerName }}</p>
        @endif
        <p class="updated">Last updated: {{ optional($document->updated_at)->format('F j, Y g:i A') }}</p>
    </div>

    <div class="content">
        {!! $document->content ?: '<p></p>' !!}
    </div>
</body>
</html>
