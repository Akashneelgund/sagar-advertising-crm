/**
 * Sagar Advertising CRM - Charts & Analytics Initializer
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Dashboard Monthly Quotation Value & Sales Trend Chart
    const monthlySalesCtx = document.getElementById('monthlySalesChart');
    if (monthlySalesCtx && window.MONTHLY_SALES_DATA) {
        new Chart(monthlySalesCtx, {
            type: 'line',
            data: {
                labels: window.MONTHLY_SALES_DATA.labels,
                datasets: [
                    {
                        label: 'Total Quotation Value (₹)',
                        data: window.MONTHLY_SALES_DATA.values,
                        borderColor: '#FF5500',
                        backgroundColor: 'rgba(255, 85, 0, 0.08)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 3,
                        pointBackgroundColor: '#FF5500',
                        pointRadius: 4
                    },
                    {
                        label: 'Approved / Converted Sales (₹)',
                        data: window.MONTHLY_SALES_DATA.approved_values,
                        borderColor: '#10B981',
                        backgroundColor: 'transparent',
                        borderDash: [5, 5],
                        borderWidth: 2,
                        pointBackgroundColor: '#10B981',
                        pointRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { boxWidth: 12, font: { family: 'Plus Jakarta Sans', size: 12, weight: '600' } }
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.dataset.label}: ₹${ctx.parsed.y.toLocaleString('en-IN')}`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#E2E8F0' },
                        ticks: {
                            callback: (v) => '₹' + (v >= 100000 ? (v / 100000).toFixed(1) + 'L' : (v / 1000).toFixed(0) + 'k')
                        }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // 2. Quotation Status Breakdown (Doughnut)
    const statusCtx = document.getElementById('quotationStatusChart');
    if (statusCtx && window.STATUS_BREAKDOWN_DATA) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: window.STATUS_BREAKDOWN_DATA.labels,
                datasets: [{
                    data: window.STATUS_BREAKDOWN_DATA.counts,
                    backgroundColor: [
                        '#10B981', // Approved (green)
                        '#FF5500', // Converted (orange)
                        '#3B82F6', // Sent (blue)
                        '#F59E0B', // Under Discussion (amber)
                        '#94A3B8', // Draft (gray)
                        '#EF4444'  // Rejected (red)
                    ],
                    borderWidth: 2,
                    borderColor: '#FFFFFF'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 10, font: { family: 'Plus Jakarta Sans', size: 11 } }
                    }
                }
            }
        });
    }

    // 3. Service-Wise Quotation Value (Bar)
    const serviceCtx = document.getElementById('serviceRevenueChart');
    if (serviceCtx && window.SERVICE_REVENUE_DATA) {
        new Chart(serviceCtx, {
            type: 'bar',
            data: {
                labels: window.SERVICE_REVENUE_DATA.labels,
                datasets: [{
                    label: 'Quoted Value (₹)',
                    data: window.SERVICE_REVENUE_DATA.values,
                    backgroundColor: '#121417',
                    hoverBackgroundColor: '#FF5500',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `Value: ₹${ctx.parsed.x.toLocaleString('en-IN')}`
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: '#E2E8F0' },
                        ticks: {
                            callback: (v) => '₹' + (v >= 100000 ? (v / 100000).toFixed(1) + 'L' : (v / 1000).toFixed(0) + 'k')
                        }
                    },
                    y: {
                        grid: { display: false }
                    }
                }
            }
        });
    }
});
