<?php
/**
 * @package    JShrink
 * @author     Robert Hafner <tedivm@tedivm.com>
 */
class jshrink {
    protected $input;
    protected $len = 0;
    protected $index = 0;
    protected $a = '';
    protected $b = '';
    protected $c;
    protected $options;
    protected $stringDelimiters = ['\'' => true, '"' => true, '`' => true];
    protected static $defaultOptions = ['flaggedComments' => true];
    protected $locks = [];
    public static function minify($js, $options = []) {
        try {
            ob_start();
            $jshrink = new jshrink();
            $js = $jshrink->lock($js);
            $jshrink->minifyDirectToOutput($js, $options);
            $js = ltrim(ob_get_clean());
            $js = $jshrink->unlock($js);
            unset($jshrink);
            return $js;
        } catch (\Exception $e) {
            if (isset($jshrink)) {
                $jshrink->clean();
                unset($jshrink);
            }
            ob_end_clean();
            throw $e;
        }
    }
    protected function minifyDirectToOutput($js, $options) {
        $this->initialize($js, $options);
        $this->loop();
        $this->clean();
    }
    protected function initialize($js, $options) {
        $this->options = array_merge(static::$defaultOptions, $options);
        $this->input = str_replace(["\r\n", '/**/', "\r"], ["\n", "", "\n"], $js);
        $this->input .= PHP_EOL;
        $this->len = strlen($this->input);
        $this->a = "\n";
        $this->b = $this->getReal();
    }
    protected $noNewLineCharacters = [
        '(' => true,
        '-' => true,
        '+' => true,
        '[' => true,
        '@' => true];
    protected function loop() {
        while ($this->a !== false && !is_null($this->a) && $this->a !== '') {
            switch ($this->a) {
                // new lines
                case "\n":
                    if ($this->b !== false && isset($this->noNewLineCharacters[$this->b])) {
                        echo $this->a;
                        $this->saveString();
                        break;
                    }
                    if ($this->b === ' ')  break;
                case ' ':
                    if (static::isAlphaNumeric($this->b)) echo $this->a;
                    $this->saveString();
                    break;

                default:
                    switch ($this->b) {
                        case "\n":
                            if (strpos('}])+-"\'', $this->a) !== false) {
                                echo $this->a;
                                $this->saveString();
                                break;
                            } else {
                                if (static::isAlphaNumeric($this->a)) {
                                    echo $this->a;
                                    $this->saveString();
                                }
                            }
                            break;

                        case ' ':
                            if (!static::isAlphaNumeric($this->a)) {
                                break;
                            }
                        default:
                            if ($this->a === '/' && ($this->b === '\'' || $this->b === '"')) {
                                $this->saveRegex();
                                continue 3;
                            }

                            echo $this->a;
                            $this->saveString();
                            break;
                    }
            }
            $this->b = $this->getReal();

            if (($this->b == '/' && strpos('(,=:[!&|?', $this->a) !== false)) {
                $this->saveRegex();
            }
        }
    }
    protected function clean() {
        unset($this->input);
        $this->len = 0;
        $this->index = 0;
        $this->a = $this->b = '';
        unset($this->c);
        unset($this->options);
    }
    protected function getChar() {
        if (isset($this->c)) {
            $char = $this->c;
            unset($this->c);
        } else {
            $char = $this->index < $this->len ? $this->input[$this->index] : false;

            // If the next character doesn't exist return false.
            if (isset($char) && $char === false) {
                return false;
            }

            // Otherwise increment the pointer and use this char.
            $this->index++;
        }

        if ($char !== "\n" && $char < "\x20") return ' ';
        

        return $char;
    }

    protected function getReal() {
        $startIndex = $this->index;
        $char = $this->getChar();
        if ($char !== '/') return $char;

        $this->c = $this->getChar();

        if ($this->c === '/') {
            $this->processOneLineComments($startIndex);

            return $this->getReal();
        } elseif ($this->c === '*') {
            $this->processMultiLineComments($startIndex);

            return $this->getReal();
        }

        return $char;
    }
    protected function processOneLineComments($startIndex) {
        $thirdCommentString = $this->index < $this->len ? $this->input[$this->index] : false;
        $this->getNext("\n");

        unset($this->c);

        if ($thirdCommentString == '@') {
            $endPoint = $this->index - $startIndex;
            $this->c = "\n" . substr($this->input, $startIndex, $endPoint);
        }
    }
    protected function processMultiLineComments($startIndex) {
        $this->getChar(); 
        $thirdCommentString = $this->getChar();
        if ($this->getNext('*/')) {
            $this->getChar(); 
            $this->getChar();
            $char = $this->getChar(); 
            if (($this->options['flaggedComments'] && $thirdCommentString === '!')
                || ($thirdCommentString === '@')) {
                if ($startIndex > 0) {
                    echo $this->a;
                    $this->a = " ";
                    if ($this->input[($startIndex - 1)] === "\n") {
                        echo "\n";
                    }
                }

                $endPoint = ($this->index - 1) - $startIndex;
                echo substr($this->input, $startIndex, $endPoint);

                $this->c = $char;

                return;
            }
        } else {
            $char = false;
        }

        if ($char === false) {
            throw new \RuntimeException('Unclosed multiline comment at position: ' . ($this->index - 2));
        }
        $this->c = $char;
    }
    protected function getNext($string) {
        $pos = strpos($this->input, $string, $this->index);
        if ($pos === false) return false;
        $this->index = $pos;
        return $this->index < $this->len ? $this->input[$this->index] : false;
    }

    protected function saveString() {
        $startpos = $this->index;
        $this->a = $this->b;
        if (!isset($this->stringDelimiters[$this->a])) return;
        
        $stringType = $this->a;
        echo $this->a;
        while (($this->a = $this->getChar()) !== false) {
            switch ($this->a) {
                case $stringType:
                    break 2;
                case "\n":
                    if ($stringType === '`') {
                        echo $this->a;
                    } else {
                        throw new \RuntimeException('Unclosed string at position: ' . $startpos);
                    }
                    break;
                case '\\':
                    $this->b = $this->getChar();
                    if ($this->b === "\n") break;
                    echo $this->a . $this->b;
                    break;
                default:
                    echo $this->a;
            }
        }
    }

    protected function saveRegex() {
        echo $this->a . $this->b;

        while (($this->a = $this->getChar()) !== false) {
            if ($this->a === '/') break;
            if ($this->a === '\\') {
                echo $this->a;
                $this->a = $this->getChar();
            }

            if ($this->a === "\n") {
                throw new \RuntimeException('Unclosed regex pattern at position: ' . $this->index);
            }

            echo $this->a;
        }
        $this->b = $this->getReal();
    }

    
    protected static function isAlphaNumeric($char) {
        return preg_match('/^[\w\$\pL]$/', $char) === 1 || $char == '/';
    }

    protected function lock($js) {
        $lock = '"LOCK---' . crc32(time()) . '"';
        $matches = [];
        preg_match('/([+-])(\s+)([+-])/S', $js, $matches);
        if (empty($matches)) return $js;
        $this->locks[$lock] = $matches[2];
        $js = preg_replace('/([+-])\s+([+-])/S', "$1{$lock}$2", $js);
        return $js;
    }
    protected function unlock($js) {
        if (empty($this->locks)) return $js;
        foreach ($this->locks as $lock => $replacement) {
            $js = str_replace($lock, $replacement, $js);
        }

        return $js;
    }
}