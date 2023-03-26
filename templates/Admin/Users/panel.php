<?php
    echo $this->Html->css('panel');
    echo '<div class="content col-10 col-sm-6 col-lg-5 col-xl-3 col-xxl-2 position-absolute top-50 start-50 p-4 rounded-3 row d-flex justify-content-center">';
    echo $this->Html->link(
        'Stwórz użytkownika',
        ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'create'],
        ['class' => 'h5'],
    );
    echo '<br />';
    echo $this->Html->link(
        'Edytuj użytkownika',
        ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'update'],
        ['class' => 'h5'],
    );
    echo '<br />';
    echo $this->Html->link(
        'Usuń użytkownika',
        ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'delete'],
        ['class' => 'h5'],
    );
    echo '<br />';
    echo $this->Html->link(
        'Lista użytkowników',
        ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'list'],
        ['class' => 'h5'],
    );
    echo '</div>';
