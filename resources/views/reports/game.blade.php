<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $quiz->title }} — Report</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; color: #1f2937; margin: 0; padding: 32px; }
        h1 { font-size: 22px; margin: 0 0 4px; }
        h2 { font-size: 15px; margin: 28px 0 10px; border-bottom: 2px solid #e5e7eb; padding-bottom: 6px; }
        .muted { color: #6b7280; font-size: 12px; }
        .summary { display: flex; gap: 16px; margin-top: 16px; }
        .card { flex: 1; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 16px; }
        .card .label { font-size: 11px; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; }
        .card .value { font-size: 22px; font-weight: 700; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th, td { text-align: left; padding: 8px 10px; border-bottom: 1px solid #e5e7eb; }
        th { background: #f9fafb; font-size: 11px; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; }
        td.num, th.num { text-align: right; }
        .bar { background: #e5e7eb; border-radius: 4px; height: 8px; overflow: hidden; width: 120px; }
        .bar > span { display: block; height: 100%; background: #22c55e; }
        .print-hint { margin-top: 28px; }
        @media print {
            body { padding: 0; }
            .print-hint { display: none; }
        }
        .btn { display: inline-block; background: #4f46e5; color: #fff; text-decoration: none; padding: 10px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h1>{{ $quiz->title }}</h1>
    <p class="muted">
        Game code: {{ $gameSession->game_code }}
        @if ($gameSession->finished_at)
            &middot; {{ $gameSession->finished_at->toDayDateTimeString() }}
        @endif
    </p>

    <div class="summary">
        <div class="card"><div class="label">Players</div><div class="value">{{ $playerCount }}</div></div>
        <div class="card"><div class="label">Questions</div><div class="value">{{ $totalQuestions }}</div></div>
        <div class="card"><div class="label">Winner</div><div class="value">{{ $leaderboard->first()['nickname'] ?? '—' }}</div></div>
    </div>

    <h2>Leaderboard</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Player</th>
                <th class="num">Correct</th>
                <th class="num">Best streak</th>
                <th class="num">Score</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($leaderboard as $entry)
                <tr>
                    <td>{{ $entry['rank'] }}</td>
                    <td>{{ $entry['nickname'] }}</td>
                    <td class="num">{{ $entry['correct_count'] }} / {{ $totalQuestions }}</td>
                    <td class="num">{{ $entry['best_streak'] }}</td>
                    <td class="num">{{ $entry['score'] }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">No players.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Question Analysis</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Question</th>
                <th class="num">Correct</th>
                <th class="num">Wrong</th>
                <th class="num">No answer</th>
                <th>Correct rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($questions as $idx => $q)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ $q['question_text'] }}</td>
                    <td class="num">{{ $q['correct_count'] }}</td>
                    <td class="num">{{ $q['incorrect_count'] }}</td>
                    <td class="num">{{ $q['no_answer_count'] }}</td>
                    <td>
                        <div class="bar"><span style="width: {{ $q['correct_pct'] }}%"></span></div>
                        <span class="muted">{{ $q['correct_pct'] }}%</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="print-hint">
        <button class="btn" onclick="window.print()">Print / Save as PDF</button>
    </div>
</body>
</html>
