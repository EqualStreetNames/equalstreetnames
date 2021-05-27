'use strict';

function changeTheme (darkMode: boolean) {
  document.documentElement.setAttribute('data-theme', (darkMode) ? 'dark' : 'light');
  (document.getElementById('themeSwitch') as HTMLInputElement).checked = darkMode;
}

export default function initTheme () {
  changeTheme(window.matchMedia('(prefers-color-scheme: dark)').matches);

  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', theme => {
    changeTheme(theme.matches);
  });

  document.getElementById('themeSwitch')?.addEventListener('click', (event: MouseEvent) => {
    changeTheme((event.target as HTMLInputElement).checked);
  });
}
