// =========================
// モーダル共通実装ルール
// =========================
// 1. このファイルは「開閉の共通処理」だけを持つ（業務データは扱わない）。
// 2. モーダル固有の初期値セット・フォーム反映は各モーダルJS側で行う。
// 3. dialogIdは固定文字列で渡し、存在しない場合は即エラーで検知する。

// dialogIdの取得
function findDialogById(dialogId) {
    const dialog = document.getElementById(dialogId);
    // dialogIdが取得できなければエラーを返す
    if (!(dialog instanceof HTMLDialogElement)) {
        throw new Error(`[modal-manager] dialog not found or not <dialog>: ${dialogId}`);
    }

    return dialog;
}

// モーダルを開く
export function openDialogById(dialogId) {
    const dialog = findDialogById(dialogId);
    if (dialog.open) {
        return;
    }

    dialog.showModal();
}

// モーダルを閉じる
export function closeDialogById(dialogId) {
    const dialog = findDialogById(dialogId);
    if (!dialog.open) {
        return;
    }

    dialog.close();
}

// backdropクリックでモーダルを閉じる
export function bindBackdropCloseByTarget(dialogId) {
    const dialog = findDialogById(dialogId);

    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            closeDialogById(dialogId);
        }
    });
}
