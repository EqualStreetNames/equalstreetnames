"use strict";

import Chart from "chart.js";

import colors from "./colors";

import statistics from "../static/statistics.json";

let elementCanvas: HTMLCanvasElement;
let elementDiv: HTMLDivElement;

function createChart(data: number[]): Chart | null {
  const context = elementCanvas.getContext("2d");

  if (context !== null) {
    return new Chart(context, {
      type: "doughnut",
      data: {
        labels: [
          "Male (cis)",
          "Female (cis)",
          "Male (transgender)",
          "Female (transgender)",
          "Intersex"
        ],
        datasets: [
          {
            data,
            backgroundColor: [
              colors.m,
              colors.f,
              colors.mx,
              colors.fx,
              colors.x
            ]
          }
        ]
      },
      options: {
        animation: {
          animateScale: false,
          animateRotate: false
        },
        circumference: Math.PI,
        legend: false,
        responsive: true,
        rotation: -Math.PI
      }
    });
  }

  return null;
}

function updateLabel(gender: string, count: number, total: number): void {
  const labelElement = elementDiv.querySelector(
    `.gender-chart-label[data-gender=${gender}]`
  ) as HTMLDivElement;
  const countElement = labelElement.querySelector(
    ".gender-chart-count"
  ) as HTMLSpanElement;

  const percentage = Math.round((count / total) * 100 * 100) / 100;

  labelElement.style.color = colors[gender];
  countElement.innerText = `${count} (${percentage}%)`;
}

function updateLabels(count: {
  f: number;
  m: number;
  fx: number;
  mx: number;
  x: number;
  o: number;
}): void {
  const totalPerson = count.f + count.m + count.fx + count.mx + count.x;
  const total = totalPerson + count.o;

  ["f", "m" /*, "fx"*/, "mx" /*, "x"*/].forEach((gender: string) => {
    updateLabel(gender, count[gender], totalPerson);
  });
}

export default function(element: HTMLCanvasElement): void {
  elementCanvas = element;
  elementDiv = elementCanvas.parentElement as HTMLDivElement;

  const count = {
    f: statistics["F"],
    m: statistics["M"],
    fx: statistics["FX"],
    mx: statistics["MX"],
    x: statistics["X"],
    o: statistics["-"]
  };
  const totalPerson = count.f + count.m + count.fx + count.mx + count.x;
  const total = totalPerson + count.o;

  createChart([count["m"], count["f"], count["mx"], count["fx"], count["x"]]);
  updateLabels(count);

  const spanCount = document.getElementById("count-person") as HTMLSpanElement;
  const spanTotal = document.getElementById("count-total") as HTMLSpanElement;

  const percentage = Math.round((totalPerson / total) * 100 * 100) / 100;

  spanCount.innerText = `${totalPerson} (${percentage}%)`;
  spanTotal.innerText = total;
}
