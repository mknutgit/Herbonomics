<?php

class GrowersStrains
{
    private $id;
    private $growers_id;
    private $strain_name;
    private $pheno;
    private $thc;
    private $cbd;
    private $cgc; //clean green certified = cgc
    private $price;

    function __construct($id=null, $strain_name, $pheno, $thc, $cbd, $cgc, $price, $growers_id)
    {
        $this->id = $id;
        $this->strain_name = $strain_name;
        $this->pheno = $pheno;
        $this->thc = $thc;
        $this->cbd = $cbd;
        $this->cgc = $cgc;
        $this->price = $price;
        $this->growers_id = $growers_id;
    }

    function getId()
    {
        return $this->id;
    }

    function getGrowersId()
    {
        return $this->growers_id;
    }

    function setStrainName($new_strain_name)
    {
        $this->strain_name = $new_strain_name;
    }

    function getStrainName()
    {
        return $this->strain_name;
    }

    function setPheno($new_pheno)
    {
        $this->pheno = $new_pheno;
    }

    function getPheno()
    {
        return $this->pheno;
    }

    function setThc($new_thc)
    {
        $this->thc = $new_thc;
    }

    function getThc()
    {
        return $this->thc;
    }

    function setCbd($new_cbd)
    {
        $this->cbd = $new_cbd;
    }

    function getCbd()
    {
        return $this->cbd;
    }

    function setCgc($new_cgc)
    {
        $this->cgc = $new_cgc;
    }

    function getCgc()
    {
        return $this->cgc;
    }

    function setPrice($new_price)
    {
        $this->price = $new_price;
    }

    function getPrice()
    {
        return $this->price;
    }

    function save()
    {//saves strain to specific grower's profile
        $GLOBALS['DB']->exec("INSERT INTO growers_strains (strain_name, pheno, thc, cbd, cgc, price, growers_id) VALUES ('{$this->getStrainName()}',
        '{$this->getPheno()}',
        {$this->getThc()},
        {$this->getCbd()},
        {$this->getCgc()},
        {$this->getPrice()},
        {$this->getGrowersId()});");
        $this->id = $GLOBALS['DB']->lastInsertId();
    }

    static function getAll()
    {//gets every single strain by every grower
        $returned_strains = $GLOBALS['DB']->query("SELECT * FROM growers_strains;");
        $strains = array();

        foreach($returned_strains as $strain) {
            $id = $strain['id'];
            $strain_name = $strain['strain_name'];
            $pheno = $strain['pheno'];
            $thc = (float) $strain['thc'];
            $cbd = (float) $strain['cbd'];
            $cgc = (int) $strain['cgc'];
            $price = (int) $strain['price'];
            $growers_id = (int) $strain['growers_id'];
            $new_strain = new GrowersStrains($id, $strain_name, $pheno, $thc, $cbd, $cgc, $price, $growers_id);
            array_push($strains, $new_strain);
        }
        return $strains;
    }

    static function deleteAll()
    {
        $GLOBALS['DB']->exec("DELETE FROM growers_strains;");
    }

    static function findById($search_id)
    {
        $found_strain = null;
        $strains = GrowersStrains::getAll();

        foreach($strains as $strain) {
          $strain_id = $strain->getId();
          if ($strain_id == $search_id) {
              $found_strain = $strain;
          }
        }
        return $found_strain;
    }

    function update($strain_name, $pheno, $thc, $cbd, $cgc, $price)
    {
        $GLOBALS['DB']->exec("UPDATE strains_growers SET strain_name = '{$strain_name}', pheno = '{$pheno}', thc = {$thc}, cbd = {$cbd}, price = {$price} WHERE id = {$this->getId()}");
        $this->setStrainName($strain_name);
        $this->setPheno($pheno);
        $this->setThc($thc);
        $this->setCbd($cbd);
        $this->setCgc($cgc);
        $this->setPrice($price);
    }

}
?>