<?php

    echo $this->Html->css('form');

    echo $this->Form->create(null, [
        'class' => 'col-2 position-absolute top-50 start-50 p-4 rounded-3 row d-flex justify-content-center',
    ]);
    echo '<h1 class="pb-3">Smartsme</h1>';
    echo $this->Form->control('password', ['class' => 'form-control w-100 mb-3 mx-auto', 'label' => 'Hasło']);
    echo $this->Form->control('password_confirm', ['class' => 'form-control w-100 mb-3 mx-auto', 'label' => 'Potwierdź hasło', 'type' => 'password']);

    echo $this->Form->button('Zmień hasło', ['class' => 'btn btn-primary']);
    echo $this->Flash->render('resetPassword');
    echo $this->Form->end();
