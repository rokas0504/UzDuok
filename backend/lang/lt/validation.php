<?php
return [
    'confirmed' => 'Lauko :attribute patvirtinimas nesutampa',
    'unique' => ':Attribute jau užimtas',
    'required' => 'Laukas yra būtinas',
    'required_if' => 'Laukas yra būtinas',
    'required_with' => 'Privaloma užpildyti lauką, kai pateikta :values',
    'string' => 'Laukas :attribute turi būti tekstinis',
    'numeric' => 'Laukas :attribute turi būti skaičius',
    'date' => ':Attribute reikšmė nėra galiojanti data',
    'digits_between' => ':Attribute turi būti nuo :min iki :max skaitmenų',
    'exists' => 'Pasirinktas :attribute yra neteisingas',
    'email' => ':Attribute turi būti galiojantis, pvz.: vardas.pavarde@pavyzdys.lt',
    'url' => 'Neteisinga nuoroda. Naudokite formatą, pvz.: http://www.tavo-web-pavadinimas.lt',
    'file' => 'Turi būti failas',
    'mimes' => ':Attribute turi būti vienas iš šių tipų: :values',
    'after' => 'Pabaigos laikas turi būti vėlesnis nei pradžios laikas',

    'password' => [
        'symbols' => 'Slaptažodis turi turėti bent vieną simbolį',
        'mixed' => 'Slaptažodis turi turėti bent vieną didžiąją ir mažąją raidę',
        'numbers' => 'Slaptažodis turi turėti bent vieną skaičių'
    ],

    'min' => [
        'string' => 'Laukas :attribute turi turėti ne mažiau nei :min simbolių',
        'numeric' => 'Laukas :attribute turi būti ne mažiau nei :min',
    ],
    'max' => [
        'file' => 'Failo dydis neturi viršyti :max kilobaitų',
        'string' => 'Laukas :attribute turi turėti ne daugiau nei :max simbolių',
    ],
    'lte' => [
        'end_days_before' => 'Registracijos pabaigos dienų skaičius negali būti didesnis nei pradžios dienų skaičius'
    ],

    'attributes' => [
        'email' => 'el. paštas',
    ],
];
