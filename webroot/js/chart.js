$(() => {
    const ctx = $('#chart');
    let data = JSON.parse($('#data').val());
    const labels = JSON.parse($('#labels').val());
    const params = new URLSearchParams(window.location.search);
    const startDate = params.get('start') ?? '2023-01-01';
    const endDate = params.get('end') ?? `${new Date().getFullYear()}-${String(new Date().getMonth() + 1).padStart(2, '0')}-${String(new Date().getDate()).padStart(2, '0')}`;

    let currency = localStorage.getItem('currency') ?? 'PLN';
    $('select.currency').val(currency);

    $.ajax({
        url: `get-rates?start_date=${startDate}&end_date=${endDate}`,
        success: (res) => {
            localStorage.setItem('rates', JSON.stringify(res));
        }
    });

    const dataWithPrice = [
        'value',
        'price',
        'first_transaction_rate',
        'dkr',
        'session_min',
        'session_max',
        'total_value_of_turnover',
        'course',
        'cro',
        'cros',
        'croz',
        'rate_min',
        'rate_max',
    ];

    const units = {
        'value - brent_oil': `${currency}/baryłka`,
        'value - carbon_emissions': `${currency}`,
        'price - coal_api2': `${currency}`,
        'open - coal_api2': `${currency}`,
        'high - coal_api2': `${currency}`,
        'low - coal_api2': `${currency}`,
        'volume': 'K',
        'first_transaction_rate': `${currency}/MWh`,
        'dkr': `${currency}/MWh`,
        'session_min': `${currency}/MWh`,
        'session_max': `${currency}/MWh`,
        'total_value_of_turnover': `${currency}`,
        'total_volumen': 'MWh',
        'lop': 'MWh',
        'course': `${currency}/MWh`,
        'change': '%',
        'volume - electric_rdn_energy': 'MWh',
        'wind': 'MWh',
        'solar': 'MWh',
        'Generacja': 'MW',
        'Pompowanie': 'MW',
        'ceps_export': 'MWh',
        'ceps_import': 'MWh',
        'seps_export': 'MWh',
        'seps_import': 'MWh',
        '50hertz_export': 'MWh',
        '50hertz_import': 'MWh',
        'svk_export': 'MWh',
        'svk_import': 'MWh',
        'nek_export': 'MWh',
        'nek_import': 'MWh',
        'litgrid_export': 'MWh',
        'litgrid_import': 'MWh',
        'predicted': 'MW',
        'actual': 'MW',
        'cro': `${currency}/MWh`,
        'cros': `${currency}/MWh`,
        'croz': `${currency}/MWh`,
        'contract_status': 'MW',
        'imbalance': 'MW',
        'course_change': '%',
        'volume_change': '%',
        'price_change': '%',
        'rate_min': `${currency}/MWh`,
        'rate_max': `${currency}/MWh`,
    };

    let unqiueUnits = [];

    function generateDatasets() {
        data = JSON.parse($('#data').val());
        let datasets = [];
        for (let d in data) {
            if (unqiueUnits.indexOf(getUnit(d)) == -1) {
                unqiueUnits.push(getUnit(d));
            }
            if (dataWithPrice.some(x => d.startsWith(x))) {
                let rates = JSON.parse(localStorage.getItem('rates'));
                for (let i = 0; i < data[d].length; i++) {
                    if (d === 'value - brent_oil') {
                        data[d][i]['y'] = data[d][i]['y'] * rates['USD'][data[d][i]['x']] / rates[currency][data[d][i]['x']]
                    } else {
                        console.log(rates);
                        data[d][i]['y'] = data[d][i]['y'] * rates[currency][data[d][i]['x']]
                    }
                }
            }
            datasets.push({
                label: d,
                data: data[d],
                borderWidth: 1
            })
        }

        $('div#units').html(unqiueUnits.filter(x => x != undefined).toString().replaceAll(',', '<br/>'));
        return datasets;
    }

    function getUnit(s) {
        for (let i = 0; i < Object.keys(units).length; i++) {
            if(s.includes(Object.keys(units)[i])) {
                return units[Object.keys(units)[i]];
            }
        }
    }

    let datasets = generateDatasets();

    $('#start').val(startDate);
    $('#end').val(endDate);
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

    $('select.currency').change(function() {
        localStorage.setItem('currency', $(this).val());
        currency = $(this).val();
        $.ajax({
            url: `get-rates?start_date=${startDate}&end_date=${endDate}&currency=${localStorage.getItem('currency')}`,
            success: (res) => {
                console.log(res);
                localStorage.setItem('rates', JSON.stringify(res));
                let datasets = generateDatasets();
                chart.data.datasets = datasets;
                chart.update()
            }
        });
        
    })
})
