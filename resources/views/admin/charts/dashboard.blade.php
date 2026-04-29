<div class="grid grid-cols-1 gap-6 mb-6" style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem;">
    <div style="background:var(--color-bg);border:1px solid var(--color-border);border-radius:0.75rem;padding:1.25rem;">
        <div style="font-size:0.8rem;font-weight:600;color:var(--color-text-secondary);margin-bottom:1rem;text-transform:uppercase;letter-spacing:.05em;">
            Депозиты за 14 дней
        </div>
        <div id="chart-deposits"></div>
    </div>
    <div style="background:var(--color-bg);border:1px solid var(--color-border);border-radius:0.75rem;padding:1.25rem;">
        <div style="font-size:0.8rem;font-weight:600;color:var(--color-text-secondary);margin-bottom:1rem;text-transform:uppercase;letter-spacing:.05em;">
            Апгрейды за 14 дней
        </div>
        <div id="chart-upgrades"></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts@3/dist/apexcharts.min.js"></script>
<script>
(function() {
    const isDark = document.documentElement.classList.contains('dark')
        || document.body.classList.contains('dark');

    const textColor   = isDark ? '#9ca3af' : '#6b7280';
    const borderColor = isDark ? '#374151' : '#e5e7eb';
    const bg          = isDark ? '#1f2937' : '#ffffff';

    const baseOpts = {
        chart: { height: 200, toolbar: { show: false }, background: 'transparent', fontFamily: 'inherit' },
        grid: { borderColor, strokeDashArray: 4 },
        tooltip: { theme: isDark ? 'dark' : 'light' },
        xaxis: { labels: { style: { colors: textColor, fontSize: '11px' } }, axisBorder: { show: false }, axisTicks: { show: false } },
        yaxis: { labels: { style: { colors: textColor, fontSize: '11px' } } },
        dataLabels: { enabled: false },
        legend: { labels: { colors: textColor } },
    };

    // Chart 1 — Deposits
    const depositLabels  = @json($depositLabels);
    const depositAmounts = @json($depositAmounts);

    new ApexCharts(document.getElementById('chart-deposits'), {
        ...baseOpts,
        chart: { ...baseOpts.chart, type: 'area' },
        series: [{ name: 'Депозиты ₽', data: depositAmounts }],
        xaxis: { ...baseOpts.xaxis, categories: depositLabels },
        colors: ['#6366f1'],
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 100] } },
        stroke: { curve: 'smooth', width: 2 },
    }).render();

    // Chart 2 — Upgrades win/lose
    const upgradeLabels = @json($upgradeLabels);
    const upgradeWins   = @json($upgradeWins);
    const upgradeLoses  = @json($upgradeLoses);

    new ApexCharts(document.getElementById('chart-upgrades'), {
        ...baseOpts,
        chart: { ...baseOpts.chart, type: 'bar', stacked: true },
        series: [
            { name: 'Победы', data: upgradeWins },
            { name: 'Проигрыши', data: upgradeLoses },
        ],
        xaxis: { ...baseOpts.xaxis, categories: upgradeLabels },
        colors: ['#22c55e', '#ef4444'],
        plotOptions: { bar: { borderRadius: 3, columnWidth: '55%' } },
    }).render();
})();
</script>
