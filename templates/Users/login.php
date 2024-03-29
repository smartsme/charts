<?php

    echo $this->Html->css('form');
    echo $this->Html->css('login');

    echo $this->Form->create(null, [
        'class' => 'col-10 col-sm-6 col-lg-5 col-xl-3 col-xxl-2 offset-lg-0 offset-xl-3 position-absolute top-50 start-50 p-4 rounded-3 row d-flex justify-content-center',
        'url' => [
            'controller' => 'Users',
            'action' => 'login',
        ],
    ]);
    echo '<h1 class="pb-3">Smartsme</h1>';
    echo $this->Form->control('login', ['class' => 'form-control w-100 mb-3 mx-auto', 'label' => 'Login']);
    echo $this->Form->control('password', ['class' => 'form-control w-100 mb-3 mx-auto', 'label' => 'Hasło']);
    echo $this->Html->link(
        'Zapomniałem hasła',
        ['prefix' => null, 'controller' => 'Users', 'action' => 'forgotPassword'],
        ['class' => 'text-center mb-4'],
    );
    echo $this->Form->button('Zaloguj się', ['class' => 'btn btn-primary']);
    echo $this->Flash->render('authError');
    echo $this->Flash->render('passwordReset');
    echo $this->Form->end();
