/**
 * Accessible in-page confirm for forms with data-confirm="…".
 * Replaces window.confirm() (DICT Guideline 2.9 / G7).
 */
export function registerConfirmDialog(Alpine) {
    document.addEventListener(
        'submit',
        (event) => {
            const form = event.target;

            if (!(form instanceof HTMLFormElement)) {
                return;
            }

            const message = form.getAttribute('data-confirm');

            if (!message || form.dataset.confirmAccepted === '1') {
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();

            window.dispatchEvent(
                new CustomEvent('app-confirm', {
                    detail: {
                        message,
                        title: form.getAttribute('data-confirm-title') || 'Please confirm',
                        confirmLabel: form.getAttribute('data-confirm-label') || 'Confirm',
                        danger: form.getAttribute('data-confirm-danger') === '1',
                        form,
                    },
                }),
            );
        },
        true,
    );

    Alpine.data('confirmDialog', () => ({
        open: false,
        title: 'Please confirm',
        message: '',
        confirmLabel: 'Confirm',
        danger: false,
        pendingForm: null,

        init() {
            window.addEventListener('app-confirm', (event) => {
                this.title = event.detail.title;
                this.message = event.detail.message;
                this.confirmLabel = event.detail.confirmLabel;
                this.danger = Boolean(event.detail.danger);
                this.pendingForm = event.detail.form;
                this.open = true;

                this.$nextTick(() => {
                    this.$refs.confirmBtn?.focus();
                });
            });
        },

        cancel() {
            this.open = false;
            this.pendingForm = null;
        },

        accept() {
            const form = this.pendingForm;
            this.open = false;
            this.pendingForm = null;

            if (!form) {
                return;
            }

            form.dataset.confirmAccepted = '1';

            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }
        },
    }));
}
