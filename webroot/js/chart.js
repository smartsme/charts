$(() => {
    const ctx = $('#chart');
    const data = JSON.parse($('#data').val());
    const labels = JSON.parse($('#labels').val());

    let datasets = [];
    for (let d in data) {
        datasets.push({
            label: d,
            data: data[d],
            borderWidth: 1
        })
    }

    let params = new URLSearchParams(window.location.search);

    $('#start').val(params.get('start'));
    $('#end').val(params.get('end'));
    $('#sum').prop('checked', JSON.parse(params.get('sum')));
    for (let i = 0; i < params.getAll('table').length; i++) {
        $(`input[data-table=${params.getAll('table')[i]}]`).prop('checked', true);
    }

    for (let i = 0; i < $('input[data-daily=true]').siblings('select').length; i++) {
        generateOptions($('input[data-daily=true]').siblings('select').eq(i), $('input[data-daily=true]').eq(i).data('table'));
    }

    function generateOptions(select, table) {
        $.ajax({
            url: `get-codes?table=${table}`,
            success: (res) => {
                let codes = res.flat();
                let html = '';
                for (let i = 0; i < codes.length; i++) {
                    html += `<option value='${codes[i]}'>${codes[i]}</option>`;
                }
                select.html(html);
            }
        });
    }

    $('#submitBtn').click(function() {
        params.delete('table');
        params.delete('code');
        params.delete('mode');
        $('input:checked:not(#draw):not(#sum)').each(function() {
            params.append('table', $(this).data('table'));
            if ($(this).siblings('.code').length) {
                params.append('code', $(this).siblings('.code').eq(0).val());
            }
            if ($(this).siblings('.mode').length) {
                params.append('mode', $(this).siblings('.mode').eq(0).val());
            }
        })
        params.set('start', $('#start').val());
        params.set('end', $('#end').val());
        params.set('sum', $('#sum').is(':checked'));
        location.replace(`${window.location.origin}${window.location.pathname}?${params.toString()}`)
    });

    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            spanGaps: 1, 
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
            },
            scales: {
                y: {
                    border: {
                        color: '#fff',
                        display: true,
                        width: 1
                    },
                    beginAtZero: true,
                    color: '#fff',
                    ticks: {
                        color: '#fff'
                    }
                },
                x: {
                    border: {
                        color: '#fff',
                        display: true,
                        width: 1
                    },
                    color: '#fff',
                    ticks: {
                        maxTicksLimit: 15,
                        color: '#fff'
                    }
                }
            },
            animations: {
                y: {
                    easing: 'easeInOutElastic',
                    from: (ctx) => {
                        if (ctx.type === 'data') {
                            if (ctx.mode === 'default' && !ctx.dropped) {
                                ctx.dropped = true;
                                return 0;
                            }
                        }
                    }
                }
            },
        },
        plugins: [{
            afterDraw: chart => {
                if (chart.tooltip?._active?.length) {               
                    let x = chart.tooltip._active[0].element.x;
                    let y = chart.tooltip._active[0].element.y;            
                    let yAxis = chart.scales.y;
                    let xAxis = chart.scales.x;
                    let ctx = chart.ctx;
                    ctx.save();
                    ctx.beginPath();
                    ctx.moveTo(x, yAxis.top);
                    ctx.lineTo(x, yAxis.bottom);
                    ctx.lineWidth = 0.5;
                    ctx.strokeStyle = '#a3a3a3';
                    ctx.moveTo(xAxis.left, y);
                    ctx.lineTo(xAxis.right, y);
                    ctx.lineWidth = 0.5;
                    ctx.strokeStyle = '#a3a3a3';
                    ctx.stroke();
                    ctx.restore();
                }
            }
        }],
    });

    $('#draw').change(function() {
        chart.options.datasets = {
            line: {
                pointRadius: !$(this).prop('checked') ? 3 : 0,
                pointHoverRadius: !$(this).prop('checked') ? 6 : 3
            }
        }
        chart.update();
    })
})
