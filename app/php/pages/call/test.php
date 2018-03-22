<?php
require_once(DIR_APP);
$app = new app(['crud']);
$app->load(['unitTest'],['crud']);

if(isset($_URL[2]))  {

$app->unitTest->run([
    'crud'=>[
        'save'=>[
            'message'=>'Must return 1 or 0',
            'and'=>[['==',0]],
        ],
        'update'=>[
            'message'=>'Must return 1 or 0',
            'and'=>[['==',0]],
        ],
        'delete'=>[
            'message'=>'Must return 1 or 0',
            'and'=>[['==',0]],
        ],
        'count'=>[
            'message'=>'Must return 1 or 0',
            'and'=>[['==',0]],
        ],
        'get'=>[
            'func'=>'is_array',
            'func_vars'=>['_val'],
            'message'=>'Must return an array'
        ],
        'search'=>[
            'func'=>'array_key_exists',
            'func_vars'=>['hits','_val'],
            'message'=>'hits key must exist'
        ]
    ]
]);

}
?>
<style> table{width:100%;}tr:nth-child(even){background:#f3f3f3;}td {padding:7px 15px;}body{font-family:Consolas,Monaco,Lucida Console,Courier New;}</style>
<button onclick='window.location.href = "/call/test/run"' style='width:100%;font-size:18px;padding:10px;'>Run Test</button>
<?php exit; ?>
