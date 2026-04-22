<dialog id="vacation-modal" class="backdrop:bg-slate-900/50 fixed inset-0 m-auto w-[min(92vw,56rem)] max-h-[calc(100dvh-2rem)] overflow-y-auto rounded-2xl p-0 shadow-xl">
    <form method="dialog" class="border-b border-slate-200 px-6 py-4">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xl font-bold">有給追加</p>
                <p id="vacation-modal-member-name" class="mt-1 text-sm text-slate-600"></p>
            </div>
            <button type="submit" class="rounded-lg px-3 py-1 text-xl text-slate-500 hover:bg-slate-100">×</button>
        </div>
    </form>

    <form id="vacation-modal-form" method="POST" action="" class="space-y-5 p-6">
        @csrf
        @method('PUT')

        <input id="vacation-modal-member-id" type="hidden" name="member_id" value="">
        <input id="vacation-modal-route-template" type="hidden" value="{{ $vacationUpdateRouteTemplate }}">
        <input id="vacation-modal-today" type="hidden" value="{{ now()->toDateString() }}">

        <div class="flex items-center justify-between">
            <button id="vacation-modal-prev-month" type="button" class="rounded-lg px-3 py-2 text-2xl text-slate-700 hover:bg-slate-100">
                ‹
            </button>
            <p id="vacation-modal-month-label" class="text-3xl font-bold tracking-tight"></p>
            <button id="vacation-modal-next-month" type="button" class="rounded-lg px-3 py-2 text-2xl text-slate-700 hover:bg-slate-100">
                ›
            </button>
        </div>

        <div class="grid grid-cols-7 gap-2">
            <p class="text-center text-xs font-semibold text-slate-500">日</p>
            <p class="text-center text-xs font-semibold text-slate-500">月</p>
            <p class="text-center text-xs font-semibold text-slate-500">火</p>
            <p class="text-center text-xs font-semibold text-slate-500">水</p>
            <p class="text-center text-xs font-semibold text-slate-500">木</p>
            <p class="text-center text-xs font-semibold text-slate-500">金</p>
            <p class="text-center text-xs font-semibold text-slate-500">土</p>
        </div>
        <div id="vacation-modal-calendar-days" class="grid grid-cols-7 gap-2"></div>

        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
            <p class="text-sm font-semibold text-blue-900">選択中の有給日</p>
            <p id="vacation-modal-selected-dates" class="mt-2 text-blue-800">有給予定なし</p>
        </div>

        <div id="vacation-modal-hidden-inputs"></div>

        <div class="flex items-center justify-between gap-3 border-t border-slate-200 pt-4">
            <button type="button" data-modal-close="vacation-modal" class="w-full rounded-xl border border-slate-300 px-5 py-3 text-lg font-semibold text-slate-800 hover:bg-slate-100">
                キャンセル
            </button>
            <button type="submit" class="w-full rounded-xl bg-blue-600 px-5 py-3 text-lg font-semibold text-white hover:bg-blue-700">
                保存
            </button>
        </div>
    </form>
</dialog>
