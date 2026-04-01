<style>
    body.fi-body.fi-panel-admin {
        color: #193760;
        --gray-400: #193760;
        --gray-950: #193760;
        --gray-700: #193760;
        --gray-500: #193760;
        --admin-shell-surface: #fff;
        --admin-shell-surface-muted: #f9f9f9;
        --admin-shell-border: #e5edf6;
        --admin-shell-text: #193760;
        --admin-shell-text-muted: #8695aa;
        --admin-shell-shadow: 0 10px 30px rgba(25, 55, 96, 0.08);
    }

    .fi-panel-admin .fi-sidebar-header-ctn {
        display: none;
    }

    .admin-shell-header {
        font-family: "Inter", sans-serif;
        background: var(--admin-shell-surface);
        border-bottom: 1px solid var(--admin-shell-border);
        box-shadow: var(--admin-shell-shadow);
    }

    .admin-shell-footer {
        font-family: "Inter", sans-serif;
    }

    .admin-shell-header a,
    .admin-shell-header button,
    .admin-shell-footer button {
        color: var(--admin-shell-text);
    }

    .admin-shell-footer .text-brand-gray-dark,
    .admin-shell-footer .text-brand-gray-dark a {
        color: #8695aa;
    }

    .admin-shell-footer .text-brand-gray,
    .admin-shell-footer .text-brand-gray a {
        color: #c7ced7;
    }

    .admin-shell-header .button_1,
    .admin-shell-header .button_1:hover,
    .admin-shell-header .button_1:focus-visible,
    .admin-shell-header .button_1:active {
        color: #fff;
    }

    .admin-shell-footer {
        margin-top: 3rem;
        background: var(--admin-shell-surface-muted);
        border-top: 1px solid var(--admin-shell-border);
    }

    .admin-shell-footer h4,
    .admin-shell-footer h5,
    .admin-shell-footer a,
    .admin-shell-footer div,
    .admin-shell-footer li,
    .admin-shell-footer ul {
        font-family: "Inter", sans-serif;
        font-style: normal;
    }

    .admin-shell-header__desktop-exit {
        display: none;
    }

    @media (min-width: 1024px) {
        .admin-shell-header__desktop-exit {
            display: block;
        }
    }

    .admin-shell-socials {
        color: var(--admin-shell-text);
    }

    .admin-shell-socials svg {
        display: block;
        color: inherit;
    }

    .admin-shell-socials svg path {
        fill: currentColor;
    }
</style>
