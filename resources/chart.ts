"use strict";

import Chart from "chart.js";

import colors from "./colors";

import statistics from "../data/statistics.json";

let elementCanvas: HTMLCanvasElement;
let elementDiv: HTMLDivElement;

function createChart(data: number[]): Chart | null {
  const context = elementCanvas.getContext("2d");

  if (context !== null) {
    return new Chart(context, {
      type: "doughnut",
      data: {
        labels: ["Male", "Female", "Transgender"],
        datasets: [
          {
            data,
            backgroundColor: [colors.m, colors.f, colors.x]
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

function updateLabels(
  count: { f: number; m: number; x: number; o: number },
  total: number
): void {
  ["f", "m", "x"].forEach((gender: string) => {
    const labelFemale = elementDiv.querySelector(
      `.gender-chart-label[data-gender=${gender}]`
    ) as HTMLDivElement;
    const countFemale = labelFemale.querySelector(
      ".gender-chart-count"
    ) as HTMLSpanElement;

    const percentage = Math.round((count[gender] / total) * 100 * 100) / 100;

    labelFemale.style.color = colors[gender];
    countFemale.innerText = `${count[gender]} (${percentage}%)`;
  });
}

export default function(element: HTMLCanvasElement) {
  elementCanvas = element;
  elementDiv = elementCanvas.parentElement as HTMLDivElement;

  const count = {
    f: statistics["f"].length,
    m: statistics["m"].length,
    x: statistics["x"].length,
    o: statistics["-"].length
  };
  const total = count["f"] + count["m"] + count["x"];

  createChart([count["m"], count["f"], count["x"]]);
  updateLabels(count, total);
}
