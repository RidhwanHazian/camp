document.addEventListener("DOMContentLoaded", () => {
  const packageCountsData = window.packageCountsData || [];
  const packageLabels = window.packageLabels || [];
  const trendingPackages = window.trendingPackages || [];

  const trendingValue = document.getElementById("trending-value");
  const badge = document.getElementById("trending-badge");
  const card = document.getElementById("trending-card");

  const chartColors = [
    '#3498db', '#e67e22', '#2ecc71', '#9b59b6', '#f1c40f',
    '#e74c3c', '#1abc9c', '#34495e', '#8e44ad', '#16a085'
  ];

  // Show trending packages and apply dynamic color
  if (!packageCountsData.length || Math.max(...packageCountsData) === 0) {
    trendingValue.textContent = "No Data";
    badge.style.backgroundColor = "#ccc";
    card.style.borderLeft = "8px solid #ccc";
  } else {
    const max = Math.max(...packageCountsData);
    const topIndices = packageCountsData
      .map((val, i) => val === max ? i : -1)
      .filter(i => i !== -1);

    const topPackages = topIndices.map(i => packageLabels[i]);
    const topColors = topIndices.map(i => chartColors[i % chartColors.length]);

    trendingValue.textContent = topPackages.join(", ");

    if (topColors.length === 1) {
      badge.style.backgroundColor = topColors[0];
      card.style.borderLeft = `8px solid ${topColors[0]}`;
    } else {
      // Horizontal gradient for badge
      const badgeGradient = `linear-gradient(to right, ${topColors.join(", ")})`;
      badge.style.background = badgeGradient;

      // Vertical gradient for border using border-image
      const borderGradient = `linear-gradient(to bottom, ${topColors.join(", ")})`;
      card.style.borderLeft = '8px solid';
      card.style.borderImage = borderGradient;
      card.style.borderImageSlice = 1;
    }
  }

  // Render pie chart
  const ctx = document.getElementById('packageChart').getContext('2d');
  new Chart(ctx, {
    type: 'pie',
    data: {
      labels: packageLabels,
      datasets: [{
        data: packageCountsData,
        backgroundColor: chartColors.slice(0, packageLabels.length),
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'bottom',
        }
      }
    }
  });
});
