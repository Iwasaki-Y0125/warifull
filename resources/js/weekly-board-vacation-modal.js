import { createVacationCalendar } from './weekly-board-vacation-calendar';
import { openDialogById } from './modal-manager';

function parseVacationDates(rawValue) {
    // data属性から渡る文字列を配列へ変換する。
    // 不正JSONや空文字でも例外で止めず、空配列で扱う。
    if (!rawValue) {
        return [];
    }

    try {
        const parsed = JSON.parse(rawValue);

        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return [];
    }
}

export function initVacationModal() {
    // モーダル制御に必要な要素をまとめて取得する。
    const form = document.getElementById('vacation-modal-form');
    const routeTemplateInput = document.getElementById('vacation-modal-route-template');
    const todayInput = document.getElementById('vacation-modal-today');
    const memberIdInput = document.getElementById('vacation-modal-member-id');
    const memberNameElement = document.getElementById('vacation-modal-member-name');
    const monthLabelElement = document.getElementById('vacation-modal-month-label');
    const daysContainerElement = document.getElementById('vacation-modal-calendar-days');
    const selectedDatesElement = document.getElementById('vacation-modal-selected-dates');
    const hiddenInputsElement = document.getElementById('vacation-modal-hidden-inputs');
    const prevButtonElement = document.getElementById('vacation-modal-prev-month');
    const nextButtonElement = document.getElementById('vacation-modal-next-month');

    const requiredElements = [
        form,
        routeTemplateInput,
        todayInput,
        memberIdInput,
        memberNameElement,
        monthLabelElement,
        daysContainerElement,
        selectedDatesElement,
        hiddenInputsElement,
        prevButtonElement,
        nextButtonElement,
    ];

    if (requiredElements.some((element) => element === null)) {
        // 対象画面以外でこのJSが読み込まれても安全に何もしない。
        return;
    }

    // 1. selectedDates を hidden input に同期
    // 2. 「選択中の有給日」表示も同じデータで更新
    // 3. 画面表示と送信値のズレを防ぐ
    const updateSelectionView = (selectedDates) => {
        hiddenInputsElement.replaceChildren();

        selectedDates.forEach((dateString) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'vacation_dates[]';
            input.value = dateString;
            hiddenInputsElement.appendChild(input);
        });

        selectedDatesElement.textContent =
            selectedDates.length > 0
                ? selectedDates.map((dateString) => calendar.formatMonthDay(dateString)).join(', ')
                : '有給予定なし';
    };

    const calendar = createVacationCalendar({
        monthLabelElement,
        daysContainerElement,
        prevButtonElement,
        nextButtonElement,
        today: todayInput.value,
        onSelectionChange: updateSelectionView,
    });

    // 押下したメンバーカードの data 属性から、モーダル初期値を作る。
    const openModalForMember = (buttonElement) => {
        const memberId = buttonElement.dataset.memberId ?? '';
        const memberName = buttonElement.dataset.memberName ?? '';
        const vacationDates = parseVacationDates(buttonElement.dataset.vacationDates);
        const routeTemplate = routeTemplateInput.value;

        // メンバー情報と送信先URLを対象者に合わせて差し替える。
        memberIdInput.value = memberId;
        memberNameElement.textContent = memberName;
        form.action = routeTemplate.replace('__MEMBER__', memberId);

        // カレンダー状態を初期化してからモーダルを開く。
        calendar.setSelectedDates(vacationDates);
        openDialogById('vacation-modal');
    };

    // 各メンバーカードの起点ボタンに、モーダル初期化イベントを紐付ける。
    document.querySelectorAll('.js-open-vacation-modal').forEach((buttonElement) => {
        buttonElement.addEventListener('click', () => {
            openModalForMember(buttonElement);
        });
    });
}
