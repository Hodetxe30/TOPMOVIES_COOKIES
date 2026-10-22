<?php

class Filmak{
    private $filmak;

    public function __construct(){
        $this->filmak=[];
    }

    public function getFilmak(){
        return $this->filmak;
    }

    public function gehitu(Filma $filma){
        if(!$this->bilatuISAN($filma->getISAN())){
            $this->filmak[$filma->getISAN()] = $filma;
            return true;
        }
        return false;
    }

    public function bilatuISAN($ISAN){
        if(isset($this->filmak[$ISAN])){
            return $this->filmak[$ISAN];
        }
        return null;
    }

    public function ezabatu($ISAN){
        if(isset($this->filmak[$ISAN])){
            unset($this->filmak[$ISAN]);
            return true;
        }
        return false;
    }

    public function normalizatu($testua){
        $testua = mb_strtolower($testua, 'UTF-8');

        $normalizatua = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE',
            $testua);

            return $normalizatua !== false ? $normalizatua : $testua;
    }

    public function bilatuIzenez($izena){
        $emaitza = [];
        $bilaketa = $this->normalizatu($izena);

        foreach($this->filmak as $filma){
            $filmarenIzena = $this->normalizatu($filma->getIzena());

            if(strpos($filmarenIzena, $bilaketa) !== false){
                $emaitza[] = $filma;
            }

        }
        return $emaitza;
        }

        public function eguneratu($ISAN, $izena, $puntuazioa){
            if (isset($this->filmak[$ISAN])){
                $this->filmak[$ISAN]->setIzena($izena);
                $this->filmak[$ISAN]->setPuntuazioa($puntuazioa);
                return true;
            }
            return false;
        }

}

?>