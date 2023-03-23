<?php
    echo $this->Html->link(
        'Wykresy',
        ['prefix' => null, 'controller' => 'Pages', 'action' => 'chart'],
    );

    echo $this->Html->link(
        'Panel administratora',
        ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'panel'],
    );
