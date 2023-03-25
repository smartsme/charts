<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class UsersTable extends Table
{
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->notEmptyString('login', 'Login nie może być pusty.')
            ->notEmptyString('first_name', 'Imię nie może być puste.')
            ->notEmptyString('last_name', 'Nazwisko nie może być puste.')
            ->notEmptyString('email', 'Email nie może być pusty.');

        return $validator;
    }
}
