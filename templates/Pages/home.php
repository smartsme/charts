<?php
    echo $this->Html->link(
        'Wykresy',
        ['prefix' => null, 'controller' => 'Pages', 'action' => 'chart'],
    );
    if ($is_admin) {
        echo '<br />';
        echo $this->Html->link(
            'Panel administratora',
            ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'panel'],
        );
    }
    echo '<br />';
    echo $this->Html->link(
        'Wyloguj się',
        ['prefix' => null, 'controller' => 'Users', 'action' => 'logout'],
    );
