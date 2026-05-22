(() => {
    const indicators = document.querySelectorAll('[data-session-indicator]');
    if (!indicators.length) {
        return;
    }

    const getText = (indicator, data) => {
        if (!data.active) {
            return indicator.dataset.idleText || 'Session idle';
        }

        const role = data.user?.role ? data.user.role.charAt(0).toUpperCase() + data.user.role.slice(1) : 'Member';
        const name = data.user?.name || 'User';
        return `Active session · ${role} · ${name}`;
    };

    const updateIndicator = (indicator, data) => {
        const textTarget = indicator.querySelector('[data-session-text]');
        indicator.classList.toggle('active', Boolean(data.active));
        if (textTarget) {
            textTarget.textContent = getText(indicator, data);
        }

        const shouldRedirect = indicator.dataset.autoRedirect === 'true';
        if (shouldRedirect && data.active && data.dashboard) {
            window.location.href = data.dashboard;
        }
    };

    const pingSession = (keepAlive = false) => {
        const url = keepAlive ? 'session_status.php?keepAlive=1' : 'session_status.php';
        return fetch(url, { credentials: 'include' })
            .then((res) => res.ok ? res.json() : Promise.reject(new Error('Network response was not ok.')))
            .then((data) => {
                indicators.forEach((indicator) => updateIndicator(indicator, data));
                return data;
            })
            .catch(() => {
                indicators.forEach((indicator) => {
                    const textTarget = indicator.querySelector('[data-session-text]');
                    indicator.classList.remove('active');
                    if (textTarget) {
                        textTarget.textContent = indicator.dataset.errorText || 'Unable to verify session';
                    }
                });
            });
    };

    pingSession();
    const keepAliveInterval = Number(document.body.dataset.sessionKeepalive || 120000);
    const intervalId = setInterval(() => pingSession(true), keepAliveInterval);

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            pingSession();
        }
    });

    window.addEventListener('beforeunload', () => clearInterval(intervalId));
})();

