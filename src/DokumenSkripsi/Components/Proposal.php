<?php
namespace reyzeal\DokumenSkripsi\Components;

use reyzeal\DokumenSkripsi;

class Proposal extends DokumenSkripsi{
    public function __construct($file){
        parent::__construct($file);
        $this->type='Proposal';
    }

}