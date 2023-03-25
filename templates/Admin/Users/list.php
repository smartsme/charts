<table class="table m-5">
    <thead>
        <tr>
            <th scope='col'>L.p.</th>
            <th scope='col'>Login</th>
            <th scope='col'>Imię</th>
            <th scope='col'>Nazwisko</th>
            <th scope='col'>Email</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $i = 1;
        foreach ($users as $user) {
            echo '<tr>';
            echo '<td scope="row">' . $i . '</td>';
            echo '<td>' . $user->login . '</td>';
            echo '<td>' . $user->first_name . '</td>';
            echo '<td>' . $user->last_name . '</td>';
            echo '<td>' . $user->email . '</td>';
            echo '</tr>';
            $i++;
        }
        ?>
    </tbody>
</table>