<?php
namespace reyzeal\DokumenSkripsi\Components;

use reyzeal\DokumenSkripsi;

class Laporan extends DokumenSkripsi{
    protected $bab;
    public function __construct($file){
        parent::__construct($file);
        $this->type='Laporan';
    }

    public function bab($nama = null){
        $metadata = $this->info();
        $pages = intval($metadata['Pages']);
        $bab = [];
        for($i=1;$i<=$pages;$i++){
            $data = $this->page($i);
            preg_match_all("/(BAB \w{1,3}|DAFTAR PUSTAKA|DAFTAR ISI)\n/",$data,$found);
            if(isset($found[1][0])){
                $bab[$found[1][0]] = $i;
            }
        }
        if($nama) return $bab[$nama];
        $this->bab = $bab;
        return $bab;
    }
}