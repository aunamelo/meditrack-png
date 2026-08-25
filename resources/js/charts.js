import Chart from 'chart.js/auto';

function themeColors() {
    const isDark = document.documentElement.classList.contains('dark');

    return {
        text: isDark ? '#e4e4e7' : '#33454b',
        muted: isDark ? '#a1a1aa' : '#64757b',
        grid: isDark ? 'rgba(255, 255, 255, 0.06)' : 'rgba(15, 32, 39, 0.08)',
        border: isDark ? '#3f3f46' : '#dfe7e6',
        tooltipBg: isDark ? '#18181b' : '#0f2027',
    };
}

function buildOptions(config, colors) {
    const horizontal = config.horizontal === true;
    const isDonut = config.type === 'doughnut';
    const values = (config.datasets ?? [])
        .flatMap((dataset) => dataset.data ?? [])
        .map((value) => Number(value))
        .filter((value) => Number.isFinite(value));
    const maxValue = values.length ? Math.max(...values) : 0;
    const valueAxis = {
        ticks: {
            color: colors.muted,
            font: { family: '"Plus Jakarta Sans", sans-serif', size: 11 },
            precision: 0,
        },
        grid: { color: colors.grid, drawBorder: false },
        border: { color: colors.border },
        beginAtZero: true,
        suggestedMax: maxValue > 0 ? Math.max(5, Math.ceil(maxValue * 1.15)) : 5,
    };
    const categoryAxis = {
        ticks: { color: colors.muted, font: { family: '"Plus Jakarta Sans", sans-serif', size: 11 } },
        grid: { color: colors.grid, drawBorder: false },
        border: { color: colors.border },
    };

    return {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: horizontal ? 'y' : 'x',
        plugins: {
            legend: {
                display: isDonut || (config.datasets?.length ?? 0) > 1,
                labels: {
                    color: colors.text,
                    font: { family: '"Plus Jakarta Sans", sans-serif', size: 12, weight: '600' },
                    boxWidth: 12,
                    padding: 16,
                },
            },
            tooltip: {
                backgroundColor: colors.tooltipBg,
                titleColor: '#ffffff',
                bodyColor: '#d4d4d8',
                padding: 12,
                cornerRadius: 10,
            },
        },
        scales: isDonut
            ? {}
            : {
                x: horizontal ? valueAxis : categoryAxis,
                y: horizontal ? categoryAxis : valueAxis,
            },
    };
}

function createChart(canvas, config) {
    const colors = themeColors();

    return new Chart(canvas, {
        type: config.type,
        data: {
            labels: config.labels,
            datasets: config.datasets,
        },
        options: buildOptions(config, colors),
    });
}

function registerDashboardCharts() {
    document.addEventListener('alpine:init', () => {
        Alpine.data('dashboardChart', (config) => ({
            chart: null,
            observer: null,
            config,

            init() {
                this.renderChart();
                this.observer = new MutationObserver(() => this.renderChart());
                this.observer.observe(document.documentElement, {
                    attributes: true,
                    attributeFilter: ['class'],
                });
            },

            renderChart() {
                const canvas = this.$refs.canvas;

                if (! canvas) {
                    return;
                }

                if (this.chart) {
                    this.chart.destroy();
                }

                this.chart = createChart(canvas, this.config);
            },

            destroy() {
                this.chart?.destroy();
                this.observer?.disconnect();
            },
        }));

        Alpine.data('dashboardDispensingChart', (payload) => ({
            chart: null,
            observer: null,
            config: payload.config,
            url: payload.url,
            drug: '',
            loading: false,

            init() {
                this.renderChart();
                this.observer = new MutationObserver(() => this.renderChart());
                this.observer.observe(document.documentElement, {
                    attributes: true,
                    attributeFilter: ['class'],
                });
            },

            renderChart() {
                const canvas = this.$refs.canvas;

                if (! canvas || ! this.config) {
                    return;
                }

                if (this.chart) {
                    this.chart.destroy();
                }

                this.chart = createChart(canvas, this.config);
            },

            async reload() {
                if (! this.url) {
                    return;
                }

                this.loading = true;

                try {
                    const { data } = await window.axios.get(this.url, {
                        params: { drug: this.drug || undefined },
                    });
                    this.config = data;
                    this.renderChart();
                } finally {
                    this.loading = false;
                }
            },

            destroy() {
                this.chart?.destroy();
                this.observer?.disconnect();
            },
        }));
    });
}

registerDashboardCharts();
