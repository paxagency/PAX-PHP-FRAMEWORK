<?php
/***********************************
docs.paxagency.com/php/libraries/elasticsearch
mit license • by albert kiteck
***********************************/

class elastic {
    public $server;
    public $port;
    public $index;
    public $ajax = false;
    public $restarting = false;
    public function __construct() {
          $this->server = DB_SERVER;
          $this->port = DB_PORT;
          $this->index = DB_NAME;
    }
    /**
	* Get one row
	* @param  string	$id
	* @param  string  	$key
	* @param  string	$index
	* @return array		
	*/
    public function get($id,$key,$index=false) {
        if($key=='id') {
            $h=  $this->call('_doc/'.$id,0,'GET',$index);
        } else {
            $a[] = ["term"=>[$key=>$id]];
            $query['query']['bool']['filter'] = $a;
            $call= $this->call('_search?size=1',$query,'GET',$index);
            $h= (is_array($call) && $call['hits'] && $call['hits']['hits']) ? $call['hits']['hits'][0] : 0;
        }
        
        if(!isset($h['_source'])) return ['error'=>'not found'];
        $h['_source']['id'] = $h['_id']; 
        $h['_source']['_seq_no']= $h['_seq_no'];
        foreach($h['_source'] as $k=>$o) if($o=='0' || $o=='1') $h['_source'][$k] = (float)$o;
        return $h['_source'];
    }
    /**
	* Search Method
	* @param  string 	$type
	* @param  array 	$query
	* @param  integer  	$max
	* @param  integer	$page
	* @param  string 	$order
	* @param  string	$sort
	* @param  string 	$after
	* @return array
	*/
    public function search($type=false,$query=[],$max=12,$page=0,$order='asc',$sort='_id',$after=false) {
            $start = $page * $max;
            $path = '_search?size='.$max.'&from='.$start;
            if($type) $query['type']=$type;
            if($sort=='id') $sort = '_id';
            $search = $this->call($path,$this->query($query,$order,$sort,$after));
            $rows = [];
            if(isset($search['error'])) return ['count'=>0,'hits'=>[],'error'=>$search['error']['root_cause']];
            foreach($search['hits']['hits'] as $h) {
                $h['_source']['id'] = $h['_id']; 
                $rows[] = $h['_source'];
            }
            $count = $search['hits']['total']['value'];
            if($count==10000) {
            	$c = $this->count($type);
            	$count = $c["count"];
            } 
            return ['count'=>$count,'hits'=>$rows];
    }
    /**
	* Count type
	* @param  string 	$type
	* @param  array 	$query
	* @param  string	$index
	* @return array		
	*/
    public function count($type=0,$query=[],$index=0) {
        if($type) $query['type']=$type;
        return $this->call('_doc/_count',$this->query($query),'POST',$index);
    }
    /**
	* Get/Set mapping object of index
	* @param  string 	$type
	* @param  array 	$query
	* @param  string	$index
	* @return array		
	*/
    public function mapping($map=0) {
        $call = ($map) ? 'PUT' : 'GET';
        $query = ($map) ? ['properties'=>$map] : 0;
        return $this->call('_mapping',$query,$call);
    }
    /**
	* Get/Set mapping object of index
	* @param  string 	$type
	* @param  array 	$query
	* @param  string	$index
	* @return array		
	*/
    public function table($map,$index=false,$settings=[]){
        //Set a normalizer for keywords so you can search lowercase and uppercase
        $normalizer = ["normalizer" => ["my_normalizer" => [
            "type" => "custom", 
            "filter" => ["lowercase"] 
        ]]];
        $settings = ($settings) ? $settings : ["index"=> ["number_of_shards"=>1,"number_of_replicas"=>1],"analysis" => $normalizer];
        //Loop through map and set all keyword fields to include this normalizer
        foreach($map as $k=>$m) if(isset($m['type']) && $m['type']=='keyword') $map[$k]['normalizer'] = 'my_normalizer';
        
        //Create a template so that dynamic fields being generated are keyword type by default
        $temp = [["strings"=>["match_mapping_type"=>"string","mapping"=>["type"=>"keyword","normalizer"=>"my_normalizer"]]]];
        $object = ["settings"=>$settings,"mappings"=>["properties"=>$map,"dynamic_templates"=>$temp]];
        return $this->call('',$object,'PUT',$index);
    }
    /**
	* List all Indexes in cluster
	* @return array		
	*/
    public function listIndexes(){
        return $this->call('_alias',[],'GET','');
    }
    /**
	* Erace and then Map Index
	* @param  string 	$db
	* @param  array 	$map
	* @return array		
	*/
    public function setup($db,$map){
        $this->reset($db);
        return $this->table($map,$db);
    }
    /**
	* Erace, Map, and Bulk import Data into Index
	* @param  string 	$db
	* @param  array 	$data
	* @param  array 	$map
	* @return array		
	*/
    public function seed($db,$data,$map=0){
        $this->reset($db); 
        if($map)  $create = $this->table($map['fields'],$db);
        $bulk = [];
        foreach($data as $type=>$dat) {
            foreach($dat as $n=>$d) {
                $d['_action'] = 'index';
                $d['_index'] = $db;
                $d['type'] = $type;
                $bulk[] = $d;
            }
        }
        return $this->bulk($bulk);
    }
    /**
	* Advanced Query conversion 
	* @param  array 	$query
	* @return array		
	*/
    public function queryConvert($qu){
        if(!isset($qu['and']) && !isset($qu['or']) && !isset($qu['query']))  return $qu;
       
        $query = [];
        foreach($qu as $key=>$que) {
            if($key!='and' && $key!='or')  break;
            if($key=='and') $key='must';
            if($key=='or') $key='should';
            foreach($que as $q) {
                switch($q[1]){
                    case "=":
                        $query[$key][] = ["match_phrase"=>[$q[0]=>$q[2]]];
                    break;
                    case "!=":
                        $query[$key][] = ['bool'=>['must_not'=>["match_phrase"=>[$q[0]=>$q[2]]]]]; 
                    break;
                    case ">":
                        $query[$key][] = ["range"=>[$q[0]=>['gt'=>$q[2]]]];
                    break;
                    case "<":
                        $query[$key][] = ["range"=>[$q[0]=>['lt'=>$q[2]]]];
                    break;
                    case ">=":
                        $query[$key][] = ["range"=>[$q[0]=>['gte'=>$q[2]]]];
                    break;
                    case "<=":
                        $query[$key][] = ["range"=>[$q[0]=>['lte'=>$q[2]]]];
                    break;
                    case "exists":
                        $query[$key][] = ["exists"=>['field'=>$q[0]]];
                    break;
                    case "empty":
                        $query[$key][] = ['bool'=>['must_not'=>["exists"=>['field'=>$q[0]]]]]; 
                    break;
                    case "contains":
                        $query[$key][] = ["query_string"=>['query'=>'*'.$q[2].'*','fields'=>[$q[0]]]];
                    break;
                    case "contains_not": 
                        $query[$key][] = ['bool'=>['must_not'=>["query_string"=>['query'=>'*'.$q[2].'*','fields'=>[$q[0]]]]]];
                    break;
                    case "*":
                        $query[$key][] = ["wildcard"=>[$q[0]=>['value'=>$q[2]]]];
                    break;

                }
            }
        }
      
        
       if($qu['type']) $query['type']=$qu['type'];
       if(isset($qu['query'])) $query['query'] =  ["query"=>'*'.$qu['query'].'*',"default_operator"=>"and"];
      
        return $query;
    }
    /**
	* Basic Query conversion 
	* @param  array 	$query
	* @param  string 	$order
	* @param  string 	$sort
	* @param  string 	$after
	* @return array		
	*/
    public function query($query,$order=false,$sort=false,$after=false){
        $query = $this->queryConvert($query);
        $obj = [];
        if(isset($query['must']) && $query['must']) $obj['query']['bool']['must'] = $query['must'];
        if(isset($query['should']) && $query['should']) $obj['query']['bool']['should'] = $query['should'];
        if(isset($query['should']) && $query['should']) $obj['query']['bool']['minimum_should_match']=1;
        if(isset($query['must_not'])) $obj['query']['bool']['must_not'] = $query['must_not'];
        if(isset($query['filter'])) $obj['query']['bool']['filter'] = $query['filter'];
        if(isset($query['aggs']) && $query['aggs']) $obj['aggs'] = $query['aggs'];
        if(isset($query['aggs_date']) && $query['aggs_date']) $obj['aggs']['result']['date_histogram'] = $query['aggs_date'];
        if(isset($query['aggs_term']) && $query['aggs_term']) $obj['aggs']['values']['terms']["field"] = $query['aggs_term'];
        if(isset($query['aggs_sum'])) $obj['aggs']['result']['sum']['field'] = $query['aggs_sum'];
        if(isset($query['type'])) $obj['query']['bool']['filter']['match']['type'] = $query['type'];
        if(isset($query['query'])) $obj['query']['bool']['must']['query_string'] = $query['query'];
		if($after) $obj["search_after"] = [$after];
	
        if($sort) $obj['sort'][$sort] = $order;
        return $obj;
    }
    /**
	* Save Data
	* @param  string 	$type
	* @param  array 	$data
	* @param  string 	$id
	* @param  string 	$index
	* @param  boolean 	$wait
	* @return array		
	*/
    public function save($type,$data,$id=false,$wait=false){
        $id = ($id) ? $id : '';
        $wait = ($wait) ? '_doc/'.$id.'?refresh=wait_for' : '_doc/';
        $data['type'] = $type;
        return $this->call($wait,$data,'POST');
    }
    /**
	* Update Data
	* @param  string 	$id
	* @param  array 	$data
	* @param  string 	$index
	* @param  boolean 	$wait
	* @return array		
	*/
    public function update($id,$data,$wait=false){
        $data = ['doc'=>$data];
        $wait = ($wait) ? '?refresh=wait_for' : '';
        return $this->call('_doc/'.$id.'/_update'.$wait,$data,'POST');
    }
    /**
	* Bulk Import Data
	* @param  array 	$data
	* @param  string 	$type
	* @param  string 	$action
	* @param  boolean 	$wait
	* @return array		
	*/
    public function bulk($data,$type=false,$act=false,$wait=0){
        $wait = ($wait) ? '?refresh=wait_for' : '';
    	$this->isBulk = true;
        $b = [];
       
        foreach($data as $a) {
            if(!isset($a['_action']) && !$act) return ['error'=>'Must include _action var'];
            $action = ($act) ? $act : $a['_action'];
            if($type) $a['type'] = $type;
            $index = $a['_index'] ?? $this->index;

            $root = ['_index'=>$index,'_type'=>'_doc'];
            if(isset($a['id'])) {
				$root['_id'] = $a['id'];
				unset($a['id']);
			}
            if(isset($a['_parent'])) $root['parent'] = $a['_parent'];
            $b[]=json_encode([$action=>$root]);

            unset($a['_index'],$a['_action'],$a['_parent'],$a['_id']);
            if($action=='update') $a=['doc'=>$a];
            if($action!='delete') $b[]=json_encode($a,JSON_NUMERIC_CHECK);
        }
        $str =  str_replace(['"true"','"false"','"null"','\"'],['true','false','null',''],join("\n", $b)."\n");
        return $this->call('_bulk'.$wait,['data'=>$str],'POST','');
    }
    /**
	* Bulk Import Data Raw
	* @param  array 	$data
	* @return array		
	*/
    public function bulkRaw($data){
        return $this->call('_bulk',$data,'POST');
    }
    /**
	* Bulk Get
	* @param  array 	$data
	* @return array		
	*/
    public function bulkGet($data) {
        return $this->call('',["docs"=>$data],'POST','_mget');
    }
    /**
	* Clean Data
	* @param  array 	$array
	* @return string		
	*/
    public function clean($array){
        if(isset($this->isBulk)){
            return $array['data'] ?? [];
        } else {
            $string = json_encode($array);
            return str_replace(['"true"','"false"','"null"'],['true','false','null'],$string);
        }
    }
    /**
	* Delete Data
	* @param  string 	$id
	* @param  boolean 	$wait
	* @return array		
	*/
    public function delete($id,$wait=false){
        $wait = ($wait) ? '?refresh=wait_for' : '';
        return $this->call('_doc/'.$id.$wait,0,'DELETE');
    }
    /**
	* Delete Type
	* @param  string 	$id
	* @param  boolean 	$wait
	* @return array		
	*/
    public function deleteType($type,$wait=false){
        $wait = ($wait) ? '?refresh=wait_for' : '';
        $query['type']=$type;
        return $this->call('_delete_by_query'.$wait,$this->query($query),'POST');
    }
    /**
	* Delete Query
	* @param  string 	$id
	* @param  boolean 	$wait
	* @return array		
	*/
    public function deleteQuery($query,$wait=false){
        $wait = ($wait) ? '?refresh=wait_for' : '';
        return $this->call('_delete_by_query'.$wait,$this->query($query),'POST');
    }
    /**
	* Duplicate Data
	* @param  array 	$data
	* @param  boolean 	$wait
	* @return array		
	*/
    public function duplicate($data){
          $query = [];
          $query['source']['index'] = $data['source'];
          $query['dest']['index'] = $data['dest'];
          if(isset($data['conflicts'])) $data['conflicts']='proceed';
          if(isset($data['type'])) $data['source']['type'] = $data['type'];
          if(isset($data['query'])) $data['source']['query'] = $data['query'];
          if(isset($data['op_type'])) $data['dest']['op_type'] = $data['op_type'];
          if(isset($data['version_type'])) $data['dest']['version_type'] = $data['version_type'];
          return $this->call('',$query,'POST','_reindex');
    }
    /**
	* Delete Index
	* @param  string 	$db
	* @return array		
	*/
    public function reset($db){
        return $this->call('',false,'DELETE',$db);
    }
    /**
	* Restart Cluster
	* @return array		
	*/
    public function restart(){
        if($this->restarting) return false;
        $this->restarting = true;
        $cmd = 'sudo service elasticsearch restart';
        return shell_exec($cmd);
    }
    /**
	* CURL Call
	* @return array		
	*/
    public function call($path,$query=false,$method='GET',$index=false){
        if($query && $method=='GET') $method = 'POST';
        
        $ind = ($index || $index=='') ? $index.'/' : $this->index.'/' ;
        $server = ($ind!='') ? $this->server.'/' : $this->server;
        $ci = curl_init();

        curl_setopt($ci, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        curl_setopt($ci, CURLOPT_URL, $server . $ind  . $path);
        curl_setopt($ci, CURLOPT_PORT, $this->port);
        curl_setopt($ci, CURLOPT_TIMEOUT, 200);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ci, CURLOPT_FORBID_REUSE, 0);
        curl_setopt($ci, CURLOPT_CUSTOMREQUEST, $method);
        if($query) curl_setopt($ci, CURLOPT_POSTFIELDS, $this->clean($query));
        $response = curl_exec($ci);
        return ($this->ajax) ? $response :  json_decode($response,true) ;
    }
    /**
	* ID Exists
	* @return array		
	*/
    public function exists($id,$db=false){return $this->callHead($id,$db);}
    /**
	* CURL Head Call
	* @return array		
	*/
    public function callHead($path,$index=false){
        $ind = ($index) ? $index : $this->index ;
        $call = get_headers($this->server . '/' . $ind . '/' . $path);
        return ($call[0]=='HTTP/1.0 200 OK') ? ["successful"=>1,'header'=>$call[0]] : ["successful"=>0,'header'=>$call[0]];
    }
    
}



?>
