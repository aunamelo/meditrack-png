/**
 * Shared Alpine state for the guest login form and the home-page role picker.
 */
export function guestLoginFormState(payload = {}) {
    return {
        email: payload.email ?? '',
        password: '',
        showPassword: false,
        emailError: payload.emailError ?? '',
        passwordError: payload.passwordError ?? '',
        credentialsError: payload.credentialsError ?? '',
        isSubmitting: false,
        panelShaking: false,
        bannerTimer: null,
        dismissCredentialsError() {
            this.credentialsError = '';
            this.clearBannerTimer();
        },
        clearBannerTimer() {
            if (this.bannerTimer) {
                clearTimeout(this.bannerTimer);
                this.bannerTimer = null;
            }
        },
        scheduleBannerDismiss() {
            this.clearBannerTimer();
            if (! this.credentialsError) {
                return;
            }

            this.bannerTimer = setTimeout(() => {
                this.credentialsError = '';
                this.bannerTimer = null;
            }, 5000);
        },
        clearEmail() {
            this.emailError = '';
            this.credentialsError = '';
            this.clearBannerTimer();
        },
        clearPassword() {
            this.passwordError = '';
            this.credentialsError = '';
            this.clearBannerTimer();
        },
        shakePanel() {
            this.panelShaking = false;
            this.$nextTick(() => {
                this.panelShaking = true;
                setTimeout(() => {
                    this.panelShaking = false;
                }, 500);
            });
        },
        bootLoginErrors() {
            if (! this.credentialsError) {
                return;
            }

            this.shakePanel();
            this.scheduleBannerDismiss();
        },
        onSubmit(event) {
            if (this.isSubmitting) {
                event.preventDefault();
                return;
            }

            this.emailError = '';
            this.passwordError = '';
            this.credentialsError = '';
            this.clearBannerTimer();

            let valid = true;
            if (! String(this.email).trim()) {
                this.emailError = 'This field is required.';
                valid = false;
            }
            if (! String(this.password)) {
                this.passwordError = 'This field is required.';
                valid = false;
            }

            if (! valid) {
                event.preventDefault();
                this.shakePanel();
                return;
            }

            this.isSubmitting = true;
        },
        init() {
            this.bootLoginErrors();

            return () => this.clearBannerTimer();
        },
    };
}

export function guestRolePicker(payload = {}) {
    return {
        ...guestLoginFormState(payload),
        selectedRole: payload.selectedRole ?? null,
        labels: payload.labels ?? {},
        microsoftUrls: payload.microsoftUrls ?? {},
        loginOpen: Boolean(payload.loginOpen),
        showValidation: false,
        highlightGrid: false,
        shakeButton: false,
        lastRoleCard: null,
        select(role, event) {
            this.selectedRole = role;
            this.showValidation = false;
            this.highlightGrid = false;
            this.lastRoleCard = event?.currentTarget ?? this.lastRoleCard;
        },
        roleSlug() {
            return this.selectedRole ? String(this.selectedRole).replace(/_/g, '-') : '';
        },
        focusableIn(container) {
            return [...container.querySelectorAll(
                'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
            )].filter((el) => {
                if (el.hasAttribute('disabled') || el.getAttribute('aria-hidden') === 'true') {
                    return false;
                }

                if (el.closest('[hidden], [aria-hidden="true"]')) {
                    return false;
                }

                return el.getClientRects().length > 0;
            });
        },
        trapFocus(event) {
            if (! this.loginOpen || event.key !== 'Tab') {
                return;
            }

            const root = this.$refs.loginPanel;
            if (! root) {
                return;
            }

            const nodes = this.focusableIn(root);
            if (! nodes.length) {
                event.preventDefault();
                return;
            }

            const first = nodes[0];
            const last = nodes[nodes.length - 1];
            const active = document.activeElement;

            if (! root.contains(active)) {
                event.preventDefault();
                (event.shiftKey ? last : first).focus();
                return;
            }

            if (event.shiftKey && active === first) {
                event.preventDefault();
                last.focus();
            } else if (! event.shiftKey && active === last) {
                event.preventDefault();
                first.focus();
            }
        },
        continueToLogin() {
            if (! this.selectedRole) {
                this.showValidation = true;
                this.highlightGrid = true;
                this.shakeButton = false;
                this.$nextTick(() => {
                    this.shakeButton = true;
                    setTimeout(() => {
                        this.shakeButton = false;
                    }, 450);
                });

                return;
            }

            this.showValidation = false;
            this.highlightGrid = false;
            this.lastRoleCard = document.getElementById(`guest-role-card-${this.selectedRole}`) ?? this.lastRoleCard;
            this.loginOpen = true;
            this.$nextTick(() => {
                this.$refs.loginEmail?.focus();
            });
        },
        closeLogin() {
            if (! this.loginOpen || this.isSubmitting) {
                return;
            }

            this.loginOpen = false;
            this.$el.classList.remove('guest-home--reopen-login');
            window.setTimeout(() => {
                const selectedCard = this.selectedRole
                    ? document.getElementById(`guest-role-card-${this.selectedRole}`)
                    : null;
                const target = (this.lastRoleCard && document.contains(this.lastRoleCard))
                    ? this.lastRoleCard
                    : (selectedCard || this.$refs.continueBtn);

                target?.focus();
            }, 200);
        },
        init() {
            this.bootLoginErrors();
            this.$watch('loginOpen', (open) => {
                this.syncLoginChrome(open);
            });

            if (this.loginOpen) {
                this.syncLoginChrome(true);
                this.$nextTick(() => this.$refs.loginEmail?.focus());
            }

            if (this.selectedRole) {
                this.lastRoleCard = document.getElementById(`guest-role-card-${this.selectedRole}`);
            }

            return () => {
                this.clearBannerTimer();
                this.syncLoginChrome(false);
            };
        },
        syncLoginChrome(open) {
            document.body.classList.toggle('overflow-hidden', Boolean(open));
            document.body.classList.toggle('guest-login-modal-open', Boolean(open));
            document.querySelectorAll('[data-guest-inert]').forEach((el) => {
                if (open) {
                    el.setAttribute('inert', '');
                } else {
                    el.removeAttribute('inert');
                }
            });
        },
    };
}

export function registerGuestLogin(Alpine) {
    Alpine.data('guestLoginForm', (payload = {}) => guestLoginFormState(payload));
    Alpine.data('guestRolePicker', (payload = {}) => guestRolePicker(payload));
}
