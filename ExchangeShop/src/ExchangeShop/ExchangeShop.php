<?php
declare(strict_types=1);

namespace ExchangeShop;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;

use ExchangeShop\Commands\ShopSettingCommand;
use ExchangeShop\Commands\ShopCommand;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;


use pocketmine\world\Position;
use pocketmine\world\World;
use pocketmine\world\WorldManager;

use LifeInventoryLib\LifeInventoryLib;
use LifeInventoryLib\InventoryLib\LibInvType;

use pocketmine\scheduler\Task;
use pocketmine\scheduler\AsyncTask;

class ExchangeShop extends PluginBase {

  protected $config;
  public $db;
  public $get = [];
  private static $instance = null;

  public static function getInstance(): ExchangeShop
  {
    return static::$instance;
  }

  public function onLoad():void
  {
    self::$instance = $this;
  }

  public function onEnable():void
  {
    $this->player = new Config ($this->getDataFolder() . "players.yml", Config::YAML);
    $this->pldb = $this->player->getAll();
    $this->shop = new Config ($this->getDataFolder() . "shops.yml", Config::YAML);
    $this->shopdb = $this->shop->getAll();
    $this->getServer()->getCommandMap()->register('ExchangeShop', new ShopSettingCommand($this));
    $this->getServer()->getCommandMap()->register('ExchangeShop', new ShopCommand($this));
    $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
  }

  public function tag() : string
  {
    return "§c【 §fExchangeShop §c】 §7: ";
  }

  public function getShopNameLists($player) : array{
    $arr = [];
    foreach($this->shopdb ["상점"] as $ShopName => $v){
      array_push($arr, $ShopName);
    }
    return $arr;
  }

  public function ShopAddSettingGUI($player) {
    $name = $player->getName ();
    $playerPos = $player->getPosition();
    $inv = LifeInventoryLib::getInstance ()->create("DOUBLE_CHEST", new Position($playerPos->x, $playerPos->y - 2, $playerPos->z, $playerPos->getWorld()), "[ 교환상점 ] | 보상세팅",$player);
    $shopname = $this->pldb [strtolower($name)] ["ShopName"];
    $i = 0;
    foreach($this->shopdb [$shopname] as $count => $v){
      if(isset($this->shopdb [$shopname] [$count] ["nbt"])){
        $item = Item::jsonDeserialize($this->shopdb [$shopname] [$count] ['nbt']);
        $inv->setItem( $i , $item );
        ++$i;
      }
    }
    $inv->setItem( 45 , ItemFactory::getInstance()->get(63, 0, 1)->setCustomName(" "));
    $inv->setItem( 46 , ItemFactory::getInstance()->get(63, 0, 1)->setCustomName(" "));
    $inv->setItem( 47 , ItemFactory::getInstance()->get(63, 0, 1)->setCustomName(" "));
    $inv->setItem( 48 , ItemFactory::getInstance()->get(63, 0, 1)->setCustomName(" "));
    $inv->setItem( 49 , ItemFactory::getInstance()->get(63, 0, 1)->setCustomName(" "));
    $inv->setItem( 50 , ItemFactory::getInstance()->get(63, 0, 1)->setCustomName(" "));
    $inv->setItem( 51 , ItemFactory::getInstance()->get(63, 0, 1)->setCustomName(" "));
    $inv->setItem( 52 , ItemFactory::getInstance()->get(63, 0, 1)->setCustomName(" "));
    $inv->setItem( 53 , ItemFactory::getInstance()->get(54, 0, 1)->setCustomName("설정완료")->setLore([ "이벤트 이용시 상점 설정완료\n경고 : 이용시 이전 저장정보가 삭제됩니다." ]) );

    LifeInventoryLib::getInstance ()->send($inv, $player);
  }

  public function ShopGiveSettingGUI($player) {
    $name = $player->getName ();
    $playerPos = $player->getPosition();
    $inv = LifeInventoryLib::getInstance ()->create("DOUBLE_CHEST", new Position($playerPos->x, $playerPos->y - 2, $playerPos->z, $playerPos->getWorld()), "[ 교환상점 ] | 교환상점재료세팅",$player);
    $shopname = $this->pldb [strtolower($name)] ["ShopName"];
    $i = 0;
    foreach($this->shopdb [$shopname] as $count => $v){
      if(isset($this->shopdb [$shopname] [$count] ["nbt"])){
        $item = Item::jsonDeserialize($this->shopdb [$shopname] [$count] ['nbt']);
        $CheckItem = $item;
        $inv->setItem( $i , $CheckItem );
        ++$i;
      }
    }
    $inv->setItem( 45 , ItemFactory::getInstance()->get(63, 0, 1)->setCustomName(" "));
    $inv->setItem( 46 , ItemFactory::getInstance()->get(63, 0, 1)->setCustomName(" "));
    $inv->setItem( 47 , ItemFactory::getInstance()->get(63, 0, 1)->setCustomName(" "));
    $inv->setItem( 48 , ItemFactory::getInstance()->get(63, 0, 1)->setCustomName(" "));
    $inv->setItem( 49 , ItemFactory::getInstance()->get(63, 0, 1)->setCustomName(" "));
    $inv->setItem( 50 , ItemFactory::getInstance()->get(63, 0, 1)->setCustomName(" "));
    $inv->setItem( 51 , ItemFactory::getInstance()->get(63, 0, 1)->setCustomName(" "));
    $inv->setItem( 52 , ItemFactory::getInstance()->get(63, 0, 1)->setCustomName(" "));
    $inv->setItem( 53 , ItemFactory::getInstance()->get(63, 0, 1)->setCustomName(" "));

    LifeInventoryLib::getInstance ()->send($inv, $player);
  }

  public function ShopGiveSetGUI($player) {
    $name = $player->getName ();
    $playerPos = $player->getPosition();
    $inv = LifeInventoryLib::getInstance ()->create("DOUBLE_CHEST", new Position($playerPos->x, $playerPos->y - 2, $playerPos->z, $playerPos->getWorld()), "[ 교환상점 ] | 교환재료",$player);
    $inv->setItem( 45 , ItemFactory::getInstance()->get(63, 0, 1)->setCustomName(" "));
    $inv->setItem( 46 , ItemFactory::getInstance()->get(63, 0, 1)->setCustomName(" "));
    $inv->setItem( 47 , ItemFactory::getInstance()->get(63, 0, 1)->setCustomName(" "));
    $inv->setItem( 48 , ItemFactory::getInstance()->get(63, 0, 1)->setCustomName(" "));
    $inv->setItem( 49 , ItemFactory::getInstance()->get(63, 0, 1)->setCustomName(" "));
    $inv->setItem( 50 , ItemFactory::getInstance()->get(63, 0, 1)->setCustomName(" "));
    $inv->setItem( 51 , ItemFactory::getInstance()->get(63, 0, 1)->setCustomName(" "));
    $inv->setItem( 52 , ItemFactory::getInstance()->get(63, 0, 1)->setCustomName(" "));
    $inv->setItem( 53 , ItemFactory::getInstance()->get(54, 0, 1)->setCustomName("설정완료")->setLore([ "이벤트 이용시 상점 설정완료\n경고 : 이용시 이전 저장정보가 삭제됩니다." ]) );

    LifeInventoryLib::getInstance ()->send($inv, $player);
  }

  public function PlayerShopOpen($player) {
    $name = $player->getName ();
    $playerPos = $player->getPosition();
    $shopname = $this->pldb [strtolower($name)] ["ShopName"];
    $inv = LifeInventoryLib::getInstance ()->create("DOUBLE_CHEST", new Position($playerPos->x, $playerPos->y - 2, $playerPos->z, $playerPos->getWorld()), "[ 교환상점 ] | {$shopname}",$player);
    $i = 0;
    if (isset($this->shopdb [$shopname])){
      foreach($this->shopdb [$shopname] as $count => $v){
        if(isset($this->shopdb [$shopname] [$count] ["nbt"])){
          $item = Item::jsonDeserialize($this->shopdb [$shopname] [$count] ["nbt"]);
          $CheckItem = $item;
          $inv->setItem( $i , $CheckItem->setLore([ "§r§l§b아이템 이동시 교환진행가능" ]) );
          ++$i;
        }
      }
    }
    $inv->setItem( 53 , ItemFactory::getInstance()->get(54, 0, 1)->setCustomName("나가기")->setLore([ "해당 GUI를 나갑니다." ]) );

    LifeInventoryLib::getInstance ()->send($inv, $player);
  }

  public function PlayerCheck($player) {
    $name = $player->getName ();
    $playerPos = $player->getPosition();
    $shopname = $this->pldb [strtolower($name)] ["ShopName"];
    $i = $this->pldb [strtolower($name)] ["Slot"];
    $inv = LifeInventoryLib::getInstance ()->create("DOUBLE_CHEST", new Position($playerPos->x, $playerPos->y - 2, $playerPos->z, $playerPos->getWorld()), "[ 교환상점 ] | {$shopname} 진행",$player);
    $Xyz = 0;
    if(isset($this->shopdb [$shopname] [$i."번"])){
      foreach($this->shopdb [$shopname] [$i."번"] ["교환재료"] as $count => $v){
        if(isset($this->shopdb [$shopname] [$i."번"] ["교환재료"] [$count])){
          $item = Item::jsonDeserialize($this->shopdb [$shopname] [$i."번"] ["교환재료"] [$count]);
          $inv->setItem( $Xyz , $item->setLore([ "§r§l§b교환에 필요한 재료입니다." ]) );
          ++$Xyz;
        }
      }
    }
    $inv->setItem( 52 , ItemFactory::getInstance()->get(54, 0, 1)->setCustomName("교환진행하기")->setLore([ "교환을 진행합니다." ]) );
    $inv->setItem( 53 , ItemFactory::getInstance()->get(54, 0, 1)->setCustomName("나가기")->setLore([ "해당 GUI를 나갑니다." ]) );

    LifeInventoryLib::getInstance ()->send($inv, $player);
  }

  public function onDisable():void
  {
    $this->save();
  }

  public function save():void
  {
    $this->player->setAll($this->pldb);
    $this->player->save();
    $this->shop->setAll($this->shopdb);
    $this->shop->save();
  }
}
