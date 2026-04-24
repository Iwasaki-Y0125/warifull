import { createVacationCalendar } from './weekly-board-vacation-calendar';
import { bindBackdropCloseByTarget, closeDialogById, openDialogById } from './modal-manager';

// =========================
// モーダル共通実装ルール（個別モーダル側）
// =========================
// 1. data属性の読み取り、フォームaction/hidden反映はこのファイルで担当する。
// 2. カレンダーなどの画面部品は初期化のみ行い、内部描画ロジックは専用ファイルへ委譲する。
// 3. モーダルの開閉自体は modal-manager.js の共通関数を必ず経由する。

// =========================
// データ変換
// =========================
// 有給日を配列に変換する
function parseVacationDates(rawValue) {
    // data属性から渡る文字列を配列へ変換する。
    // 空文字は空配列として扱い、不正JSONは例外で検知する。
    if (!rawValue) {
        return [];
    }

    try {
        const parsed = JSON.parse(rawValue);

        return Array.isArray(parsed) ? parsed : [];
    } catch {
        throw new Error('[weekly-board-vacation-modal] data-vacation-dates must be valid JSON');
    }
}

// =========================
// 要素取得・検証
// =========================
// モーダル関連のDOM要素をまとめて取得する
function getVacationModalElements() {
    return {
        form: document.getElementById('vacation-modal-form'),
        routeTemplateInput: document.getElementById('vacation-modal-route-template'),
        todayInput: document.getElementById('vacation-modal-today'),
        memberIdInput: document.getElementById('vacation-modal-member-id'),
        memberNameElement: document.getElementById('vacation-modal-member-name'),
        monthLabelElement: document.getElementById('vacation-modal-month-label'),
        daysContainerElement: document.getElementById('vacation-modal-calendar-days'),
        selectedDatesElement: document.getElementById('vacation-modal-selected-dates'),
        hiddenInputsElement: document.getElementById('vacation-modal-hidden-inputs'),
        prevButtonElement: document.getElementById('vacation-modal-prev-month'),
        nextButtonElement: document.getElementById('vacation-modal-next-month'),
        cancelButtonElement: document.querySelector('[data-modal-close="vacation-modal"]'),
    };
}

// 必須DOM要素の不足有無を判定する
function hasMissingElements(elements) {
    return Object.values(elements).some((element) => element === null);
}

// =========================
// 表示同期
// =========================
// 選択中の有給日表示とhidden inputを同期する関数を作る
function createVacationInfoSyncer({ hiddenInputsElement, selectedDatesElement, formatMonthDay }) {
    // 1. selectedDates を hidden input に同期
    // 2. 「選択中の有給日」表示も同じデータで更新
    // 3. 画面表示と送信値のズレを防ぐ
    return (selectedDates) => {
        hiddenInputsElement.replaceChildren();

        selectedDates.forEach((dateString) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'vacation_dates[]';
            input.value = dateString;
            hiddenInputsElement.appendChild(input);
        });

        selectedDatesElement.textContent =
            selectedDates.length > 0 ? selectedDates.map((dateString) => formatMonthDay(dateString)).join(', ') : '有給予定なし';
    };
}

// =========================
// 開閉イベント登録
// =========================
// 有給追加ボタンに「開く」イベントを登録する
function bindOpenModalEvents(openModalForMember) {
    document.querySelectorAll('.js-open-vacation-modal').forEach((buttonElement) => {
        buttonElement.addEventListener('click', () => {
            openModalForMember(buttonElement);
        });
    });
}

// キャンセルボタン・backdropに「閉じる」イベントを登録する
function bindCloseModalEvents(cancelButtonElement) {
    cancelButtonElement.addEventListener('click', () => {
        closeDialogById('vacation-modal');
    });

    bindBackdropCloseByTarget('vacation-modal');
}

// =========================
// 初期化フロー
// =========================
// モーダル制御に必要な要素をまとめて取得する。
export function initVacationModal() {
    const elements = getVacationModalElements();
    if (hasMissingElements(elements)) {
        return;
    }

    let formatMonthDay = (dateString) => dateString;
    const syncVacationInfo = createVacationInfoSyncer({
        hiddenInputsElement: elements.hiddenInputsElement,
        selectedDatesElement: elements.selectedDatesElement,
        formatMonthDay: (dateString) => formatMonthDay(dateString),
    });

    // カレンダー情報の読み込み
    const calendar = createVacationCalendar({
        monthLabelElement: elements.monthLabelElement,
        daysContainerElement: elements.daysContainerElement,
        prevButtonElement: elements.prevButtonElement,
        nextButtonElement: elements.nextButtonElement,
        today: elements.todayInput.value,
        onSelectionChange: syncVacationInfo,
    });
    formatMonthDay = calendar.formatMonthDay;

    // 押下したメンバーの data 属性から、モーダル初期値を作る。
    const openModalForMember = (buttonElement) => {
        const memberId = buttonElement.dataset.memberId ?? '';
        const memberName = buttonElement.dataset.memberName ?? '';
        const vacationDates = parseVacationDates(buttonElement.dataset.vacationDates);
        const routeTemplate = elements.routeTemplateInput.value;

        // メンバー情報と送信先URLを対象者に合わせて差し替える。
        elements.memberIdInput.value = memberId;
        elements.memberNameElement.textContent = memberName;
        elements.form.action = routeTemplate.replace('__MEMBER__', memberId);

        // カレンダー状態を初期化してからモーダルを開く。
        calendar.setSelectedDates(vacationDates);
        openDialogById('vacation-modal');
    };

    // 有給追加ボタンに「開く」イベントを登録する
    bindOpenModalEvents(openModalForMember);

    // キャンセルボタン・backdropに「閉じる」イベントを登録する
    bindCloseModalEvents(elements.cancelButtonElement);
}
