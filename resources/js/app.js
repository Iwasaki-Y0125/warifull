import { initModalManager } from './modal-manager';
import { initVacationModal } from './weekly-board-vacation-modal';

document.addEventListener('DOMContentLoaded', () => {
    initModalManager();
    initVacationModal();
});
