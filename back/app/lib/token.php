<?php
/****************************************
docs.paxagency.com/php/libraries/token
*****************************************/
class token {
    public $key = 'nhU0FxpV2SVL57SfG6dC7GvwsJYBidSE';
    public $algos = [
        'HS256' => ['hash_hmac', 'SHA256'],
        'HS512' => ['hash_hmac', 'SHA512'],
        'HS384' => ['hash_hmac', 'SHA384'],
        'RS256' => ['openssl', 'SHA256'],
        'RS384' => ['openssl', 'SHA384'],
        'RS512' => ['openssl', 'SHA512']
    ];
    public function encode($payload,$alg='HS256',$head=[]) {
        if(!$this->algos[$alg]) return ['success'=>0,'message'=>'algo not supported'];
        $header = array_merge($head,['typ'=>'JWT','alg'=>$alg]);
        $segments = [
            $this->urlsafeB64Encode(json_encode($header)),
            $this->urlsafeB64Encode(json_encode($payload))
        ];
        $signing_input = implode('.', $segments);
        $signature = $this->sign($signing_input, $this->key, $alg);
        $segments[] = $this->urlsafeB64Encode($signature);
        return implode('.', $segments);
    }
    public function sign($msg, $key, $alg = 'HS256') {  
        $function = $this->algos[$alg][0];
        $algorithm = $this->algos[$alg][1];
        switch($this->algos[$alg][0]) {
            case 'hash_hmac':
                return hash_hmac($algorithm, $msg, $key, true);
            case 'openssl':
                $signature = '';
                $success = openssl_sign($msg, $signature, $key, $algorithm);
                return (!$success) ? 0 : $signature;
        }
    }
    public  function decode($jwt){
        $key = $this->key;
        $timestamp = time();
        $tks = explode('.', $jwt);
        if (count($tks) != 3) return ['success'=>0,'message'=>'wrong number of segments.'];
        list($headb64, $bodyb64, $cryptob64) = $tks;
  
        $header = json_decode($this->urlsafeB64Decode($headb64));
        $payload = json_decode($this->urlsafeB64Decode($bodyb64),TRUE);
     
        $sig = $this->urlsafeB64Decode($cryptob64);
        if (is_array($key) && isset($header->kid)) $key = $key[$header->kid];
        
        if(!$header || !$payload || !$sig
        || !$this->verify("$headb64.$bodyb64", $sig, $key, $header->alg)) 
            return ['success'=>0,'message'=>'The token could not be decoded'];
        
        if (isset($payload['nbf']) && $timestamp < $payload['nbf']) 
            return ['success'=>0,'message'=>'The token is not active yet.'];
           
        if (isset($payload['exp']) && $timestamp > $payload['exp']) 
            return ['success'=>0,'message'=>'The token has expired.'];
        
        $payload['success']=1;
        return $payload;
    }
    private function verify($msg, $signature, $key, $alg) {
        if (!$this->algos[$alg]) return 0;
        list($function, $algorithm) = $this->algos[$alg];
        switch($function) {
            case 'openssl':
                return openssl_verify($msg, $signature, $key, $algorithm);
            case 'hash_hmac':
            default:
                $hash = hash_hmac($algorithm, $msg, $key, true);
                if (function_exists('hash_equals')) return hash_equals($signature, $hash);
                
                $len = min(strlen($signature), strlen($hash));
                $status = 0;
                for ($i = 0; $i < $len; $i++) {
                    $status |= (ord($signature[$i]) ^ ord($hash[$i]));
                }
                $status |= ($this->safeStrlen($signature) ^ $this->safeStrlen($hash));
                return ($status === 0);
        }
    }
    public function urlsafeB64Encode($input) {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }
    public function urlsafeB64Decode($input) {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }
}