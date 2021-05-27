'use strict';

function updateTheme (newTheme: string) {
  // Change the active item
  document.querySelectorAll('.theme-option').forEach(element => {
    element.setAttribute('data-active', 'false');
  });
  document.getElementById(`theme-${newTheme}`)?.setAttribute('data-active', 'true');

  if (newTheme === 'light' || newTheme === 'dark') {
    document.documentElement.setAttribute('data-theme', newTheme);
  } else {
    // Follow system theme
    const theme = window.matchMedia('(prefers-color-scheme: dark)');
    document.documentElement.setAttribute('data-theme', (theme.matches) ? 'dark' : 'light');
  }
}

function themeBtnClick (this: HTMLElement, ev: MouseEvent) {
  // Update localstorage
  const newTheme = this.id.split('-')[1] ?? 'system';
  localStorage.setItem('theme', newTheme);

  updateTheme(newTheme);
}

export default function initTheme () {
  updateTheme(localStorage.getItem('theme') ?? 'system');

  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', _ => {
    updateTheme(localStorage.getItem('theme') ?? 'system');
  });

  document.getElementById('theme-light')?.addEventListener('click', themeBtnClick);
  document.getElementById('theme-dark')?.addEventListener('click', themeBtnClick);
  document.getElementById('theme-system')?.addEventListener('click', themeBtnClick);
}
