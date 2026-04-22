export function openDialogById(dialogId) {
    // 対象が <dialog> で、まだ開いていない時だけ開く。
    const dialog = document.getElementById(dialogId);
    if (!(dialog instanceof HTMLDialogElement) || dialog.open) {
        return;
    }

    dialog.showModal();
}

export function closeDialogById(dialogId) {
    // 対象が <dialog> で、開いている時だけ閉じる。
    const dialog = document.getElementById(dialogId);
    if (!(dialog instanceof HTMLDialogElement) || !dialog.open) {
        return;
    }

    dialog.close();
}

export function initModalManager() {
    // data属性で指定されたモーダルを、画面共通のルールで開閉する。
    document.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof Element)) {
            return;
        }

        const openDialogId = target.closest('[data-modal-open]')?.getAttribute('data-modal-open');
        if (openDialogId) {
            openDialogById(openDialogId);
            return;
        }

        const closeDialogId = target.closest('[data-modal-close]')?.getAttribute('data-modal-close');
        if (closeDialogId) {
            closeDialogById(closeDialogId);
        }
    });
}
