// =========================
// 日付フォーマット
// =========================
// Date -> YYYY-MM-DD（サーバー送信用） *JSは月だけ0始まり　例）0 = 1月
function formatDate(date) {
    const year = String(date.getFullYear());
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

// モーダル見出し用（例: 2026年4月）
function formatMonthLabel(date) {
    return `${date.getFullYear()}年${date.getMonth() + 1}月`;
}

// 表示用（例: 2026-04-28 -> 4/28）
function formatMonthDay(dateString) {
    const [year, month, day] = dateString.split('-').map(Number);

    return `${month}/${day}`;
}

// 月移動計算を簡単にするため、必ず*月1日へ丸める。
function toMonthStart(dateString) {
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
    // =========================
    // カレンダー要素
    // =========================
    const todayString = today;
    const todayDate = toMonthStart(todayString);

    // currentMonth: 今表示している月
    // selectedDates: 選択中の有給取得日
    let currentMonth = toMonthStart(todayString);
    let selectedDates = new Set();

    // 【selectedDatesについて補足】
    // 型Set()取得することで、同じに値が入らない（重複防止、もう一度クリックすると削除する挙動になる）
    // 合わせて、Set()で保持することで月移動しても selectedDates は破棄しない。

    // =========================
    // 選択状態の連携
    // =========================
    const sortedSelectedDates = () => Array.from(selectedDates).sort();

    const emitSelectionChange = () => {
        // 親モジュール（weekly-board-vacation-modal.js)へ
        // 「現在の選択状態」を通知して現在の画面に選択した日付を同期。
        onSelectionChange(sortedSelectedDates());
    };

    // 過去日かどうか判定
    const isPastDate = (dateString) => dateString < todayString;

    // =========================
    // 状態更新
    // =========================
    // 再クリックで選択/非選択をトグルする。
    const toggleSelectedDate = (dateString) => {
        if (selectedDates.has(dateString)) {
            selectedDates.delete(dateString);
        } else {
            selectedDates.add(dateString);
        }
    };

    // 再描画と選択状態の通知をまとめる。
    const rerenderAndSync = () => {
        render();
        emitSelectionChange();
    };

    // =========================
    // 描画準備
    // =========================
    // カレンダーに必要な「1日の曜日」と「末日が何日か」を返す。
    const getMonthMeta = () => {
        // その月の1日の日付オブジェクトを作成
        const firstDay = new Date(currentMonth.getFullYear(), currentMonth.getMonth(), 1);

        // getDay() で曜日を取得（0:日 / 1:月 / 2:火 / 3:水 / 4:木 / 5:金 / 6:土）
        const firstWeekday = firstDay.getDay();

        // new Date(年, 次の月, 0日) で「今月の末日」を取得
        const lastDayOfMonth = new Date(currentMonth.getFullYear(), currentMonth.getMonth() + 1, 0).getDate();

        return { firstWeekday, lastDayOfMonth };
    };

    // 月初の曜日ぶんだけ「空マス」を先頭に入れる。
    const renderLeadingSpacers = (firstWeekday) => {
        // 例: 1日が水曜（3）なら、日・月・火の3マス分の空白を先に入れる
        for (let i = 0; i < firstWeekday; i += 1) {
            const spacer = document.createElement('div');
            daysContainerElement.appendChild(spacer);
        }
    };

    // 日付ボタンを生成
    const createDayButton = ({ day, dateString, selected, pastDate }) => {
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

        // 過去日ならボタンは不活性化
        button.disabled = pastDate;

        if (!pastDate) {
            button.addEventListener('click', () => {
                toggleSelectedDate(dateString);
                rerenderAndSync();
            });
        }

        return button;
    };

    // =========================
    // 描画
    // =========================
    // 当月の日付ボタンを描画する。
    const renderDayButtons = (lastDayOfMonth) => {
        // その月の末尾まで日付オブジェクトを生成する
        for (let day = 1; day <= lastDayOfMonth; day += 1) {
            const date = new Date(currentMonth.getFullYear(), currentMonth.getMonth(), day);
            const dateString = formatDate(date);
            const selected = selectedDates.has(dateString);
            const pastDate = isPastDate(dateString);
            const button = createDayButton({ day, dateString, selected, pastDate });

            daysContainerElement.appendChild(button);
        }
    };

    // カレンダー生成
    const render = () => {
        // ヘッダー月表示を更新
        monthLabelElement.textContent = formatMonthLabel(currentMonth);
        // 子要素を全消しして更地にする（初期化）
        daysContainerElement.replaceChildren();

        const { firstWeekday, lastDayOfMonth } = getMonthMeta();
        renderLeadingSpacers(firstWeekday);
        renderDayButtons(lastDayOfMonth);
    };

    // 「＜」が押されたら、前月へ移動して再描画。
    prevButtonElement.addEventListener('click', () => {
        currentMonth = new Date(currentMonth.getFullYear(), currentMonth.getMonth() - 1, 1);
        render();
    });

    // 「＞」が押されたら、次月へ移動して再描画。
    nextButtonElement.addEventListener('click', () => {
        currentMonth = new Date(currentMonth.getFullYear(), currentMonth.getMonth() + 1, 1);
        render();
    });

    // カレンダーの有給日を返す
    return {
        setSelectedDates(dateStrings) {
            // 初期値は過去日を除外してからセットする。
            selectedDates = new Set((dateStrings ?? []).filter((dateString) => !isPastDate(dateString)));

            const firstSelected = sortedSelectedDates()[0];
            // 既存選択がある場合は最初の選択月を表示開始月にする。
            // ない場合は当月表示で開始する。
            currentMonth = firstSelected ? toMonthStart(firstSelected) : todayDate;

            // カレンダー描写
            render();
            // 有給日を同期
            emitSelectionChange();
        },
        // 今の選択日をソート済み配列で返す
        getSelectedDates() {
            return sortedSelectedDates();
        },
        // 表示用フォーマッタ関数でそのまま外に公開　YYYY-MM-DD -> M/D
        formatMonthDay,
    };
}
