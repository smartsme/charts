<?php
    echo $this->Html->link(
        'Stwórz użytkownika',
        ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'create'],
    );
    echo '<br />';
    echo $this->Html->link(
        'Edytuj użytkownika',
        ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'update'],
    );
    echo '<br />';
    echo $this->Html->link(
        'Usuń użytkownika',
        ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'delete'],
    );
