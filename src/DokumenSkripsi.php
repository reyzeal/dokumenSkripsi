<?php
namespace reyzeal;

use Spatie\PdfToText\Pdf;
use JsonSerializable;
class DokumenSkripsi implements JsonSerializable{
    private $file, $info, $bab;
    protected $type=null;
    public function __construct($file){
        if(!file_exists($file)) die('file not found');
        $this->file= $file;
    }

    private function pdf($first=null,$last=null,$array=false){
        $first = $first?"-f $first":null;
        $last = $last?"-l $last":null;
        $opt = [];
        if($first) $opt[]=$first; 
        if($last) $opt[]=$last; 
        $text = (new Pdf())
            ->setPdf($this->file)
            ->setOptions($opt)
            ->text();
        return $text;
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
        $nama = strpos($cover,strtolower($nama)) != false;
        $nim = strpos($cover,$nim) != false;
        return $nama && $nim ;
    }

    public function scan(){
        $cover = strtolower($this->pdf(null,1));
        preg_match_all("/\s+(tugas akhir)\s+/",$cover,$laporan);
        preg_match_all("/\s+(proposal)\s+/",$cover,$proposal);
        if(isset($proposal[1][0]) && strlen($proposal[1][0])){
            $this->type = "Proposal";
        }
        if(isset($laporan[1][0]) && strlen($laporan[1][0])){
            $this->type = "Laporan";
        }
        $this->bab();
        $this->meta();
    }

    public function meta(){
        $esc = escapeshellarg($this->file);
        exec("pdfinfo $esc",$output);
        $output = implode("\n",$output);
        preg_match_all("/([^:\n]+):(?:\n|\s+([^\n]+))/",$output,$matches);
        $result = [];
        $i = 0;
        foreach ($matches[1] as $item){
            $result[$item] = $matches[2][$i++];
        }
        $this->info = $result;
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
    public function jsonSerialize(){
        return [
            'type' => $this->type,
            'file' => $this->file,
            'bab' => $this->bab,
            'info' => $this->info
        ];
    }
    public function bab($nama = null){
        $metadata = $this->meta();
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