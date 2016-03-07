<?php


class CountryConfigFabric
{
    public static function getConfig($countryName)
    {
        $className = $countryName."Config";
        return new $className;
    }

}

abstract class Syrup
{
    public function getPrice(){
        return $this->price;
    }
    public function getName(){
        return $this->name;
    }
}

class Cocos extends Syrup
{
    public $name = "Кокосовый сироп";
    public $price = 0.5;
}

class Melon extends  Syrup
{
    public $name = "Cироп дыня";
    public $price = 0.5;
}

//------------------------------------
abstract class Additions
{
    public function getName(){
        return $this->name;
    }

    public function getPrice(){
        return $this->price;
    }
}

class Chocolate extends Additions
{
    public $name = "Плитка шоколада";
    public $price = 1;
}

class Cruasan extends  Additions
{
    public $name = "Круасан";
    public $price = 1;
}








abstract class CountryConfig {};

class ItalyConfig extends CountryConfig
{
    /**
     * ItalyConfig constructor.
     */
    public function __construct()
    {
        $this->coffeeCount = 1;
        $this->syrup = new Cocos();
        $this->addition = new Cruasan();
        $this->milkPrice = 0.4;
        $this->basePrice = 1.5;
        $this->tax = 0.07;
    }
}

class SpainConfig extends CountryConfig
{
    public function __construct()
    {
        $this->coffeeCount = 2;
        $this->syrup = new Melon();
        $this->addition = new Chocolate();
        $this->milkPrice = 0.3;
        $this->basePrice = 1;
        $this->tax = 0.03;
    }
}



class Coffee {

    protected $price;
    protected $config;
    protected $bill = [];

    public function __construct(CountryConfig $config)
    {
        $this->config = $config;

    }



    protected function boilWater(){ }
    protected function waterToCup() {}

    protected function addCoffee(){
        $this->bill[] = "Порция кофе x".$this->config->coffeeCount;
        $this->price+=$this->config->coffeeCount * $this->config->basePrice;
    }
    protected function addMilk()
    {
        $this->bill[] = "Молоко";
        $this->price+=$this->config->milkPrice;
    }

    public function addSyrup()
    {
        $this->bill[] = "Сироп: ".$this->config->syrup->getName();
        $this->price+=$this->config->syrup->getPrice();
    }

    public function addAdditions()
    {
        $this->bill[] = "Дополнение: ".$this->config->addition->getName();
        $this->price+=$this->config->addition->getPrice();
    }

    public function getIngredients()
    {
        return implode(", ",$this->bill);
    }
    public function getPrice()
    {
        return $this->price + $this->price*$this->config->tax;
    }
}




class Latte extends  Coffee
{

    public function __construct(CountryConfig $config)
    {
        parent::__construct($config);
    }

    public function make()
    {
        $this->boilWater();
        $this->waterToCup();
        $this->addCoffee();
        $this->addMilk();
    }



}


?>


<form action="" method="post">
    <table>
        <tr>
            <td>
                Страна:<td>
                <select name="country">
                    <option value="Italy">Италия</option>
                    <option value="Spain">Испания</option>
                </select>

        <tr>
            <td>Добавить сироп</td>
            <td><input type="checkbox" name="syrup" value="1"></td>
        </tr>

        <tr>
            <td>Добавить дополнение</td>
            <td><input type="checkbox" name="add" value="1"></td>
        </tr>
        <tr>
            <input type="submit"  value="Заказать">
        </tr>
    </table>
    <input type="hidden" name="action" value="search">
</form>

<?php
if($_POST['action']=="search")
{

    $config = CountryConfigFabric::getConfig($_POST['country']);
    $cup = new Latte($config);
    $cup->make();
    if($_POST['syrup']=="1")
    {
        $cup->addSyrup();
    }
    if($_POST['add']=="1")
    {
        $cup->addAdditions();
    }
    echo $cup->getIngredients(). PHP_EOL;

    echo "цена: ".number_format($cup->getPrice(),2);
}


?>



