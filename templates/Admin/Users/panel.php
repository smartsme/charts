<?php
    echo $this->Html->link(
        'Stwórz użytkownika',
        ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'create'],
    );
    debug($users);
