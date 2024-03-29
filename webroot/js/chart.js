$(() => {
    const ctx = $('#chart');
    let data = JSON.parse($('#data').val());
    const labels = JSON.parse($('#labels').val());
    const params = new URLSearchParams(window.location.search);
    const startDate = params.get('start') ?? '2023-01-01';
    const endDate = params.get('end') ?? `${new Date().getFullYear()}-${String(new Date().getMonth() + 1).padStart(2, '0')}-${String(new Date().getDate()).padStart(2, '0')}`;

    $('select.code').each(function() {
        $(this).select2({});
    });

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
        'volume': 'KL',
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

    //Populating datasets for chart.js
    function generateDatasets() {
        data = JSON.parse($('#data').val());
        let datasets = [];
        for (let d in data) {
            if (unqiueUnits.indexOf(getUnit(d)) == -1) {
                unqiueUnits.push(getUnit(d));
            }
            // if (dataWithPrice.some(x => d.startsWith(x))) {
            //     let rates = JSON.parse(localStorage.getItem('rates'));
            //     for (let i = 0; i < data[d].length; i++) {
            //         if (d === 'value - brent_oil') {
            //             data[d][i]['y'] = +data[d][i]['y'] * rates['USD'][data[d][i]['x']] / rates[currency][data[d][i]['x']]
            //         } else {
            //             data[d][i]['y'] = +data[d][i]['y'] * rates[currency][data[d][i]['x']]
            //         }
            //     }
            // }
            datasets.push({
                label: d,
                data: data[d],
                borderWidth: 1
            })
        }

        //Rendering units that are first of all filtered so that no undefined is present in final html and then replacing units with units wrapped with paragraphs
        $('div#units').html(unqiueUnits.filter(x => x != undefined).map(x => `<p>${x}</p>`).toString().replaceAll(',', ''));
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

    //Settings values of inputs from url
    $('#start').val(startDate);
    $('#end').val(endDate);
    $('#sum').prop('checked', JSON.parse(params.get('sum')));

    let tables = JSON.parse(params.get('tables'));

    //Select2 behavior e.g. when checkbox is unchecked all the codes are automatically unselected or when users selects a code then checkbox will be automatically checked
    let selects = $('input[data-daily=true]').siblings('select');
    for (let i = 0; i < selects.length; i++) {
        selects.eq(i).on('select2:select', function() {
            $(this).siblings('input').prop('checked', true);
        });

        selects.eq(i).on('select2:unselect', function(e) {
            $(this).siblings('input').prop('checked', String($(this).val()) == '' ? false : true);
        });

        selects.eq(i).siblings('input').on('change', function() {
            if (!$(this).is(':checked')) {
                selects.eq(i).val(null).trigger('change');
            }
        })
        generateOptions(selects.eq(i), $('input[data-daily=true]').eq(i).data('table'), tables?.filter(_ => _.tableName == selects.eq(i).attr('id'))[0]?.codes);
    }

    if (tables) {
        for (let i = 0; i < tables.length; i++) {
            $(`input[data-table=${tables[i].tableName}]`).prop('checked', true);
        }
    }

    function generateOptions(select, table, selectedCodes) {
        $.ajax({
            url: `get-codes?table=${table}`,
            success: (res) => {
                let codes = res.flat();
                let data = [];
                for (let code of codes) {
                    data.push({ id: code, text: code, selected: selectedCodes?.includes(code) });
                }
                select.select2({
                    data: data,
                    closeOnSelect: false,
                    placeholder: 'Wybierz kod'
                });
            }
        });
        
    }

    //Collecting data from the form and putting it in the url
    $('#submitBtn').click(function() {
        let tables = [];
        $('input:checked:not(#draw):not(#sum)').each(function() {
            tables.push({ tableName : $(this).data('table'), codes: $(this).siblings('.code').eq(0).val() })
        })
        params.set('start', $('#start').val());
        params.set('end', $('#end').val());
        params.set('sum', $('#sum').is(':checked'));
        params.set('tables', JSON.stringify(tables));
        location.replace(`${window.location.origin}${window.location.pathname}?${params.toString()}`);
    });

    //Chart config
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
                let ctx = chart.ctx;
                let yAxis = chart.scales.y;
                let xAxis = chart.scales.x;

                //Plugin for making the cross lines on the cursor
                if (chart.tooltip?._active?.length) {               
                    let x = chart.tooltip._active[0].element.x;
                    let y = chart.tooltip._active[0].element.y;            
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
                //Plugin for the no data text when no data is present
                if (chart.data.datasets.length === 0) {
                    let width = chart.width;
                    let height = chart.height
                    
                    ctx.save();
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillStyle = 'white';
                    ctx.font = '20px Comfortaa';
                    ctx.fillText('Brak danych', width / 2, height / 2);
                    ctx.fillText('spełniających podane kryteria', width / 2, height / 2 + 35);
                    ctx.restore();
                } else {
                    //Plugin for displaying a line from left to right on chart on value 0 of the yAxis
                    ctx.moveTo(xAxis.left, yAxis.getPixelForValue(0));
                    ctx.lineTo(xAxis.right, yAxis.getPixelForValue(0));
                    ctx.lineWidth = 1;
                    ctx.strokeStyle = '#ffffff';
                    ctx.stroke();
                    ctx.restore();
                }
            }
        }],
    });

    //Drawing the charts points only if performance mode is not selected
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
