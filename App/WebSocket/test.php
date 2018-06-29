<?php 
	$a=[
    "controller"=>"Chat",
    "action"=>"hello",
    "content"=>['a'=>1,'b'=>1]
];
echo json_encode($a);