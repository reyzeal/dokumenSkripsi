<?php
namespace reyzeal;

use reyzeal\DokumenSkripsi\Components\Laporan;
use reyzeal\DokumenSkripsi\Components\Proposal;
use Spatie\PdfToText\Pdf;

class DokumenSkripsi{
    private $file;
    protected $type=null;
    public function __construct($file){
        if(!file_exists($file)) die('file not found');

        $this->file= $file;
    }

    private function pdf($first=null,$last=null,$array=false){
        $first = $first?"-f $first":"";
        $last = $last?"-l $last":"";
        exec("pdftotext -raw $first $last $this->file - 2>&1",$output);
        return $array?$output:implode("\n",$output);
    }

    public function first(){
        return $this->pdf(null,1);
    }

    public function section($awal,$akhir){
        return $this->pdf($awal,$akhir);
    }

    public function page($number,$array=false){
        return $this->pdf($number,$number,$array);
    }
    public function allPages($array=false){
        return $this->pdf(null,null,$array);
    }

    public function validate($nama,$nim){
        $cover = strtolower($this->first());
        $nama = strpos($cover,strtolower($nama)) != -1;
        $nim = strpos($cover,$nim) != -1;
        return $nama && $nim ;
    }

    public function scan(){
        $cover = strtolower($this->pdf(null,1));
        preg_match_all("/\s+(tugas akhir)\s+/",$cover,$laporan);
        preg_match_all("/\s+(proposal)\s+/",$cover,$proposal);
        if(isset($proposal[1][0]) && strlen($proposal[1][0])) return new Proposal($this->file);
        if(isset($laporan[1][0]) && strlen($laporan[1][0])) return new Laporan($this->file);
    }

    public function info(){
        exec("pdfinfo $this->file 2>&1",$output);
        $output = implode("\n",$output);
        preg_match_all("/([^:\n]+):(?:\n|\s+([^\n]+))/",$output,$matches);
        $result = [];
        $i = 0;
        foreach ($matches[1] as $item){
            $result[$item] = $matches[2][$i++];
        }
        return $result;
    }

    public function type(){
        return $this->type;
    }

    public function isProposal(){
        return $this->type=='Proposal';
    }

    public function isLaporan(){
        return $this->type=='Laporan';
    }
}