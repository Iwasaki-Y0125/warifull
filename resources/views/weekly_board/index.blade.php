<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>ワリフル | 週次業務ボード</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#f3f5f9] text-slate-900">
        @php
            $members = $members ?? collect();
            $orderedWeekdays = [1, 2, 3, 4, 5];
            $weekdayTabs = $weekdayTabs ?? [];
            $activeWeekday = $activeWeekday ?? 1;
        @endphp

        <header class="border-b border-slate-200 bg-white px-6 py-4">
            <div class="mx-auto flex max-w-7xl items-center gap-3">
                <div class="grid h-11 w-11 place-items-center rounded-xl bg-blue-600 text-xl text-white">🗓️</div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">ワリフル</h1>
                    <p class="text-sm text-slate-600">有給時の自動再割り当てシステム</p>
                </div>
            </div>
        </header>

        <main class="mx-auto grid max-w-7xl grid-cols-1 gap-6 p-6 lg:grid-cols-[290px_1fr]">
            <aside class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold">チームメンバー</h2>

                <div class="mt-4 space-y-3">
                    @forelse ($members as $member)
                        <article class="rounded-2xl border border-blue-200 bg-blue-50/60 p-4">
                            <div class="flex items-center gap-3">
                                <div class="text-2xl">🧑‍💼</div>
                                <p class="text-lg font-semibold">{{ $member['name'] ?? '' }}</p>
                            </div>
                            <p class="mt-2 text-sm text-blue-700">出勤中</p>
                            <p class="mt-1 text-sm text-slate-600">
                                直近の有給予定:
                                {{ ! empty($member['upcoming_vacation_dates']) ? implode(', ', $member['upcoming_vacation_dates']) : 'なし' }}
                            </p>
                        </article>
                    @empty
                        <p class="rounded-lg border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500">
                            表示対象のメンバーはありません。
                        </p>
                    @endforelse
                </div>
            </aside>

            <section class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl border border-slate-300 bg-white text-3xl text-slate-500 shadow-sm"
                        disabled
                    >
                        ‹
                    </button>

                    <div class="grid flex-1 grid-cols-5 gap-2 rounded-2xl border border-slate-200 bg-slate-50 p-2 shadow-inner">
                        @foreach ($orderedWeekdays as $weekday)
                            @php
                                $isActive = $activeWeekday === $weekday;
                                $weekdayLabel = $weekdayTabs[$weekday]['label'] ?? '';
                                $weekdayDate = $weekdayTabs[$weekday]['date'] ?? '';
                            @endphp

                            <div class="{{ $isActive ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-700' }} rounded-xl px-3 py-2 text-center">
                                <p class="text-sm font-semibold {{ $isActive ? 'text-blue-100' : 'text-slate-500' }}">{{ $weekdayLabel }}</p>
                                <p class="mt-1 text-2xl font-bold">{{ $weekdayDate }}</p>
                            </div>
                        @endforeach
                    </div>

                    <button
                        type="button"
                        class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl border border-slate-300 bg-white text-3xl text-slate-500 shadow-sm"
                        disabled
                    >
                        ›
                    </button>
                </div>

                <div class="space-y-5">
                    @foreach ($orderedWeekdays as $weekday)
                        @php
                            $tasks = $weeklyTasksByWeekday->get($weekday, collect());
                        @endphp

                        <section>
                            <h3 class="mb-2 text-sm font-semibold text-slate-500">
                                {{ $weekdayTabs[$weekday]['label'] ?? '' }}曜日
                            </h3>

                            <div class="space-y-3">
                                @forelse ($tasks as $task)
                                    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                        <p class="text-lg font-semibold">{{ $task['name'] }}</p>
                                        <p class="mt-1 text-sm text-slate-600">{{ $task['description'] }}</p>

                                        <div class="mt-4 flex items-center justify-between gap-3">
                                            <div class="grid gap-1 text-sm text-slate-700">
                                                <p>開始時刻: {{ \Illuminate\Support\Str::of($task['start_time'])->substr(0, 5) }}</p>
                                            </div>
                                            <div class="inline-flex min-h-16 items-center gap-4 rounded-2xl bg-blue-50 px-4 py-4 text-lg font-semibold leading-relaxed text-blue-900 whitespace-nowrap">
                                                <span class="text-lg leading-none">🧑‍💼</span>
                                                <span>{{ $task['main_owner_name'] ?? '未設定' }}</span>
                                            </div>
                                        </div>
                                    </article>
                                @empty
                                    <p class="rounded-lg border border-dashed border-slate-300 bg-slate-50 p-3 text-sm text-slate-500">
                                        この曜日のタスクはありません。
                                    </p>
                                @endforelse
                            </div>
                        </section>
                    @endforeach
                </div>
            </section>
        </main>
    </body>
</html>
