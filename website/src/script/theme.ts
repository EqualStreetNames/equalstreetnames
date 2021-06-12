'use strict';

import initChart from './chart';
import initMap from './map';

const themeSwitch = document.querySelector('#themeSwitch') as HTMLInputElement;

export let theme!: string;

function changeTheme () {
  document.documentElement.setAttribute('data-theme', theme);

  if (typeof themeSwitch?.checked !== 'undefined') {
    themeSwitch.checked = theme === 'dark';
  }

  initMap();

  initChart();
}

export default function initTheme (): void {
  // Initialize theme
  theme = window.matchMedia('(prefers-color-scheme: dark)').matches === true ? 'dark' : 'light';

  changeTheme();

  // Update theme when browser configuration changes
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (event: MediaQueryListEvent) => {
    theme = event.matches === true ? 'dark' : 'light';

    changeTheme();
  });

  // Update theme when user click on them switch
  themeSwitch?.addEventListener('click', (event: MouseEvent) => {
    theme = themeSwitch.checked === true ? 'dark' : 'light';

    changeTheme();
  });
}
