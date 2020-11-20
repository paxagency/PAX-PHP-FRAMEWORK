<?php

if(isset($_URL[2]))  {
$app->get('tests')->run([
    'modelExample'=>[
        'getString'=>[
            'func'=>'is_string',
            'func_vars'=>['_val'],
            'message'=>'Must return an array'
        ],
        'getNumber'=>[
            'message'=>'Must return 1 or 0',
            'and'=>[['==',0]],
        ],
        'getArray'=>[
            'func'=>'is_array',
            'func_vars'=>['_val'],
            'message'=>'Must return an array'
        ],
        'getAssoc'=>[
            'func'=>'is_array',
            'func_vars'=>['_val'],
            'message'=>'Must return an array'
        ]
    ]
]);
echo $app->tests->html;
}
?>
<style> table{width:100%;}tr:nth-child(even){background:#f3f3f3;}td {padding:7px 15px;}body{font-family:Consolas,Monaco,Lucida Console,Courier New;}</style>
<button onclick='window.location.href = "/api/test/run"' style='width:100%;font-size:18px;padding:10px;'>Run Test</button>
<?php exit; ?>
