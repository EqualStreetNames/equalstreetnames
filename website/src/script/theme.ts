'use strict';

export default function initTheme () {
  const theme = window.matchMedia('(prefers-color-scheme: dark)');

  document.documentElement.setAttribute('data-theme', (theme.matches) ? 'dark' : 'light');

  theme.addEventListener('change', event => {
    document.documentElement.setAttribute('data-theme', (event.matches) ? 'dark' : 'light');
  });
}
