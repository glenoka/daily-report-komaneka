import './bootstrap';
import { initListPasteHandler } from './list-normalizer';

// Initialize paste auto-clean handler
document.addEventListener('DOMContentLoaded', () => {
    initListPasteHandler();
});
