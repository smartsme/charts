<?php

    echo $this->Html->css('form');

    echo $this->Form->create(null, [
        'class' => 'col-2 position-absolute top-50 start-50 p-4 rounded-3 row d-flex justify-content-center',
        'url' => [
            'prefix' => 'Admin',
            'controller' => 'Users',
            'action' => 'update',
        ],
    ]);
    echo '<h1 class="pb-3">Smartsme</h1>';
    echo $this->Form->select('user_id', $users, ['class' => 'form-control w-100 mb-3 mx-auto', 'label' => 'Użytkownik']);
    echo $this->Form->control('login', ['class' => 'form-control w-100 mb-3 mx-auto', 'label' => 'Login']);
    echo $this->Form->control('first_name', ['class' => 'form-control w-100 mb-3 mx-auto', 'label' => 'Imię']);
    echo $this->Form->control('last_name', ['class' => 'form-control w-100 mb-3 mx-auto', 'label' => 'Nazwisko']);
    echo $this->Form->control('email', ['class' => 'form-control w-100 mb-3 mx-auto', 'label' => 'Email']);
    echo $this->Form->control('password', ['class' => 'form-control w-100 mb-3 mx-auto', 'label' => 'Hasło']);
    echo $this->Form->control('password_confirm', ['class' => 'form-control w-100 mb-3 mx-auto', 'label' => 'Potwierdź hasło', 'type' => 'password']);
    echo $this->Form->control('token', ['type' => 'hidden']);
    echo $this->Form->control('id', ['type' => 'hidden']);
    echo $this->Form->button('Edytuj użytkownika', ['class' => 'btn btn-primary']);
    echo $this->Flash->render('update');
    echo $this->Form->end();
