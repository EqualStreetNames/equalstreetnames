'use strict';

import { ArcElement, Chart, DoughnutController } from 'chart.js';

import colors from './colors';
import { lang, lastUpdate, statistics } from './index';

let elementCanvas: HTMLCanvasElement;
let elementDiv: HTMLDivElement;

Chart.register(ArcElement, DoughnutController);

function createChart (data: number[]): Chart | null {
  const context = elementCanvas.getContext('2d');

  if (context !== null) {
    return new Chart(context, {
      type: 'doughnut',
      data: {
        labels: [
          'Male (cis)',
          'Female (cis)',
          'Male (trans)',
          'Female (trans)',
          'Intersex',
          'Non-binary',
          'Unknown'
        ],
        datasets: [
          {
            data,
            backgroundColor: [
              colors.m,
              colors.f,
              colors.mx,
              colors.fx,
              colors.x,
              colors.nb,
              colors.u
            ]
          }
        ]
      },
      options: {
        animation: {
          animateScale: false,
          animateRotate: false
        },
        circumference: 180,
        plugins: {
          legend: {
            display: false
          }
        },
        responsive: false,
        rotation: -90
      }
    });
  }

  return null;
}

function updateLabels (count: {
  f: number;
  m: number;
  fx: number;
  mx: number;
  x: number;
  nb: number;
  u: number; // unknown
  p: number; // multiple
  o: number; // not a person
}): void {
  const totalPerson =
    count.f + count.m + count.fx + count.mx + count.x + count.nb + count.u + count.p;

  Object.keys(count).forEach((gender: string) => {
    const labelElement = elementDiv.querySelector(
      `.gender-chart-label[data-gender=${gender}]`
    ) as HTMLTableRowElement;
    if (labelElement !== null) {
      labelElement.style.color = colors[gender];

      const countElement = labelElement.querySelector(
        '.gender-chart-count'
      ) as HTMLTableCellElement;
      if (countElement !== null) {
        countElement.innerText = `${count[gender]}`;
      }

      const pctElement = labelElement.querySelector(
        '.gender-chart-pct'
      ) as HTMLTableCellElement;
      if (pctElement !== null) {
        const percentage = Math.round((count[gender] / totalPerson) * 100 * 100) / 100;

        pctElement.innerText = `${percentage} %`;
      }
    }
  });
}

export default function (element: HTMLCanvasElement): void {
  elementCanvas = element;
  elementDiv = elementCanvas.parentElement as HTMLDivElement;

  const count = {
    f: statistics.F,
    m: statistics.M,
    fx: statistics.FX,
    mx: statistics.MX,
    x: statistics.X,
    nb: statistics.NB,
    u: statistics['?'], // unknown
    p: statistics['+'], // multiple
    o: statistics['-'] // not a person
  };
  const totalPerson =
    count.f + count.m + count.fx + count.mx + count.x + count.nb + count.u + count.p;
  const total = totalPerson + count.o;

  createChart([count.m, count.f, count.mx, count.fx, count.x, count.nb, count.u]);
  updateLabels(count);

  const spanCount = document.getElementById(
    'count-person'
  ) as HTMLSpanElement;
  const spanTotal = document.getElementById(
    'count-total'
  ) as HTMLSpanElement;

  const percentage = Math.round((totalPerson / total) * 100 * 100) / 100;

  spanCount.innerText = `${totalPerson} (${percentage}%)`;
  spanTotal.innerText = total;

  const spanLastUpdate = document.getElementById('last-update') as HTMLSpanElement;
  if (spanLastUpdate !== null) {
    const date = new Date(lastUpdate);
    const format = new Intl.DateTimeFormat(lang, { dateStyle: 'medium', timeStyle: 'short' });

    spanLastUpdate.innerText = format.format(date);
  }
}
