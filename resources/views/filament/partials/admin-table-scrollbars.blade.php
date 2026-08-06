<style>
    .fi-panel-admin .admin-top-scrollbar {
        width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        height: 14px;
        margin-bottom: 0.5rem;
    }

    .fi-panel-admin .admin-top-scrollbar:empty {
        display: none;
    }

    .fi-panel-admin .admin-top-scrollbar__inner {
        height: 1px;
    }
</style>

<script>
    (() => {
        const SCROLLER_SELECTOR = '.fi-ta-content-ctn';
        let livewireHooksRegistered = false;

        const getScrollableElement = (scope) => scope.scrollWidth > scope.clientWidth + 1 ? scope : null;

        const destroyTopScrollbar = (scope) => {
            scope.__adminTopScrollbarCleanup?.();
            delete scope.__adminTopScrollbarCleanup;
            delete scope.__adminTopScrollbarTarget;
            delete scope.__adminTopScrollbarSync;
        };

        const attachTopScrollbar = (scope) => {
            const scrollable = getScrollableElement(scope);

            if (! scrollable) {
                destroyTopScrollbar(scope);

                return;
            }

            if (scope.__adminTopScrollbarTarget === scrollable) {
                scope.__adminTopScrollbarSync?.();

                return;
            }

            destroyTopScrollbar(scope);

            const topScrollbar = document.createElement('div');
            topScrollbar.className = 'admin-top-scrollbar';

            const topScrollbarInner = document.createElement('div');
            topScrollbarInner.className = 'admin-top-scrollbar__inner';
            topScrollbar.appendChild(topScrollbarInner);

            scope.before(topScrollbar);

            let isSyncing = false;

            const syncWidths = () => {
                topScrollbarInner.style.width = `${scrollable.scrollWidth}px`;
                topScrollbar.style.display = scrollable.scrollWidth > scrollable.clientWidth + 1 ? 'block' : 'none';
            };

            const syncFromTop = () => {
                if (isSyncing) {
                    return;
                }

                isSyncing = true;
                scrollable.scrollLeft = topScrollbar.scrollLeft;
                isSyncing = false;
            };

            const syncFromBottom = () => {
                if (isSyncing) {
                    return;
                }

                isSyncing = true;
                topScrollbar.scrollLeft = scrollable.scrollLeft;
                isSyncing = false;
            };

            topScrollbar.addEventListener('scroll', syncFromTop, { passive: true });
            scrollable.addEventListener('scroll', syncFromBottom, { passive: true });

            const resizeObserver = 'ResizeObserver' in window
                ? new ResizeObserver(() => {
                    syncWidths();
                    syncFromBottom();
                })
                : null;

            resizeObserver?.observe(scrollable);
            resizeObserver?.observe(scope);

            const onWindowResize = () => {
                syncWidths();
                syncFromBottom();
            };

            window.addEventListener('resize', onWindowResize);

            scope.__adminTopScrollbarTarget = scrollable;
            scope.__adminTopScrollbarSync = () => {
                syncWidths();
                syncFromBottom();
            };
            scope.__adminTopScrollbarCleanup = () => {
                topScrollbar.removeEventListener('scroll', syncFromTop);
                scrollable.removeEventListener('scroll', syncFromBottom);
                window.removeEventListener('resize', onWindowResize);
                resizeObserver?.disconnect();
                topScrollbar.remove();
            };

            syncWidths();
            syncFromBottom();
        };

        let frameId = null;

        const initTopScrollbars = () => {
            if (frameId !== null) {
                window.cancelAnimationFrame(frameId);
            }

            frameId = window.requestAnimationFrame(() => {
                frameId = null;

                document.querySelectorAll(SCROLLER_SELECTOR).forEach((scope) => {
                    attachTopScrollbar(scope);
                });
            });
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initTopScrollbars, { once: true });
        } else {
            initTopScrollbars();
        }

        document.addEventListener('livewire:navigated', initTopScrollbars);
        const registerLivewireHooks = () => {
            initTopScrollbars();

            if (livewireHooksRegistered || ! window.Livewire?.hook) {
                return;
            }

            livewireHooksRegistered = true;

            window.Livewire.hook('morph.updated', () => {
                initTopScrollbars();
            });

            window.Livewire.hook('commit', ({ succeed }) => {
                succeed(() => {
                    initTopScrollbars();
                });
            });
        };

        document.addEventListener('livewire:init', registerLivewireHooks);
        document.addEventListener('livewire:initialized', registerLivewireHooks);
        registerLivewireHooks();
    })();
</script>
