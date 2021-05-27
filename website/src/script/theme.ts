'use strict';

const themeSwitch = document.querySelector<HTMLInputElement>('#themeSwitch');

function changeTheme (darkMode: boolean) {
  document.documentElement.setAttribute('data-theme', (darkMode) ? 'dark' : 'light');
  if (themeSwitch) {
    themeSwitch.checked = darkMode;
  }
}

export default function initTheme () {
  changeTheme(window.matchMedia('(prefers-color-scheme: dark)').matches);

  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', theme => {
    changeTheme(theme.matches);
  });

  themeSwitch?.addEventListener('click', (event: MouseEvent) => {
    changeTheme(themeSwitch.checked);
  });
}
