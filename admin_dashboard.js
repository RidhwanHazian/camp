const ctx = document.getElementById('packageChart').getContext('2d');
const packageChart = new Chart(ctx, {
  type: 'pie',
  data: {
    labels: ['Package A', 'Package B', 'Package C', 'Package D', 'Package E'],
    datasets: [{
      label: 'Packages Chosen',
      data: window.packageCountsData || [0, 0, 0, 0, 0],
      backgroundColor: [
        '#3498db', '#2ecc71', '#e67e22', '#e74c3c', '#9b59b6'
      ],
      borderWidth: 1
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: {
        position: 'bottom'
      }
    }
  }
});

// Handle trending package display
const chartData = packageChart.data.datasets[0].data;
const chartLabels = packageChart.data.labels;
const chartColors = packageChart.data.datasets[0].backgroundColor;

if (Math.max(...chartData) === 0) {
  // No data case
  document.getElementById('trending-value').textContent = "No Data";
  document.getElementById('trending-badge').style.backgroundColor = "#ccc";
  document.getElementById('trending-card').style.borderLeft = "8px solid #ccc";
} else {
  // Show trending package
  const maxIndex = chartData.indexOf(Math.max(...chartData));
  const trendingPackage = chartLabels[maxIndex];
  const trendingColor = chartColors[maxIndex];

  document.getElementById('trending-value').textContent = trendingPackage;
  document.getElementById('trending-badge').style.backgroundColor = trendingColor;
  document.getElementById('trending-card').style.borderLeft = `8px solid ${trendingColor}`;
}
