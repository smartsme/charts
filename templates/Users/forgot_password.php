<?php

    echo $this->Html->css('form');

    echo $this->Form->create(null, [
        'class' => 'col-10 col-sm-6 col-lg-5 col-xl-3 col-xxl-2 position-absolute top-50 start-50 p-4 rounded-3 row d-flex justify-content-center',
        'url' => [
            'controller' => 'Users',
            'action' => 'forgotPassword',
        ],
    ]);
    echo '<h1 class="pb-3">Smartsme</h1>';
    echo $this->Form->control('email', ['class' => 'form-control w-100 mb-3 mx-auto', 'label' => 'Email']);
    echo $this->Form->button('Wyślij email ze zmianą hasła', ['class' => 'btn btn-primary']);
    echo $this->Flash->render('forgotPassword');
    echo $this->Form->end();
