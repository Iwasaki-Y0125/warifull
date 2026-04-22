function formatDate(date) {
    // Date -> YYYY-MM-DD（サーバー送信用）
    const year = String(date.getFullYear());
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

function formatMonthLabel(date) {
    // モーダル見出し用（例: 2026年4月）
    return `${date.getFullYear()}年${date.getMonth() + 1}月`;
}

function formatMonthDay(dateString) {
    // 表示用（例: 2026-04-28 -> 4/28）
    const [year, month, day] = dateString.split('-').map(Number);

    return `${month}/${day}`;
}

function toMonthStart(dateString) {
    // 月移動計算を簡単にするため、必ず1日へ丸める。
    const [year, month] = dateString.split('-').map(Number);

    return new Date(year, month - 1, 1);
}

export function createVacationCalendar({
    monthLabelElement,
    daysContainerElement,
    prevButtonElement,
    nextButtonElement,
    today,
    onSelectionChange,
}) {
    const todayString = today;
    const todayDate = toMonthStart(todayString);

    // currentMonth: 今表示している月
    // selectedDates: モーダル全体で保持する選択状態（Setで重複防止）
    // 月移動しても selectedDates は破棄しない。
    let currentMonth = toMonthStart(todayString);
    let selectedDates = new Set();

    const sortedSelectedDates = () => Array.from(selectedDates).sort();

    const emitSelectionChange = () => {
        // 親モジュールへ「現在の選択状態」を通知してUI/hidden inputを同期。
        onSelectionChange(sortedSelectedDates());
    };

    // 文字列比較で YYYY-MM-DD の前後判定ができる前提を使う。
    const isPastDate = (dateString) => dateString < todayString;

    const render = () => {
        // 1. ヘッダー月表示を更新
        // 2. その月の日付セルを全再描画
        monthLabelElement.textContent = formatMonthLabel(currentMonth);
        daysContainerElement.replaceChildren();

        const firstDay = new Date(currentMonth.getFullYear(), currentMonth.getMonth(), 1);
        const firstWeekday = firstDay.getDay();
        const daysInMonth = new Date(currentMonth.getFullYear(), currentMonth.getMonth() + 1, 0).getDate();

        for (let i = 0; i < firstWeekday; i += 1) {
            // 1日目までの空白セル（日曜始まり）を埋める。
            const spacer = document.createElement('div');
            daysContainerElement.appendChild(spacer);
        }

        for (let day = 1; day <= daysInMonth; day += 1) {
            const date = new Date(currentMonth.getFullYear(), currentMonth.getMonth(), day);
            const dateString = formatDate(date);
            const selected = selectedDates.has(dateString);
            const pastDate = isPastDate(dateString);

            const button = document.createElement('button');
            button.type = 'button';
            button.dataset.date = dateString;
            button.textContent = String(day);
            button.className =
                'h-14 rounded-xl border text-lg font-semibold transition ' +
                (selected
                    ? 'border-blue-600 bg-blue-600 text-white'
                    : pastDate
                      ? 'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400'
                      : 'border-slate-300 bg-white text-slate-800 hover:bg-slate-50');

            button.disabled = pastDate;

            if (!pastDate) {
                button.addEventListener('click', () => {
                    // 再クリックで選択/非選択をトグルする。
                    if (selectedDates.has(dateString)) {
                        selectedDates.delete(dateString);
                    } else {
                        selectedDates.add(dateString);
                    }

                    render();
                    emitSelectionChange();
                });
            }

            daysContainerElement.appendChild(button);
        }
    };

    prevButtonElement.addEventListener('click', () => {
        // 前月へ移動して再描画。
        currentMonth = new Date(currentMonth.getFullYear(), currentMonth.getMonth() - 1, 1);
        render();
    });

    nextButtonElement.addEventListener('click', () => {
        // 次月へ移動して再描画。
        currentMonth = new Date(currentMonth.getFullYear(), currentMonth.getMonth() + 1, 1);
        render();
    });

    return {
        setSelectedDates(dateStrings) {
            // 初期値は過去日を除外してからセットする。
            selectedDates = new Set(
                (dateStrings ?? [])
                    .filter((dateString) => typeof dateString === 'string' && dateString !== '')
                    .filter((dateString) => !isPastDate(dateString))
            );

            const firstSelected = sortedSelectedDates()[0];
            // 既存選択がある場合は最初の選択月を表示開始月にする。
            // ない場合は当月表示で開始する。
            currentMonth = firstSelected ? toMonthStart(firstSelected) : todayDate;

            render();
            emitSelectionChange();
        },
        getSelectedDates() {
            return sortedSelectedDates();
        },
        formatMonthDay,
    };
}
