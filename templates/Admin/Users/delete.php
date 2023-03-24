<?php

    echo $this->Html->css('form');

    echo $this->Form->create(null, [
        'class' => 'col-2 position-absolute top-50 start-50 p-4 rounded-3 row d-flex justify-content-center',
        'url' => [
            'prefix' => 'Admin',
            'controller' => 'Users',
            'action' => 'delete',
        ],
    ]);
    echo '<h1 class="pb-3">Smartsme</h1>';
    echo $this->Form->select('user_id', $users, ['class' => 'form-control w-100 mb-3 mx-auto', 'label' => 'Użytkownik']);
    echo $this->Form->button('Usuń użytkownika', ['class' => 'btn btn-primary']);
    echo $this->Flash->render('delete');
    echo $this->Form->end();
