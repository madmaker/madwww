<?php
namespace translator;

class translator {
    private $dictionary;
    private $file_path;

    public function txt($string) {
        return isset($this->dictionary[$this->file_path][$string])?$this->dictionary[$this->file_path][$string]:$string;
    }

    public function __construct ($lang, $file_path) {
        $this->file_path=$file_path;
        $dictionary_path="translator/dictionaries/$lang/$this->file_path";
        if(file_exists($dictionary_path)) {
            /** @noinspection PhpIncludeInspection */
            require_once $dictionary_path;
        }
    }
}
