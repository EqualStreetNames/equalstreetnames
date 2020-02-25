"use strict";

import Chart from "chart.js";

import colors from "./colors";

export default function() {
  var ctx = document.getElementById("gender-chart").getContext("2d");
  var myChart = new Chart(ctx, {
    type: "doughnut",
    data: {
      labels: ["Male", "Female", "Other"],
      datasets: [
        {
          data: [92, 7, 1],
          backgroundColor: [colors.male, colors.female, colors.other]
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
