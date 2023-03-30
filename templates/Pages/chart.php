<?php

    echo $this->Html->script('chart');
    echo $this->Html->css('chart');

?>
<div class="row d-flex justify-content-center">
    <div id="form" class="col-12 col-md-9 my-5 float-start rounded-3">
        <h1>Wykresy</h1>
        <div class="input m-5 m-md-4 m-lg-4 m-xl-1 m-xxl-1">
            <span>Tryb lepszej wydajności(nie rysuje punktów na wykresie(Domyślnie wyłączone))</span>
            <input type="checkbox" id="draw" class="form-check-input">
        </div>
        <div class="input m-5 m-md-4 m-lg-4 m-xl-1 m-xxl-1">
            <span>Używaj sumy zamiast średniej dla danych godzinowych</span>
            <input type="checkbox" id="sum" class="form-check-input" name="sum">
        </div>
        <div class="input m-5 m-md-4 m-lg-4 m-xl-1 m-xxl-1">
            <span>Waluta</span>
            <select class="currency form-select">
                <option value="PLN">PLN</option>
                <option value="USD">USD</option>
                <option value="EUR">EUR</option>
            </select>
        </div>
        <div class="row">
            <div class="col col-12 col-xxl-6 float-start px-4 py-xxl-4">
                <div class="input px-1 float-start w-100 shadow p-3 m-2 rounded-3">
                    <span>Data rozpoczęcia wykresu</span>
                    <input type="date" name="start" id="start" class="form-control float-start">
                </div>
                <div class="input px-1 float-start w-100 shadow p-3 m-2 rounded-3">
                    <span>Data zakończenia wykresu</span>
                    <input type="date" name="end" id="end" class="form-control float-start">
                </div>
                <div class="input float-start w-100 shadow p-3 m-2 rounded-3">
                    <input type="checkbox" class="form-check-input" data-table="generation_from_wind_sources">
                    <span>Generacja źródeł wiatrowych i fotowoltaicznych</span>
                </div>
                <div class="input float-start w-100 shadow p-3 m-2 rounded-3">
                    <input type="checkbox" class="form-check-input" data-table="inter_system_exchange_of_power_flows">
                    <span>Wymiana Międzysystemowa - przepływy mocy</span>
                </div>
                <div class="input float-start w-100 shadow p-3 m-2 rounded-3">
                    <input type="checkbox" class="form-check-input" data-table="kse_demands">
                    <span>Zapotrzebowania mocy KSE</span>
                </div>
                <div class="input float-start w-100 shadow p-3 m-2 rounded-3">
                    <input type="checkbox" class="form-check-input" data-table="prices_and_quantity_of_energy_in_the_balancing_market">
                    <span>Ceny i ilości energii na rynku bilansującym</span>
                </div>
                <div class="input float-start w-100 shadow p-3 m-2 rounded-3">
                    <input type="checkbox" class="form-check-input" data-table="generation_of_power_generation_units" data-daily="true">
                    <span>Generacja jednostek wytwórczych</span>
                    <select class="code form-select"></select>
                </div>
                <div class="input float-start w-100 shadow p-3 m-2 rounded-3">
                    <input type="checkbox" class="form-check-input" data-table="electric_otf_energy" data-daily="true">
                    <span>Energia elektryczna OTF</span>
                    <select class='code form-select'></select>
                </div>
                <div class="input float-start w-100 shadow p-3 m-2 rounded-3">
                    <input type="checkbox" class="form-check-input" data-table="electric_rdn_energy" data-daily="true">
                    <span>Energia elektryczna RDN</span>
                    <select class='code form-select'></select>
                </div>
            </div>
            <div class="col col-12 col-xxl-6 float-start px-4 pt-xxl-4 pb-4">
                <div class="input float-start w-100 shadow p-3 m-2 rounded-3">
                    <input type="checkbox" class="form-check-input" data-table="otf_gas" data-daily="true">
                    <span>Gaz OTF</span>
                    <select class='code form-select'></select>
                </div>
                <div class="input float-start w-100 shadow p-3 m-2 rounded-3">
                    <input type="checkbox" class="form-check-input" data-table="property_rights_of_rpm_off_session" data-daily="true">
                    <span>Prawa majątkowe RPM poza-sesyjne</span>
                    <select class='code form-select'></select>
                </div>
                <div class="input float-start w-100 shadow p-3 m-2 rounded-3">
                    <input type="checkbox" class="form-check-input" data-table="property_rights_of_rpm_session" data-daily="true">
                    <span>Prawa majątkowe RPM sesyjne</span>
                    <select class='code form-select'></select>
                </div>
                <div class="input float-start w-100 shadow p-3 m-2 rounded-3">
                    <input type="checkbox" class="form-check-input" data-table="rdb_gas" data-daily="true">
                    <span>Gaz RDB</span>
                    <select class='code form-select'></select>
                </div>
                <div class="input float-start w-100 shadow p-3 m-2 rounded-3">
                    <input type="checkbox" class="form-check-input" data-table="rdn_gas_contract" data-daily="true">
                    <span>Gaz RDN kontrakty</span>
                    <select class='code form-select'></select>
                </div>
                <div class="input float-start w-100 shadow p-3 m-2 rounded-3">
                    <input type="checkbox" class="form-check-input" data-table="rdn_gas_index" data-daily="true">
                    <span>Gaz RDN indeksy</span>
                    <select class='code form-select'></select>
                </div>
                <div class="input float-start w-100 shadow p-3 m-2 rounded-3">
                    <input type="checkbox" class="form-check-input" data-table="carbon_emissions">
                    <span>Kontrakty terminowe na emisję CO2</span>
                </div>
                <div class="input float-start w-100 shadow p-3 m-2 rounded-3">
                    <input type="checkbox" class="form-check-input" data-table="coal_api2">
                    <span>Coal (API2) CIF ARA (ARGUS-McCloskey) Futures</span>
                </div>
                <div class="input">
                    <button id="submitBtn" class="btn btn-dark m-2">Wyświetl</button>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" value='<?=isset($data) ? json_encode($data) : '[]';?>' id="data"/>
    <input type="hidden" value='<?=isset($labels) ? json_encode($labels) : '[]';?>' id="labels"/>
    <div id="chartContainer" class="col-12 col-lg-10">
        <canvas id="chart"></canvas>
    </div>
</div>