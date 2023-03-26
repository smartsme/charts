<?php
    echo $this->Html->css('home');
    echo '<div class="content col-10 col-sm-6 col-lg-5 col-xl-3 col-xxl-2 position-absolute top-50 start-50 p-4 rounded-3 row d-flex justify-content-center">';
    echo $this->Html->link(
        'Wykresy',
        ['prefix' => null, 'controller' => 'Pages', 'action' => 'chart'],
        ['class' => 'h5'],
    );
    if ($is_admin) {
        echo '<br />';
        echo $this->Html->link(
            'Panel administratora',
            ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'panel'],
            ['class' => 'h5'],
        );
    }
    echo '<br />';
    echo $this->Html->link(
        'Wyloguj się',
        ['prefix' => null, 'controller' => 'Users', 'action' => 'logout'],
        ['class' => 'h5'],
    );
    echo '</div>';
