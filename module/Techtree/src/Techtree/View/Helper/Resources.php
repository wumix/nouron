<?php
$resources = array(
    0 => array('name' => 'tick', 'class'=> 'glyphicon glyphicon-time'),
    1 => array('name' => 'tick', 'class'=> 'glyphicon glyphicon-time'),
    2 => array('name' => 'tick', 'class'=> 'glyphicon glyphicon-time'),
    3 => array('name' => 'tick', 'class'=> 'glyphicon glyphicon-time'),
    4 => array('name' => 'tick', 'class'=> 'glyphicon glyphicon-time'),
    5 => array('name' => 'tick', 'class'=> 'glyphicon glyphicon-time'),
    6 => array('name' => 'tick', 'class'=> 'glyphicon glyphicon-time'),
    7 => array('name' => 'tick', 'class'=> 'glyphicon glyphicon-time')
);
foreach ($costs as $cost):
    $name = $resources[ $cost['resource_id'] ]['name'];
    $class = $resources[ $cost['resource_id'] ]['class'];
    $amount = $this->escapeHtml($cost['amount']);
    echo '<a data-placement="bottom" rel="tooltip" href="#" data-original-title="'.$name.'">';
    echo '<i class="'.$class.'"></i>'.$amount.'</a>';
endforeach; ?>
