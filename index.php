<?php

//$row = [(object)[]];

$from = [
    ['id'=>1, 'parent'=>0, 'cont'=>'cont1'],
    ['id'=>2, 'parent'=>0, 'cont'=>'cont2'],
    ['id'=>3, 'parent'=>1, 'cont'=>'cont3'],
    ['id'=>4, 'parent'=>3, 'cont'=>'cont4'],
    ['id'=>5, 'parent'=>4, 'cont'=>'cont5'],
];


function buildArray($from, $to = []) 
{  
    if (empty($from)) {
        return null; 
    }
    
    $to[array_shift($from)] = buildArray($from, $to);
    return $to;
}







echo '<pre>';
print_r(buildArray($from, $to));
echo '</pre>';
exit;